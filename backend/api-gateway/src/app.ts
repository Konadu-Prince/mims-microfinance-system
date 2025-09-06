import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import rateLimit from 'express-rate-limit';
import { createProxyMiddleware } from 'http-proxy-middleware';
import { errorHandler } from './middleware/errorHandler';
import { authMiddleware } from './middleware/auth';
import { requestLogger } from './middleware/logger';
import { validateApiKey } from './middleware/apiKey';
import { config } from './config/environment';

const app = express();

// Security middleware
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      scriptSrc: ["'self'"],
      imgSrc: ["'self'", "data:", "https:"],
    },
  },
}));

// CORS configuration
app.use(cors({
  origin: config.allowedOrigins,
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key'],
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 1000, // limit each IP to 1000 requests per windowMs
  message: 'Too many requests from this IP, please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

// Request logging
app.use(requestLogger);

// Health check endpoint
app.get('/health', (req, res) => {
  res.status(200).json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    version: process.env.npm_package_version || '1.0.0',
    environment: config.nodeEnv,
  });
});

// API documentation
app.get('/docs', (req, res) => {
  res.redirect('/api-docs');
});

// Service routing with authentication
app.use('/api/v1/auth', createProxyMiddleware({
  target: config.services.auth.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/auth': '' },
  onError: (err, req, res) => {
    console.error('Auth service error:', err);
    res.status(503).json({ error: 'Authentication service unavailable' });
  },
}));

app.use('/api/v1/users', authMiddleware, createProxyMiddleware({
  target: config.services.user.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/users': '' },
}));

app.use('/api/v1/customers', authMiddleware, createProxyMiddleware({
  target: config.services.customer.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/customers': '' },
}));

app.use('/api/v1/accounts', authMiddleware, createProxyMiddleware({
  target: config.services.account.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/accounts': '' },
}));

app.use('/api/v1/transactions', authMiddleware, createProxyMiddleware({
  target: config.services.transaction.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/transactions': '' },
}));

app.use('/api/v1/loans', authMiddleware, createProxyMiddleware({
  target: config.services.loan.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/loans': '' },
}));

app.use('/api/v1/analytics', authMiddleware, createProxyMiddleware({
  target: config.services.analytics.url,
  changeOrigin: true,
  pathRewrite: { '^/api/v1/analytics': '' },
}));

// WebSocket support for real-time features
app.use('/ws', createProxyMiddleware({
  target: config.services.websocket.url,
  ws: true,
  changeOrigin: true,
}));

// Error handling
app.use(errorHandler);

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({
    error: 'Not Found',
    message: `Route ${req.originalUrl} not found`,
    timestamp: new Date().toISOString(),
  });
});

export default app;
