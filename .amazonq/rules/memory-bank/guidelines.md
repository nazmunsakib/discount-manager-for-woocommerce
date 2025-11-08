# Discount Manager for WooCommerce - Development Guidelines

## Code Quality Standards

### WordPress Plugin Header Standards
- **Complete Plugin Header**: Always include all required WordPress plugin header fields
- **Comprehensive Metadata**: Include Plugin Name, URI, Description, Version, Author details
- **Compatibility Information**: Specify WordPress, WooCommerce, and PHP version requirements
- **License Information**: Include GPL v2 or later with full license URI
- **Internationalization**: Define Text Domain and Domain Path for translations

### Documentation Standards
- **PHPDoc Blocks**: Use complete PHPDoc blocks with @package, @author, and @since tags
- **Inline Comments**: Provide clear, descriptive comments for security checks and logic
- **Header Comments**: Use descriptive comments that explain the purpose of code sections

### Security Implementation Patterns
```php
// Prevent direct access pattern (REQUIRED for all PHP files)
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

## Code Formatting Conventions

### PHP Opening Tags
- Always use full PHP opening tags: `<?php`
- Never use short tags or alternative syntax

### Indentation and Spacing
- Use tabs for indentation (WordPress standard)
- Single space after control structures and before opening braces
- Consistent spacing around operators and assignments

### Comment Formatting
- Use `//` for single-line comments
- Capitalize first letter of comments
- End comments with periods for complete sentences

## Structural Conventions

### File Organization
- Main plugin file at root level with plugin name
- Security check immediately after opening PHP tag
- Plugin header as first code block after opening tag

### Naming Conventions
- **Plugin Files**: Use hyphenated lowercase names matching plugin directory
- **Text Domain**: Match plugin directory name exactly
- **Package Names**: Use consistent package naming (note: current @package may need updating)

### Version Management
- Use semantic versioning (MAJOR.MINOR.PATCH)
- Update version in plugin header for each release
- Maintain compatibility version information for WordPress and WooCommerce

## WordPress Integration Patterns

### Plugin Dependency Management
- Use "Requires Plugins" header for explicit plugin dependencies
- Specify minimum and tested versions for WordPress and WooCommerce
- Include PHP version requirements

### Internationalization Preparation
- Define Text Domain matching plugin directory
- Set Domain Path for language files location
- Prepare for future translation support

## Security Best Practices

### Direct Access Prevention
- **MANDATORY**: Include ABSPATH check in every PHP file
- Use `exit;` (not `die()`) for consistency
- Place security check immediately after PHP opening tag

### WordPress Security Standards
- Follow WordPress security guidelines
- Use WordPress constants and functions for security checks
- Implement proper sanitization and validation (for future development)

## Development Workflow Standards

### Plugin Initialization
- Keep main plugin file minimal and focused on initialization
- Use main plugin file as entry point for larger functionality
- Maintain clean separation between configuration and logic

### Extensibility Patterns
- Design for modular expansion
- Use WordPress hooks and filters for extensibility
- Maintain backward compatibility in version updates

## Quality Assurance

### Testing Requirements
- Test with specified WordPress versions (5.0 to 6.8)
- Verify WooCommerce compatibility (5.0 to 8.5)
- Ensure PHP 7.4+ compatibility

### Code Review Checklist
- [ ] Complete plugin header with all required fields
- [ ] ABSPATH security check present
- [ ] Proper PHPDoc documentation
- [ ] Consistent code formatting
- [ ] Version compatibility verified