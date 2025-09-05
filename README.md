# 🏦 MIMS - Microfinance Information Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](https://docker.com)

A comprehensive microfinance management system designed for African markets, featuring traditional Susu accounts, modern banking operations, and mobile-first design.

## 🌟 **Features**

### **Core Functionality**
- 👥 **Customer Management** - Complete customer lifecycle management
- 💰 **Account Types** - Current, Savings, and Traditional Susu accounts
- 📊 **Transaction Recording** - Real-time transaction processing
- 🏦 **Loan Management** - End-to-end loan processing and tracking
- 📄 **PDF Reports** - Automated report generation with TCPDF
- 📱 **Mobile Responsive** - Optimized for all devices

### **Advanced Features**
- 🔐 **Secure Authentication** - Role-based access control
- 📈 **Dashboard Analytics** - Real-time business insights
- 🖨️ **Print Management** - Comprehensive printing capabilities
- 🔍 **Search & Filter** - Advanced data filtering
- 📊 **Financial Reporting** - Detailed financial analytics
- 🌍 **Multi-language Support** - Localized for African markets

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
   cp env.example .env
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

## 🐳 **Docker Deployment**

### **Quick Start with Docker**
```bash
# Clone the repository
git clone https://github.com/Konadu-Prince/mims-microfinance-system.git
cd mims-microfinance-system

# Start all services
docker-compose up -d

# Access the application
# Web: http://localhost
# phpMyAdmin: http://localhost:8080
```

### **Docker Services**
- **Web Server:** Nginx with PHP-FPM
- **Database:** MySQL 8.0
- **Cache:** Redis
- **Admin:** phpMyAdmin (development only)

## 🏗️ **System Architecture**

```
mims-microfinance-system/
├── app/                    # Application handlers
│   ├── addcustomerHandler.php
│   ├── addTransactionHandler.php
│   ├── loginHandler.php
│   └── ...
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   ├── img/               # Images
│   └── vendor/            # Third-party assets
├── database/              # Database connection
│   └── db_connection.php
├── include/               # Shared components
│   ├── head.php          # HTML head
│   ├── navbar.php        # Navigation
│   ├── sidebar.php       # Sidebar menu
│   └── footer.php        # Footer
├── tcpdf/                # PDF generation library
├── vendor/               # Composer dependencies
├── *.php                 # Main application files
├── Dockerfile            # Docker configuration
├── docker-compose.yml    # Docker services
└── README.md             # This file
```

## 📊 **Database Schema**

### **Core Tables**
- `customers` - Customer information and profiles
- `accounts` - Account details and balances
- `transactions` - Transaction records and history
- `loans` - Loan information and status
- `account_type` - Account type definitions
- `customer_type` - Customer type definitions
- `account_status` - Account status management

