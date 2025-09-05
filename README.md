# 🏦 MIMS - Microfinance Information Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)

A comprehensive microfinance management system designed for African markets, featuring traditional Susu accounts, modern banking operations, and mobile-first design.

## 🌟 **Features**

### **Core Functionality**
- 👥 **Customer Management** - Complete customer lifecycle management
- 💰 **Account Types** - Current, Savings, and Traditional Susu accounts
- 📊 **Transaction Recording** - Real-time transaction processing
- 🏦 **Loan Management** - End-to-end loan processing and tracking
- 📄 **PDF Reports** - Automated report generation
- 📱 **Mobile Responsive** - Optimized for all devices

### **Advanced Features**
- 🔐 **Secure Authentication** - Role-based access control
- 📈 **Dashboard Analytics** - Real-time business insights
- 🖨️ **Print Management** - Comprehensive printing capabilities
- 🔍 **Search & Filter** - Advanced data filtering
- 📊 **Financial Reporting** - Detailed financial analytics

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Composer (for dependencies)

### **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/Konadu-Prince/mims-microfinance-system.git
   cd mims-microfinance-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a MySQL database named `mims`
   - Import the database schema (if available)
   - Update database credentials in `database/db_connection.php`

4. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

5. **Set Permissions**
   ```bash
   chmod -R 755 assets/
   chmod -R 755 uploads/
   ```

6. **Access the Application**
   - Navigate to your web server URL
   - Default login credentials (change after first login)

## 🏗️ **System Architecture**

```
mims-microfinance-system/
├── app/                    # Application handlers
├── assets/                 # Static assets (CSS, JS, images)
├── database/              # Database connection and schemas
├── include/               # Shared components (header, footer, etc.)
├── tcpdf/                 # PDF generation library
├── vendor/                # Composer dependencies
├── *.php                  # Main application files
└── README.md              # This file
```

## 📊 **Database Schema**

### **Core Tables**
- `customers` - Customer information
- `accounts` - Account details
- `transactions` - Transaction records
- `loans` - Loan information
- `account_type` - Account type definitions
- `customer_type` - Customer type definitions

## 🔧 **Configuration**

### **Database Configuration**
Update `database/db_connection.php` with your database credentials:

```php
<?php
$conn = new mysqli('localhost', 'username', 'password', 'mims');
?>
```

### **Environment Variables**
Create a `.env` file for production:

```env
DB_HOST=localhost
DB_NAME=mims
DB_USERNAME=your_username
DB_PASSWORD=your_password
APP_ENV=production
APP_DEBUG=false
```

## 📱 **Mobile Support**

The system is fully responsive and optimized for mobile devices:
- Touch-friendly interface
- Mobile-optimized forms
- Responsive data tables
- Mobile navigation

## 🔐 **Security Features**

- **Input Validation** - All user inputs are validated
- **SQL Injection Protection** - Prepared statements used
- **Session Management** - Secure session handling
- **Access Control** - Role-based permissions
- **Data Encryption** - Sensitive data encryption

## 📈 **Business Intelligence**

### **Dashboard Metrics**
- Total customers
- Active accounts
- Transaction volume
- Loan portfolio
- Account type distribution

### **Reporting**
- Customer reports
- Transaction reports
- Loan reports
- Financial summaries
- PDF export capabilities

## 🌍 **Localization**

The system supports multiple languages and is optimized for African markets:
- English (default)
- Local language support ready
- Currency formatting
- Date/time localization

## 🚀 **Deployment**

### **Production Deployment**
See [DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md) for detailed deployment instructions.

### **Docker Deployment**
```bash
docker-compose up -d
```

### **Cloud Deployment**
- AWS/Azure/GCP ready
- DigitalOcean/Linode compatible
- Heroku deployment supported

## 💰 **Monetization**

The system is designed for SaaS deployment with multiple revenue streams:
- Subscription-based pricing
- Transaction fees
- Premium features
- White-label licensing

See [DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md) for detailed monetization strategy.

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 **Support**

- **Documentation:** [DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md)
- **Issues:** [GitHub Issues](https://github.com/Konadu-Prince/mims-microfinance-system/issues)
- **Email:** [Your Email]

## 🙏 **Acknowledgments**

- TCPDF for PDF generation
- Bootstrap for UI framework
- AdminLTE for admin interface
- FontAwesome for icons

## 📊 **Project Status**

- ✅ Core functionality complete
- ✅ Basic security implemented
- 🔄 API development in progress
- 📋 Mobile app planned
- 🚀 Deployment optimization ongoing

---

**Built with ❤️ for African microfinance institutions**
