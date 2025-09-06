import bcrypt from 'bcryptjs';
import { UserService } from './UserService';
import { TokenService } from './TokenService';
import { EmailService } from './EmailService';
import { TwoFactorService } from './TwoFactorService';
import { AuditService } from './AuditService';
import { logger } from '../utils/logger';
import { User } from '../types/User';
import { AuthResult } from '../types/AuthResult';

export class AuthService {
  private userService: UserService;
  private tokenService: TokenService;
  private emailService: EmailService;
  private twoFactorService: TwoFactorService;
  private auditService: AuditService;

  constructor() {
    this.userService = new UserService();
    this.tokenService = new TokenService();
    this.emailService = new EmailService();
    this.twoFactorService = new TwoFactorService();
    this.auditService = new AuditService();
  }

  /**
   * Authenticate user with email and password
   */
  public async authenticate(email: string, password: string): Promise<AuthResult> {
    try {
      // Find user by email
      const user = await this.userService.findByEmail(email);
      if (!user) {
        return {
          success: false,
          message: 'Invalid email or password',
        };
      }

      // Check if user is active
      if (!user.isActive) {
        return {
          success: false,
          message: 'Account is deactivated',
        };
      }

      // Verify password
      const isPasswordValid = await bcrypt.compare(password, user.password);
      if (!isPasswordValid) {
        // Log failed login attempt
        await this.auditService.logFailedLogin(user.id, email);
        return {
          success: false,
          message: 'Invalid email or password',
        };
      }

      // Check if 2FA is enabled
      const requiresTwoFactor = user.twoFactorEnabled;

      // Log successful login
      await this.auditService.logSuccessfulLogin(user.id, email);

      return {
        success: true,
        user,
        requiresTwoFactor,
      };
    } catch (error) {
      logger.error('Authentication error:', error);
      return {
        success: false,
        message: 'Authentication failed due to server error',
      };
    }
  }

  /**
   * Verify two-factor authentication
   */
  public async verifyTwoFactor(userId: string, token: string): Promise<AuthResult> {
    try {
      const user = await this.userService.findById(userId);
      if (!user) {
        return {
          success: false,
          message: 'User not found',
        };
      }

      const isValid = await this.twoFactorService.verifyToken(userId, token);
      if (!isValid) {
        await this.auditService.logFailedTwoFactor(userId);
        return {
          success: false,
          message: 'Invalid two-factor authentication code',
        };
      }

      await this.auditService.logSuccessfulTwoFactor(userId);

      return {
        success: true,
        user,
        requiresTwoFactor: false,
      };
    } catch (error) {
      logger.error('Two-factor verification error:', error);
      return {
        success: false,
        message: 'Two-factor verification failed',
      };
    }
  }

  /**
   * Send verification email
   */
  public async sendVerificationEmail(email: string, token: string): Promise<void> {
    try {
      const verificationUrl = `${process.env.FRONTEND_URL}/verify-email?token=${token}`;
      
      await this.emailService.sendEmail({
        to: email,
        subject: 'Verify Your Email Address',
        template: 'email-verification',
        data: {
          verificationUrl,
          token,
        },
      });

      logger.info(`Verification email sent to: ${email}`);
    } catch (error) {
      logger.error('Failed to send verification email:', error);
      throw new Error('Failed to send verification email');
    }
  }

  /**
   * Send password reset email
   */
  public async sendPasswordResetEmail(email: string, token: string): Promise<void> {
    try {
      const resetUrl = `${process.env.FRONTEND_URL}/reset-password?token=${token}`;
      
      await this.emailService.sendEmail({
        to: email,
        subject: 'Reset Your Password',
        template: 'password-reset',
        data: {
          resetUrl,
          token,
        },
      });

      logger.info(`Password reset email sent to: ${email}`);
    } catch (error) {
      logger.error('Failed to send password reset email:', error);
      throw new Error('Failed to send password reset email');
    }
  }

