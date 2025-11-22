# SplashHR

**Multi-tenant HR & Payroll SaaS Platform**

A complete, production-ready HR and Payroll management system built with pure PHP and MySQL. Designed for small and medium businesses with multi-tenancy support, comprehensive HR features, and RESTful API integration.

![PHP Version](https://img.shields.io/badge/PHP-7.0%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)

---

## Features

### Core Capabilities
- **Multi-Tenant Architecture** - Single database, tenant-isolated data
- **Role-Based Access Control** - Platform Admin, Tenant Admin, Manager, Employee
- **Subscription Management** - Plans with usage limits and quota enforcement
- **RESTful API** - Employee and Attendance endpoints with API key authentication

### HR Management
- **Employee Management** - Complete employee lifecycle management
- **Department & Job Titles** - Organizational structure management
- **Attendance Tracking** - Clock in/out, overtime, late arrivals tracking
- **Leave Management** - Leave requests with approval workflow
- **Payroll Processing** - Automated monthly payroll with deductions
- **Loan Management** - Employee loans with installment tracking
- **Document Management** - File uploads for employee documents

### Employee Self-Service
- Personal dashboard
- Attendance history
- Leave request submission
- Payslip viewing
- Profile management

### Dashboards & Analytics
- Tenant dashboard with KPIs
- Platform admin dashboard
- Attendance summaries
- Birthday reminders
- Pending leave requests

---

## Technology Stack

- **Backend**: PHP 7.0+ (OOP, MVC pattern)
- **Database**: MySQL 5.7+ with InnoDB
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Authentication**: Session-based with password hashing
- **Security**: CSRF protection, prepared statements, input validation

---

## Installation

### Prerequisites

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP Extensions: PDO, pdo_mysql

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/SplashHR.git
cd SplashHR
```

### Step 2: Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your database connection:

```env
DB_HOST=localhost
DB_DATABASE=splashhr
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 3: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splashhr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 4: Import Database Schema

```bash
mysql -u root -p splashhr < database.sql
```

This will create all tables and import demo data including:
- 1 Platform Admin user
- 2 Demo Tenants (TechCorp and Global Innovations)
- Sample employees, attendance, payroll runs, etc.

### Step 5: Set File Permissions

```bash
chmod -R 775 storage/uploads
chown -R www-data:www-data storage/uploads
```

### Step 6: Configure Web Server

#### Apache

The `.htaccess` files are already included. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Point your virtual host document root to the `/public` directory.

#### Nginx

Add this to your Nginx configuration:

```nginx
server {
    listen 80;
    server_name splashhr.local;
    root /path/to/SplashHR/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 7: Access the Application

Visit: `http://localhost/SplashHR` or your configured domain.

---

## Demo Credentials

### Platform Admin
- **Email**: admin@splashhr.com
- **Password**: password
- **Access**: Manage all tenants, view platform statistics

### Tenant Admin (TechCorp)
- **Email**: michael.brown@techcorp.com
- **Password**: password
- **Access**: Full HR management for TechCorp

### Manager (TechCorp)
- **Email**: john.smith@techcorp.com
- **Password**: password
- **Access**: View reports, approve leaves

### Employee (TechCorp)
- **Email**: sarah.johnson@techcorp.com
- **Password**: password
- **Access**: Self-service portal

---

## Project Structure

```
SplashHR/
├── app/
│   ├── controllers/      # All controller classes
│   ├── models/           # All model classes
│   ├── views/            # View templates
│   │   ├── layouts/      # Layout templates
│   │   ├── partials/     # Reusable partials
│   │   ├── auth/         # Authentication views
│   │   ├── dashboard/    # Dashboard views
│   │   ├── employees/    # Employee views
│   │   ├── attendance/   # Attendance views
│   │   ├── leaves/       # Leave views
│   │   ├── payroll/      # Payroll views
│   │   └── ...
│   ├── core/             # Core framework classes
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Database.php
│   │   ├── Auth.php
│   │   └── ...
│   └── helpers/          # Helper functions
├── public/               # Public web root
│   ├── index.php         # Application entry point
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── .htaccess
├── config/               # Configuration files
├── storage/              # File uploads
│   └── uploads/
├── tests/                # Test scripts
├── database.sql          # Database schema and seed data
├── .env.example          # Environment configuration example
├── .htaccess             # Apache rewrite rules
└── README.md
```

---

## API Documentation

SplashHR provides RESTful API endpoints for integration with external systems.

### Authentication

All API requests require an API key sent in the `X-API-KEY` header:

```bash
X-API-KEY: your_api_key_here
```

API keys can be found in the `api_keys` table. Demo keys:
- **TechCorp**: `demo_key_techcorp_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567`

### Endpoints

#### Get Employees

```http
GET /api/employees
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Results per page (default: 20, max: 100)
- `department_id` (optional): Filter by department
- `status` (optional): Filter by status (active, terminated, etc.)
- `search` (optional): Search by name or code

**Example Request:**

```bash
curl -X GET "http://localhost/api/employees?page=1&per_page=20&status=active" \
  -H "X-API-KEY: your_api_key"
```

**Example Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_code": "EMP001",
      "first_name": "John",
      "last_name": "Smith",
      "email": "john.smith@techcorp.com",
      "phone": "+1-555-0101",
      "department": "Engineering",
      "job_title": "Senior Software Engineer",
      "employment_type": "full-time",
      "status": "active",
      "hire_date": "2020-01-15"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 6,
    "total_pages": 1
  }
}
```

#### Get Single Employee

```http
GET /api/employees/:id
```

**Example Request:**

```bash
curl -X GET "http://localhost/api/employees/1" \
  -H "X-API-KEY: your_api_key"
```

#### Submit Attendance

```http
POST /api/attendance
```

**Request Body:**

```json
{
  "employee_code": "EMP001",
  "date": "2025-11-22",
  "check_in": "09:00:00",
  "check_out": "17:30:00"
}
```

**Example Request:**

```bash
curl -X POST "http://localhost/api/attendance" \
  -H "X-API-KEY: your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_code": "EMP001",
    "date": "2025-11-22",
    "check_in": "09:00:00",
    "check_out": "17:30:00"
  }'
```

**Example Response:**

```json
{
  "success": true,
  "message": "Attendance submitted successfully",
  "data": {
    "id": 123,
    "employee_code": "EMP001",
    "date": "2025-11-22",
    "total_hours": 8.5
  }
}
```

#### Get Attendance Records

```http
GET /api/attendance
```

**Query Parameters:**
- `page`, `per_page`: Pagination
- `employee_id`: Filter by employee
- `date_from`, `date_to`: Date range filter
- `month`, `year`: Filter by month/year

---

## Testing

### Run Basic Tests

```bash
php tests/test_basic.php
```

This will run functional tests for:
- Database connectivity
- Model operations
- Tenant isolation
- Helper functions

### Manual Testing Checklist

#### Authentication
- [ ] Login with different user roles
- [ ] Logout functionality
- [ ] Invalid credentials handling

#### Employee Management
- [ ] Create new employee
- [ ] Update employee details
- [ ] View employee profile
- [ ] Delete employee
- [ ] Search and filter employees

#### Attendance
- [ ] Record attendance
- [ ] Calculate overtime
- [ ] Mark late arrivals
- [ ] Monthly attendance reports

#### Leave Management
- [ ] Submit leave request
- [ ] Approve leave request
- [ ] Reject leave request
- [ ] View leave history

#### Payroll
- [ ] Process monthly payroll
- [ ] Generate payslips
- [ ] Verify salary calculations
- [ ] Include loan deductions

#### Tenant Isolation
- [ ] Verify data separation between tenants
- [ ] Test subscription limits
- [ ] Check employee quota enforcement

#### API Testing
- [ ] Test employee endpoints
- [ ] Test attendance submission
- [ ] Verify API authentication

---

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **SQL Injection Protection**: All queries use PDO prepared statements
- **CSRF Protection**: Token validation on all forms
- **Input Validation**: Server-side validation on all inputs
- **Input Sanitization**: HTML entities escaped on output
- **Session Security**: HTTP-only sessions
- **File Upload Security**: File type and size validation, sanitized filenames

---

## Performance Optimization

- **Database Indexes**: Proper indexing on frequently queried columns
- **Tenant Filtering**: All queries filtered by tenant_id
- **Pagination**: Limit results to prevent memory issues
- **Prepared Statements**: Query plan caching
- **Efficient Joins**: Minimize N+1 query problems

---

## Deployment

### Production Checklist

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Disable Error Display**
   ```php
   // In public/index.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Database Optimization**
   - Enable query caching
   - Optimize indexes
   - Regular backups

4. **Security Hardening**
   - Use HTTPS
   - Set secure session cookies
   - Implement rate limiting
   - Enable firewall

5. **File Permissions**
   ```bash
   chmod 755 /path/to/SplashHR
   chmod 775 storage/uploads
   chown -R www-data:www-data storage
   ```

6. **Backup Strategy**
   - Daily database backups
   - Weekly full system backups
   - Offsite backup storage

---

## Customization

### Adding New Modules

1. Create model in `/app/models/`
2. Create controller in `/app/controllers/`
3. Add routes in `/public/index.php`
4. Create views in `/app/views/`

### Extending Subscription Plans

Edit the subscription plans in the database:

```sql
INSERT INTO subscription_plans (name, code, price_monthly, max_employees, ...)
VALUES ('Custom Plan', 'custom', 199.00, 500, ...);
```

### Custom Reports

Create new report methods in controllers and corresponding views.

---

## Troubleshooting

### Common Issues

**Issue**: White screen / 500 error
- **Solution**: Check PHP error logs, ensure database credentials are correct

**Issue**: Routes not working
- **Solution**: Verify `mod_rewrite` is enabled (Apache), check `.htaccess` files

**Issue**: Database connection failed
- **Solution**: Verify `.env` database credentials, ensure MySQL is running

**Issue**: File uploads failing
- **Solution**: Check `storage/uploads` directory permissions (775)

**Issue**: CSS/JS not loading
- **Solution**: Verify `public/assets` directory exists and is accessible

---

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## Support

For issues, questions, or suggestions:
- **GitHub Issues**: https://github.com/yourusername/SplashHR/issues
- **Email**: support@splashhr.com
- **Documentation**: https://docs.splashhr.com

---

## Roadmap

- [ ] Email notifications (SMTP integration)
- [ ] PDF generation for payslips and reports
- [ ] Two-factor authentication (2FA)
- [ ] Advanced reporting and analytics
- [ ] Mobile app (iOS/Android)
- [ ] Integration with accounting software
- [ ] Biometric attendance integration
- [ ] Multi-language support
- [ ] Calendar integration
- [ ] Performance reviews module

---

## Credits

**SplashHR** - Built with ❤️ by the SplashHR Team

Special thanks to all contributors and the open-source community.

---

## Changelog

### Version 1.0.0 (2025-11-22)
- Initial release
- Multi-tenant architecture
- Core HR modules
- Payroll processing
- REST API
- Employee self-service portal
