# 🔌 MIMS API Documentation

## Overview

The MIMS API provides RESTful endpoints for managing microfinance operations. This documentation covers the API endpoints, authentication, and usage examples.

## Base URL

```
Production: https://your-domain.com/api/v1
Development: http://localhost/api/v1
```

## Authentication

### API Key Authentication
```http
Authorization: Bearer YOUR_API_KEY
```

### Session Authentication
```http
Cookie: PHPSESSID=your_session_id
```

## Endpoints

### Authentication

#### Login
```http
POST /api/v1/auth/login
```

**Request Body:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "username": "admin",
    "role": "admin"
  }
}
```

#### Logout
```http
POST /api/v1/auth/logout
```

### Customers

#### Get All Customers
```http
GET /api/v1/customers
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `limit` (optional): Number of records per page
- `search` (optional): Search term for customer name

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "customer_number": "CUST001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "+233123456789",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_records": 100
  }
}
```

#### Create Customer
```http
POST /api/v1/customers
```

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+233123456789",
  "address": "123 Main St",
  "customer_type": 1
}
```

#### Get Customer by ID
```http
GET /api/v1/customers/{id}
```

#### Update Customer
```http
PUT /api/v1/customers/{id}
```

#### Delete Customer
```http
DELETE /api/v1/customers/{id}
```

### Accounts

#### Get All Accounts
```http
GET /api/v1/accounts
```

#### Create Account
```http
POST /api/v1/accounts
```

**Request Body:**
```json
{
  "customer_id": 1,
  "account_type": 1,
  "initial_balance": 1000.00
}
```

#### Get Account by ID
```http
GET /api/v1/accounts/{id}
```

#### Update Account
```http
PUT /api/v1/accounts/{id}
```

### Transactions

#### Get All Transactions
```http
GET /api/v1/transactions
```

#### Create Transaction
```http
POST /api/v1/transactions
```

**Request Body:**
```json
{
  "customer_id": 1,
  "account_id": 1,
  "amount": 500.00,
  "transaction_type": "deposit",
  "description": "Initial deposit"
}
```

#### Get Transaction by ID
```http
GET /api/v1/transactions/{id}
```

### Loans

#### Get All Loans
```http
GET /api/v1/loans
```

#### Create Loan
```http
POST /api/v1/loans
```

**Request Body:**
```json
{
  "customer_id": 1,
  "amount": 5000.00,
  "interest_rate": 15.5,
  "term_months": 12,
  "purpose": "Business expansion"
}
```

#### Get Loan by ID
```http
GET /api/v1/loans/{id}
```

#### Update Loan Status
```http
PUT /api/v1/loans/{id}/status
```

**Request Body:**
```json
{
  "status": "approved",
  "notes": "Loan approved after review"
}
```

### Reports

#### Generate Report
```http
POST /api/v1/reports/generate
```

**Request Body:**
```json
{
  "report_type": "customer_summary",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "format": "pdf"
}
```

## Error Handling

### Error Response Format
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": {
      "field": "email",
      "message": "Invalid email format"
    }
  }
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting

- **Free Tier:** 100 requests per hour
- **Basic Tier:** 1,000 requests per hour
- **Professional Tier:** 10,000 requests per hour
- **Enterprise:** Unlimited

## SDKs and Libraries

### PHP SDK
```php
<?php
require_once 'vendor/autoload.php';

use MIMS\Api\Client;

$client = new Client('YOUR_API_KEY');
$customers = $client->customers()->all();
?>
```

### JavaScript SDK
```javascript
import { MIMSClient } from '@mims/api-client';

const client = new MIMSClient('YOUR_API_KEY');
const customers = await client.customers.getAll();
```

## Webhooks

### Available Events
- `customer.created`
- `customer.updated`
- `transaction.created`
- `loan.approved`
- `loan.rejected`

### Webhook Payload
```json
{
  "event": "customer.created",
  "data": {
    "customer_number": "CUST001",
    "first_name": "John",
    "last_name": "Doe"
  },
  "timestamp": "2024-01-01T00:00:00Z"
}
```

## Testing

### Postman Collection
Download our Postman collection for easy API testing:
[Download Collection](https://your-domain.com/api/postman-collection.json)

### Sandbox Environment
Use our sandbox environment for testing:
```
Base URL: https://sandbox.your-domain.com/api/v1
API Key: sandbox_key_here
```

## Support

For API support and questions:
- **Email:** api-support@your-domain.com
- **Documentation:** https://docs.your-domain.com
- **Status Page:** https://status.your-domain.com