  /**
   * Reset password with token
   */
  public async resetPassword(token: string, newPassword: string): Promise<boolean> {
    try {
      // Verify reset token
      const tokenResult = await this.tokenService.verifyPasswordResetToken(token);
      if (!tokenResult.valid) {
        return false;
      }

      // Hash new password
      const hashedPassword = await bcrypt.hash(newPassword, 12);

      // Update user password
      await this.userService.updatePassword(tokenResult.userId, hashedPassword);

      // Revoke the reset token
      await this.tokenService.revokePasswordResetToken(token);

      // Log password reset
      await this.auditService.logPasswordReset(tokenResult.userId);

      logger.info(`Password reset successfully for user: ${tokenResult.userId}`);
      return true;
    } catch (error) {
      logger.error('Password reset error:', error);
      return false;
    }
  }

  /**
   * Change password for authenticated user
   */
  public async changePassword(
    userId: string,
    currentPassword: string,
    newPassword: string
  ): Promise<boolean> {
    try {
      const user = await this.userService.findById(userId);
      if (!user) {
        return false;
      }

      // Verify current password
      const isCurrentPasswordValid = await bcrypt.compare(currentPassword, user.password);
      if (!isCurrentPasswordValid) {
        return false;
      }

      // Hash new password
      const hashedPassword = await bcrypt.hash(newPassword, 12);

      // Update password
      await this.userService.updatePassword(userId, hashedPassword);

      // Log password change
      await this.auditService.logPasswordChange(userId);

      logger.info(`Password changed successfully for user: ${userId}`);
      return true;
    } catch (error) {
      logger.error('Password change error:', error);
      return false;
    }
  }

  /**
   * Enable two-factor authentication
   */
  public async enableTwoFactor(userId: string): Promise<{ secret: string; qrCode: string }> {
    try {
      const { secret, qrCode } = await this.twoFactorService.generateSecret(userId);
      
      // Update user 2FA status
      await this.userService.enableTwoFactor(userId);

      // Log 2FA enablement
      await this.auditService.logTwoFactorEnabled(userId);

      logger.info(`Two-factor authentication enabled for user: ${userId}`);
      
      return { secret, qrCode };
    } catch (error) {
      logger.error('Two-factor enablement error:', error);
      throw new Error('Failed to enable two-factor authentication');
    }
  }

  /**
   * Disable two-factor authentication
   */
  public async disableTwoFactor(userId: string, password: string): Promise<boolean> {
    try {
      const user = await this.userService.findById(userId);
      if (!user) {
        return false;
      }

      // Verify password
      const isPasswordValid = await bcrypt.compare(password, user.password);
      if (!isPasswordValid) {
        return false;
      }

      // Disable 2FA
      await this.userService.disableTwoFactor(userId);
      await this.twoFactorService.disableForUser(userId);

      // Log 2FA disablement
      await this.auditService.logTwoFactorDisabled(userId);

      logger.info(`Two-factor authentication disabled for user: ${userId}`);
      return true;
    } catch (error) {
      logger.error('Two-factor disablement error:', error);
      return false;
    }
  }

  /**
   * Validate session and return user
   */
  public async validateSession(sessionToken: string): Promise<User | null> {
    try {
      const tokenResult = await this.tokenService.verifyAccessToken(sessionToken);
      if (!tokenResult.valid) {
        return null;
      }

      const user = await this.userService.findById(tokenResult.userId);
      if (!user || !user.isActive) {
        return null;
      }

      return user;
    } catch (error) {
      logger.error('Session validation error:', error);
      return null;
    }
  }

  /**
   * Logout user from all devices
   */
  public async logoutAllDevices(userId: string): Promise<void> {
    try {
      // Revoke all refresh tokens for user
      await this.tokenService.revokeAllRefreshTokens(userId);

      // Log logout all devices
      await this.auditService.logLogoutAllDevices(userId);

      logger.info(`User logged out from all devices: ${userId}`);
    } catch (error) {
      logger.error('Logout all devices error:', error);
      throw new Error('Failed to logout from all devices');
    }
  }
}
