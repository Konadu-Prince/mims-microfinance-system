import { Request, Response } from 'express';
import { AuthService } from '../services/AuthService';
import { UserService } from '../services/UserService';
import { TokenService } from '../services/TokenService';
import { validateRequest } from '../middleware/validation';
import { loginSchema, registerSchema, refreshTokenSchema } from '../schemas/authSchemas';
import { RateLimiter } from '../utils/RateLimiter';
import { logger } from '../utils/logger';
import { HttpStatus } from '../types/httpStatus';

export class AuthController {
  private authService: AuthService;
  private userService: UserService;
  private tokenService: TokenService;
  private rateLimiter: RateLimiter;

  constructor() {
    this.authService = new AuthService();
    this.userService = new UserService();
    this.tokenService = new TokenService();
    this.rateLimiter = new RateLimiter();
  }

  /**
   * User registration with modern validation and security
   */
  public register = async (req: Request, res: Response): Promise<void> => {
    try {
      // Rate limiting
      const clientIp = req.ip || req.connection.remoteAddress || 'unknown';
      if (!this.rateLimiter.checkLimit(clientIp, 'register', 5, 3600)) {
        res.status(HttpStatus.TOO_MANY_REQUESTS).json({
          error: 'Too many registration attempts',
          message: 'Please try again later',
        });
        return;
      }

      // Validate request
      const validationResult = validateRequest(req, registerSchema);
      if (!validationResult.isValid) {
        res.status(HttpStatus.BAD_REQUEST).json({
          error: 'Validation failed',
          details: validationResult.errors,
        });
        return;
      }

      const { email, password, firstName, lastName, phone, role } = req.body;

      // Check if user already exists
      const existingUser = await this.userService.findByEmail(email);
      if (existingUser) {
        res.status(HttpStatus.CONFLICT).json({
          error: 'User already exists',
          message: 'An account with this email already exists',
        });
        return;
      }

      // Create user
      const user = await this.userService.create({
        email,
        password,
        firstName,
        lastName,
        phone,
        role: role || 'customer',
        isActive: true,
        emailVerified: false,
      });

      // Generate verification token
      const verificationToken = await this.tokenService.generateVerificationToken(user.id);

      // Send verification email (async)
      this.authService.sendVerificationEmail(user.email, verificationToken);

      // Generate JWT tokens
      const { accessToken, refreshToken } = await this.tokenService.generateTokens(user);

      logger.info(`User registered successfully: ${user.email}`, {
        userId: user.id,
        email: user.email,
        ip: clientIp,
      });

      res.status(HttpStatus.CREATED).json({
        success: true,
        message: 'User registered successfully. Please check your email for verification.',
        data: {
          user: {
            id: user.id,
            email: user.email,
            firstName: user.firstName,
            lastName: user.lastName,
            role: user.role,
            isActive: user.isActive,
            emailVerified: user.emailVerified,
            createdAt: user.createdAt,
          },
          tokens: {
            accessToken,
            refreshToken,
            expiresIn: process.env.JWT_ACCESS_EXPIRES_IN || '15m',
          },
        },
      });
    } catch (error) {
      logger.error('Registration error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Registration failed',
        message: 'An unexpected error occurred during registration',
      });
    }
  };

  /**
   * User login with modern security features
   */
  public login = async (req: Request, res: Response): Promise<void> => {
    try {
      // Rate limiting
      const clientIp = req.ip || req.connection.remoteAddress || 'unknown';
      if (!this.rateLimiter.checkLimit(clientIp, 'login', 10, 900)) {
        res.status(HttpStatus.TOO_MANY_REQUESTS).json({
          error: 'Too many login attempts',
          message: 'Please try again later',
        });
        return;
      }

      // Validate request
      const validationResult = validateRequest(req, loginSchema);
      if (!validationResult.isValid) {
        res.status(HttpStatus.BAD_REQUEST).json({
          error: 'Validation failed',
          details: validationResult.errors,
        });
        return;
      }

      const { email, password, rememberMe } = req.body;

      // Authenticate user
      const authResult = await this.authService.authenticate(email, password);
      if (!authResult.success) {
        // Record failed attempt
        this.rateLimiter.recordFailedAttempt(clientIp, 'login');
        
        res.status(HttpStatus.UNAUTHORIZED).json({
          error: 'Authentication failed',
          message: authResult.message,
        });
        return;
      }

      const { user, requiresTwoFactor } = authResult;

      // Check if 2FA is required
      if (requiresTwoFactor) {
        const twoFactorToken = await this.tokenService.generateTwoFactorToken(user.id);
        
        res.status(HttpStatus.OK).json({
          success: true,
          message: 'Two-factor authentication required',
          data: {
            requiresTwoFactor: true,
            twoFactorToken,
            user: {
              id: user.id,
              email: user.email,
              firstName: user.firstName,
              lastName: user.lastName,
            },
          },
        });
        return;
      }

      // Generate JWT tokens
      const tokenExpiry = rememberMe ? '30d' : '15m';
      const { accessToken, refreshToken } = await this.tokenService.generateTokens(user, tokenExpiry);

      // Update last login
      await this.userService.updateLastLogin(user.id, clientIp);

      logger.info(`User logged in successfully: ${user.email}`, {
        userId: user.id,
        email: user.email,
        ip: clientIp,
        userAgent: req.get('User-Agent'),
      });

      res.status(HttpStatus.OK).json({
        success: true,
        message: 'Login successful',
        data: {
          user: {
            id: user.id,
            email: user.email,
            firstName: user.firstName,
            lastName: user.lastName,
            role: user.role,
            isActive: user.isActive,
            emailVerified: user.emailVerified,
            lastLoginAt: user.lastLoginAt,
          },
          tokens: {
            accessToken,
            refreshToken,
            expiresIn: tokenExpiry,
          },
        },
      });
    } catch (error) {
      logger.error('Login error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Login failed',
        message: 'An unexpected error occurred during login',
      });
    }
  };

  /**
   * Refresh access token
   */
  public refreshToken = async (req: Request, res: Response): Promise<void> => {
    try {
      // Validate request
      const validationResult = validateRequest(req, refreshTokenSchema);
      if (!validationResult.isValid) {
        res.status(HttpStatus.BAD_REQUEST).json({
          error: 'Validation failed',
          details: validationResult.errors,
        });
        return;
      }

      const { refreshToken } = req.body;

      // Verify refresh token
      const tokenResult = await this.tokenService.verifyRefreshToken(refreshToken);
      if (!tokenResult.valid) {
        res.status(HttpStatus.UNAUTHORIZED).json({
          error: 'Invalid refresh token',
          message: tokenResult.message,
        });
        return;
      }

      // Get user
      const user = await this.userService.findById(tokenResult.userId);
      if (!user || !user.isActive) {
        res.status(HttpStatus.UNAUTHORIZED).json({
          error: 'User not found or inactive',
        });
        return;
      }

      // Generate new tokens
      const { accessToken, refreshToken: newRefreshToken } = await this.tokenService.generateTokens(user);

      res.status(HttpStatus.OK).json({
        success: true,
        message: 'Token refreshed successfully',
        data: {
          tokens: {
            accessToken,
            refreshToken: newRefreshToken,
            expiresIn: process.env.JWT_ACCESS_EXPIRES_IN || '15m',
          },
        },
      });
    } catch (error) {
      logger.error('Token refresh error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Token refresh failed',
        message: 'An unexpected error occurred',
      });
    }
  };

  /**
   * Logout user
   */
  public logout = async (req: Request, res: Response): Promise<void> => {
    try {
      const { refreshToken } = req.body;
      const userId = req.user?.id;

      if (refreshToken) {
        // Revoke refresh token
        await this.tokenService.revokeRefreshToken(refreshToken);
      }

      if (userId) {
        // Log logout event
        logger.info(`User logged out: ${req.user?.email}`, {
          userId,
          ip: req.ip,
        });
      }

      res.status(HttpStatus.OK).json({
        success: true,
        message: 'Logout successful',
      });
    } catch (error) {
      logger.error('Logout error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Logout failed',
        message: 'An unexpected error occurred',
      });
    }
  };

  /**
   * Verify email address
   */
  public verifyEmail = async (req: Request, res: Response): Promise<void> => {
    try {
      const { token } = req.params;

      const verificationResult = await this.tokenService.verifyEmailToken(token);
      if (!verificationResult.valid) {
        res.status(HttpStatus.BAD_REQUEST).json({
          error: 'Invalid or expired verification token',
          message: verificationResult.message,
        });
        return;
      }

      // Update user email verification status
      await this.userService.verifyEmail(verificationResult.userId);

      logger.info(`Email verified successfully for user: ${verificationResult.userId}`);

      res.status(HttpStatus.OK).json({
        success: true,
        message: 'Email verified successfully',
      });
    } catch (error) {
      logger.error('Email verification error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Email verification failed',
        message: 'An unexpected error occurred',
      });
    }
  };

  /**
   * Request password reset
   */
  public requestPasswordReset = async (req: Request, res: Response): Promise<void> => {
    try {
      const { email } = req.body;

      // Rate limiting
      const clientIp = req.ip || req.connection.remoteAddress || 'unknown';
      if (!this.rateLimiter.checkLimit(clientIp, 'password-reset', 3, 3600)) {
        res.status(HttpStatus.TOO_MANY_REQUESTS).json({
          error: 'Too many password reset requests',
          message: 'Please try again later',
        });
        return;
      }

      const user = await this.userService.findByEmail(email);
      if (!user) {
        // Don't reveal if email exists
        res.status(HttpStatus.OK).json({
          success: true,
          message: 'If the email exists, a password reset link has been sent',
        });
        return;
      }

      // Generate password reset token
      const resetToken = await this.tokenService.generatePasswordResetToken(user.id);

      // Send password reset email (async)
      this.authService.sendPasswordResetEmail(user.email, resetToken);

      logger.info(`Password reset requested for user: ${user.email}`, {
        userId: user.id,
        ip: clientIp,
      });

      res.status(HttpStatus.OK).json({
        success: true,
        message: 'If the email exists, a password reset link has been sent',
      });
    } catch (error) {
      logger.error('Password reset request error:', error);
      res.status(HttpStatus.INTERNAL_SERVER_ERROR).json({
        error: 'Password reset request failed',
        message: 'An unexpected error occurred',
      });
    }
  };
}
