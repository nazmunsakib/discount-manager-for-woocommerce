# Discount Manager for WooCommerce - Technology Stack

## Programming Languages
- **PHP**: Primary development language (minimum version 7.4)
- **WordPress/WooCommerce APIs**: Core platform integration

## Platform Requirements

### WordPress Environment
- **WordPress Version**: 5.0 minimum, tested up to 6.8
- **PHP Version**: 7.4 minimum requirement
- **WooCommerce**: Required dependency (5.0 minimum, tested up to 8.5)

### Plugin Dependencies
- **WooCommerce Plugin**: Explicit requirement defined in plugin header
- **WordPress Core**: Standard WordPress plugin architecture

## Development Standards

### WordPress Plugin Standards
- **Plugin Header**: Complete WordPress plugin header with all required fields
- **Text Domain**: `discount-manager-woocommerce` for internationalization
- **Domain Path**: `/languages` for translation files
- **License**: GPL v2 or later (WordPress compatible)

### Code Organization
- **Package Annotation**: `@package NivoSearch` (legacy reference)
- **Author Documentation**: Proper attribution and contact information
- **Version Control**: Semantic versioning (1.0.0)

## Security Implementation
- **Direct Access Prevention**: ABSPATH constant check
- **WordPress Security Standards**: Following WordPress security best practices

## Development Commands
- Standard WordPress plugin development workflow
- No custom build system required
- Direct PHP development with WordPress/WooCommerce APIs

## Repository Information
- **GitHub Repository**: https://github.com/nazmunsakib/discount-manager-woocommerce
- **Author Website**: https://nazmunsakib.com
- **License**: GPL v2 or later (https://www.gnu.org/licenses/gpl-2.0.html)

## Compatibility Matrix
| Component | Minimum Version | Tested Version |
|-----------|----------------|----------------|
| WordPress | 5.0 | 6.8 |
| WooCommerce | 5.0 | 8.5 |
| PHP | 7.4 | - |