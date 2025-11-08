# Discount Manager for WooCommerce - Project Structure

## Directory Structure
```
discount-manager-woocommerce/
├── .amazonq/
│   └── rules/
│       └── memory-bank/          # AI assistant documentation
└── discount-manager-woocommerce.php  # Main plugin file
```

## Core Components

### Main Plugin File
- **discount-manager-woocommerce.php**: Primary plugin entry point containing plugin header information and initialization logic

### Configuration Structure
- **Plugin Header**: Contains all WordPress plugin metadata including version, compatibility, and dependencies
- **Security Layer**: Implements direct access prevention using ABSPATH constant check
- **Namespace**: Uses @package annotation for proper code organization

## Architectural Patterns

### WordPress Plugin Architecture
- Follows standard WordPress plugin structure with main plugin file at root
- Implements WordPress plugin header standards for proper plugin recognition
- Uses WordPress security best practices with ABSPATH checks

### Modular Design Approach
- Core package architecture suggests extensible design for additional modules
- Clean separation of concerns with dedicated entry point
- Prepared for future expansion with additional feature modules

## Component Relationships
- **Main Plugin File**: Serves as the central orchestrator for all plugin functionality
- **WooCommerce Dependency**: Explicit requirement for WooCommerce plugin activation
- **WordPress Integration**: Native WordPress plugin architecture for seamless integration

## Development Structure
- Minimal initial structure focused on core functionality
- Extensible architecture ready for additional components
- Standard WordPress plugin conventions for maintainability