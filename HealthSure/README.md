# HealthSure - Health Insurance Management System

A comprehensive web-based platform for managing health insurance policies, claims, and payments with role-based access for customers, agents, and administrators.

## 🚀 Features

### Customer Portal
- **Policy Management**: Browse, apply for, and manage insurance policies
- **Claims Processing**: File new claims and track claim status
- **Payment System**: Make premium payments and view payment history
- **Profile Management**: Update personal information and preferences
- **Support System**: Contact support and access FAQ

### Agent Interface
- **Customer Management**: Register and assist customers
- **Policy Sales**: Help customers choose and apply for policies
- **Claims Assistance**: Support customers with claim filing and tracking
- **Performance Reports**: View sales and performance metrics

### Admin Dashboard
- **Policy Management**: Create, edit, and manage insurance policies
- **User Management**: Manage customers, agents, and system users
- **Claims Administration**: Review, approve, or reject claims
- **Payment Oversight**: Monitor all payments and transactions
- **Reports & Analytics**: Generate comprehensive system reports
- **System Settings**: Configure system parameters and settings

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Web Server**: Apache (XAMPP)
- **Styling**: Custom CSS with modern design principles

## 📋 Prerequisites

- XAMPP (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Safari, Edge)
- Text editor or IDE (optional, for customization)

## 🔧 Installation & Setup

### 1. Download and Install XAMPP
- Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
- Install XAMPP and start Apache and MySQL services

### 2. Setup the Project
1. Copy the `HealthSure` folder to `C:\xampp\htdocs\`
2. Open your web browser and go to `http://localhost/phpmyadmin`
3. Create a new database named `healthsure_db`
4. Import the database schema:
   - Go to the `healthsure_db` database
   - Click on "SQL" tab
   - Copy and paste the contents of `config/init_db.sql`
   - Click "Go" to execute

### 3. Configure Database Connection
- Open `config/database.php`
- Update database credentials if needed (default: localhost, root, no password)

### 4. Access the Application
- Open your web browser
- Go to `http://localhost/HealthSure`
- You'll be redirected to the login page

## 👤 Default Login Credentials

### Admin Account
- **Email**: admin@healthsure.com
- **Password**: password

### Creating Additional Accounts
- **Customers**: Can register themselves via the registration page
- **Agents**: Must be created by admin through the admin panel

## 📊 Database Schema

The system uses a relational database with the following key entities:

### Core Tables
- `users` - Authentication and user roles
- `customers` - Customer profile information
- `agents` - Agent profile information
- `policies` - Insurance policy master data

### Policy Specialization (Inheritance)
- `health_policies` - Health insurance specific attributes
- `life_policies` - Life insurance specific attributes  
- `family_policies` - Family insurance specific attributes

### Operational Tables
- `policy_holders` - Links customers to their policies
- `claims` - Insurance claim records
- `payments` - Payment transactions
- `support_queries` - Customer support tickets

## 🔐 Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Session Management**: Secure session handling with role-based access
- **Input Validation**: Comprehensive input sanitization and validation
- **SQL Injection Protection**: Prepared statements for all database queries
- **Role-based Access Control**: Separate interfaces for different user roles

## 📱 Responsive Design

The application features a modern, responsive design that works seamlessly across:
- Desktop computers
- Tablets
- Mobile devices

## 🎨 UI/UX Features

- **Modern Interface**: Clean, professional design with intuitive navigation
- **Dashboard Analytics**: Visual statistics and charts for key metrics
- **Real-time Updates**: Dynamic content updates without page refresh
- **Mobile-first Design**: Optimized for mobile devices with touch-friendly interface
- **Accessibility**: Designed with accessibility best practices

## 📈 Key Functionalities

### Policy Management
- Multiple policy types (Health, Life, Family)
- Flexible premium calculation
- Policy renewal and cancellation
- Coverage amount tracking

### Claims Processing
- Online claim filing with document upload
- Multi-stage approval workflow
- Claim status tracking
- Automated notifications

### Payment System
- Multiple payment methods support
- Payment history tracking
- Premium due notifications
- Receipt generation

### Reporting System
- Sales performance reports
- Claims analytics
- Payment tracking
- Customer insights

## 🔄 Workflow

1. **Customer Registration**: New customers sign up and complete their profile
2. **Policy Selection**: Customers browse and apply for insurance policies
3. **Agent Assignment**: Agents can be assigned to assist customers
4. **Premium Payment**: Customers pay premiums through the system
5. **Claim Filing**: Customers file claims when needed
6. **Claim Processing**: Admin reviews and processes claims
7. **Settlement**: Approved claims are settled and recorded

## 🛡️ Data Protection

- Sensitive data encryption
- Secure file upload handling
- Regular database backups recommended
- User activity logging
- Privacy compliance features

## 📞 Support

For technical support or questions:
- Check the in-app support section
- Review the FAQ page
- Contact system administrator

## 🔮 Future Enhancements

- Email notification system
- SMS integration
- Payment gateway integration (Razorpay/PayPal)
- Advanced reporting with charts
- Mobile app development
- AI-powered claim processing
- Chatbot integration

## 📄 License

This project is developed for educational and demonstration purposes. Please ensure compliance with local regulations when deploying in production environments.

## 🤝 Contributing

This is a demonstration project. For production use, consider:
- Adding comprehensive error handling
- Implementing email notifications
- Adding payment gateway integration
- Enhancing security measures
- Adding automated testing
- Implementing caching mechanisms

---

**HealthSure** - Simplifying Insurance Management Through Technology