### **Key Relationships**
- Customers can have multiple accounts
- Accounts can have multiple transactions
- Loans are linked to customers
- Account types define account behavior

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
# Database
DB_HOST=localhost
DB_NAME=mims
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Security
APP_KEY=your-32-character-secret-key
```

## 📱 **Mobile Support**

The system is fully responsive and optimized for mobile devices:
- **Touch-friendly interface** - Easy navigation on smartphones
- **Mobile-optimized forms** - Streamlined data entry
- **Responsive data tables** - Readable on small screens
- **Mobile navigation** - Collapsible sidebar menu
- **Offline capability** - Works without internet connection

## 🔐 **Security Features**

- **Input Validation** - All user inputs are validated and sanitized
- **SQL Injection Protection** - Prepared statements used throughout
- **Session Management** - Secure session handling with proper timeouts
- **Access Control** - Role-based permissions and authentication
- **Data Encryption** - Sensitive data encryption at rest
- **CSRF Protection** - Cross-site request forgery prevention
- **XSS Prevention** - Output sanitization and encoding

## 📈 **Business Intelligence**

### **Dashboard Metrics**
- **Total Customers** - Active customer count
- **Opened Accounts** - Account statistics by type
- **Transaction Volume** - Financial transaction metrics
- **Loan Portfolio** - Loan status and performance
- **Account Distribution** - Breakdown by account types

### **Reporting Capabilities**
- **Customer Reports** - Detailed customer information
- **Transaction Reports** - Financial transaction summaries
- **Loan Reports** - Loan performance and status
- **Financial Summaries** - Overall financial health
- **PDF Export** - Professional report generation

## 🌍 **Localization**

The system supports multiple languages and is optimized for African markets:
- **English** (default language)
- **Local language support** ready for implementation
- **Currency formatting** for local currencies
- **Date/time localization** for regional preferences
- **Susu account support** - Traditional African savings method

## 🚀 **Deployment Options**

### **Traditional Hosting**
- **Shared Hosting** - Basic shared hosting providers
- **VPS Hosting** - Virtual private servers
- **Dedicated Servers** - Full server control

### **Cloud Deployment**
- **AWS** - Amazon Web Services
- **Azure** - Microsoft Azure
- **Google Cloud** - Google Cloud Platform
- **DigitalOcean** - Simple cloud hosting
- **Heroku** - Platform-as-a-Service

### **Container Deployment**
- **Docker** - Containerized deployment
- **Kubernetes** - Container orchestration
- **Docker Compose** - Multi-container setup

## 💰 **Monetization**

The system is designed for SaaS deployment with multiple revenue streams:

### **Subscription Tiers**
- **Basic:** $29/month (up to 100 customers)
- **Professional:** $79/month (up to 500 customers)
- **Enterprise:** $199/month (unlimited customers)

### **Revenue Streams**
- **Subscription Fees** - Monthly/annual subscriptions
- **Transaction Fees** - Per-transaction processing fees
- **Premium Features** - Advanced reporting and API access
- **White-label Licensing** - Custom branding for institutions
- **Training & Support** - Implementation and training services

## 📚 **Documentation**

- **[Deployment Plan](DEPLOYMENT_PLAN.md)** - Complete deployment and monetization strategy
- **[API Documentation](API_DOCUMENTATION.md)** - REST API reference and examples
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation and best practices
- **[Docker Guide](docker-compose.yml)** - Container deployment instructions

## 🤝 **Contributing**

We welcome contributions to improve the MIMS system:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### **Development Guidelines**
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## 🧪 **Testing**

### **Manual Testing**
- Test all user workflows
- Verify data integrity
- Check responsive design
- Validate security measures

### **Automated Testing**
```bash
# Run PHP unit tests (when implemented)
composer test

# Run security scans
composer security-check
```

## 📝 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 **Support**

### **Documentation**
- **Deployment Guide:** [DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md)
- **API Reference:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Security Guide:** [SECURITY_GUIDE.md](SECURITY_GUIDE.md)

### **Community**
- **Issues:** [GitHub Issues](https://github.com/Konadu-Prince/mims-microfinance-system/issues)
- **Discussions:** [GitHub Discussions](https://github.com/Konadu-Prince/mims-microfinance-system/discussions)
- **Email:** [Your Contact Email]

### **Professional Support**
- **Implementation Services** - Full system setup and configuration
- **Training Programs** - Staff training and user education
- **Custom Development** - Feature customization and integration
- **Maintenance & Support** - Ongoing system maintenance

## 🙏 **Acknowledgments**

- **TCPDF** - PDF generation library
- **Bootstrap** - UI framework and components
- **AdminLTE** - Admin dashboard template
- **FontAwesome** - Icon library
- **PHP Community** - Open source PHP ecosystem

## 📊 **Project Status**

- ✅ **Core functionality** - Complete and tested
- ✅ **Basic security** - Implemented and documented
- ✅ **Documentation** - Comprehensive guides available
- ✅ **Docker support** - Containerized deployment ready
- 🔄 **API development** - In progress
- 📋 **Mobile app** - Planned for Phase 2
- 🚀 **Deployment optimization** - Ongoing improvements

## 🎯 **Roadmap**

### **Phase 1: Foundation (Completed)**
- ✅ Core microfinance features
- ✅ Basic security implementation
- ✅ Documentation and deployment guides

### **Phase 2: Enhancement (In Progress)**
- 🔄 REST API development
- 🔄 Advanced security features
- 🔄 Performance optimization

### **Phase 3: Expansion (Planned)**
- 📋 Mobile application development
- 📋 Advanced analytics and reporting
- 📋 Multi-tenancy support

### **Phase 4: Scale (Future)**
- 📋 Machine learning integration
- 📋 Advanced compliance features
- 📋 International expansion

---

## 🌟 **Why Choose MIMS?**

### **Built for African Markets**
- **Susu Account Support** - Traditional savings method
- **Mobile-First Design** - Optimized for smartphones
- **Offline Capability** - Works without internet
- **Local Language Ready** - Multi-language support
- **Affordable Pricing** - Competitive for microfinance

### **Enterprise-Ready**
- **Scalable Architecture** - Grows with your business
- **Security-First** - Bank-level security standards
- **Comprehensive Reporting** - Detailed business insights
- **Easy Integration** - REST API for third-party systems
- **Professional Support** - Implementation and training services

---

**Built with ❤️ for African microfinance institutions**

*Empowering financial inclusion through technology*