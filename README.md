# Discount Manager for WooCommerce

A powerful WordPress plugin that provides flexible discount rules for WooCommerce stores, from simple percentage discounts to complex bulk pricing strategies.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 🎯 Overview

Discount Manager for WooCommerce enables store owners to create sophisticated discount systems without custom development. Whether you need simple percentage discounts, quantity-based bulk pricing, or complex conditional rules, this plugin provides a comprehensive solution.

## ✨ Key Features

### 🎨 Discount Types
- **Percentage Discount** - Apply percentage-based discounts (e.g., 20% off)
- **Fixed Amount Discount** - Subtract a fixed amount from prices
- **Bulk/Tiered Pricing** - Create quantity-based pricing tiers
- **Cart-Level Discounts** - Apply discounts to entire cart subtotal
- **Product-Level Discounts** - Modify individual product prices

### 🎯 Advanced Targeting
- **Product Filtering** - Include or exclude specific products
- **Category Filtering** - Target entire product categories
- **Flexible Logic** - Combine multiple filters with include/exclude options
- **All Products** - Apply discounts store-wide

### 📊 Bulk Discount Features
- **Quantity Ranges** - Define min/max quantity tiers
- **Multiple Pricing Tiers** - Unlimited pricing levels
- **Bulk Operators**:
  - Product Individual: Count each product separately
  - Product Cumulative: Count all matching products together
- **Bulk Pricing Table** - Automatic display on product pages
- **Custom Labels** - Add descriptive labels to quantity ranges

### ⚙️ Rule Configuration
- **Priority System** - Control which rules apply first
- **Date Scheduling** - Set start and end dates for rules
- **Usage Limits** - Restrict total rule applications
- **Usage Tracking** - Monitor how many times rules are used
- **Active/Inactive Status** - Enable or disable rules without deletion
- **Exclusive Rules** - Prevent combining with other discounts

### 🛒 Cart & Checkout
- **Real-time Calculation** - Discounts apply automatically in cart
- **Cart Adjustments** - Display as line items or fees
- **Custom Cart Labels** - Personalize discount descriptions
- **Strikethrough Pricing** - Show original vs. discounted prices
- **Sale Badges** - Automatic "Sale!" badge display

### 🎛️ Global Settings
- **Calculate From**: Choose between regular price or sale price as base
- **Apply Method**: 
  - First matching rule
  - Biggest discount
  - Lowest discount
  - All applicable rules (stacking)
- **Coupon Behavior**:
  - Run both coupons and discount rules
  - Disable rules when coupons applied
  - Disable coupons when rules applied
- **Display Options**:
  - Show/hide strikethrough pricing
  - Show/hide bulk pricing tables
  - Configure sale badge behavior

### 🎨 Product Display
- **Price Modifications** - Automatic price updates on product pages
- **Sale Badge Control** - Conditional badge display
- **Discount Bar** - Optional promotional message
- **Bulk Pricing Table** - Visual quantity discount display

### 🔧 Technical Features
- **REST API** - Programmatic access to discount rules
- **Database Tables** - Efficient custom table structure
- **Caching** - Performance-optimized with WordPress caching
- **WooCommerce Integration** - Native hooks and filters
- **Internationalization Ready** - Translation support included

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **WooCommerce**: 5.0 or higher (required)
- **PHP**: 7.4 or higher
- **Tested up to**: WordPress 6.8, WooCommerce 8.5

## 🚀 Installation

### Via WordPress Admin

1. Download the plugin ZIP file
2. Navigate to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**
5. Ensure WooCommerce is installed and activated

### Manual Installation

1. Upload the `discount-manager-for-woocommerce` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **WooCommerce > Discount Manager** to configure

### Via Composer

```bash
composer require nazmunsakib/discount-manager-for-woocommerce
```

## 📖 Usage Guide

### Creating Your First Discount Rule

1. Go to **WooCommerce > Discount Manager**
2. Click **Add New Rule**
3. Configure the rule:
   - **Title**: Give your rule a descriptive name
   - **Discount Type**: Choose percentage, fixed, or bulk
   - **Discount Value**: Enter the discount amount
   - **Filters**: Select which products to target
   - **Priority**: Set rule order (lower numbers = higher priority)
4. Click **Save Rule**

### Use Case Examples

#### Example 1: Store-Wide 20% Off Sale
```
Title: Summer Sale
Discount Type: Percentage
Discount Value: 20
Apply To: All Products
Status: Active
```

#### Example 2: Category-Specific Discount
```
Title: Electronics 15% Off
Discount Type: Percentage
Discount Value: 15
Apply To: Specific Categories
Selected Categories: Electronics
Status: Active
```

#### Example 3: Bulk Pricing (Buy More, Save More)
```
Title: Bulk T-Shirt Discount
Discount Type: Bulk
Apply To: Specific Products
Bulk Ranges:
  - 1-5 items: 0% off
  - 6-10 items: 10% off
  - 11-20 items: 15% off
  - 21+ items: 20% off
Bulk Operator: Product Cumulative
Status: Active
```

#### Example 4: Cart Discount (Spend $100, Save $10)
```
Title: Spend & Save
Discount Type: Fixed
Discount Value: 10
Apply As: Cart Rule
Minimum Subtotal: 100
Cart Label: "You saved $10!"
Status: Active
```

#### Example 5: Seasonal Promotion
```
Title: Holiday Special
Discount Type: Percentage
Discount Value: 25
Apply To: All Products
Date From: 2024-12-01
Date To: 2024-12-31
Status: Active
```

#### Example 6: Limited Time Flash Sale
```
Title: Flash Sale - First 100 Orders
Discount Type: Percentage
Discount Value: 30
Apply To: All Products
Usage Limit: 100
Status: Active
```

## 🎯 Common Use Cases

### For E-commerce Stores
- **Clearance Sales** - Discount specific products or categories
- **Seasonal Promotions** - Time-limited discounts
- **New Customer Offers** - First-time purchase incentives
- **Loyalty Rewards** - Repeat customer discounts

### For Wholesale Businesses
- **Tiered Pricing** - Volume-based discounts
- **Bulk Orders** - Quantity-based pricing
- **Trade Discounts** - Category-specific wholesale pricing

### For Marketing Campaigns
- **Flash Sales** - Limited-time, high-discount promotions
- **Bundle Deals** - Multi-product discounts
- **Cart Incentives** - Minimum purchase rewards
- **Abandoned Cart Recovery** - Special discount codes

## ⚙️ Configuration

### Global Settings

Access settings via **WooCommerce > Discount Manager > Settings**

#### Calculate From
- **Regular Price**: Base discounts on original product prices
- **Sale Price**: Apply discounts to already-reduced sale prices

#### Apply Product Discount To
- **First**: Apply only the first matching rule
- **Biggest Discount**: Apply the rule with the largest discount
- **Lowest Discount**: Apply the rule with the smallest discount
- **All**: Stack all applicable discounts (use with caution)

#### Coupon Behavior
- **Run Both**: Allow coupons and discount rules simultaneously
- **Disable Rules**: Turn off discount rules when coupons are applied
- **Disable Coupons**: Prevent coupon usage when discount rules apply

#### Display Options
- **Show Strikeout**: Display original price with strikethrough
- **Show Bulk Table**: Display bulk pricing table on product pages
- **Sale Badge**: Control when "Sale!" badges appear

## 🔌 Developer Documentation

### Hooks & Filters

#### Filters

```php
// Modify discount calculation
add_filter('dmwoo_calculate_discount', function($discount, $rule, $product) {
    // Custom logic
    return $discount;
}, 10, 3);

// Modify product discount price
add_filter('dmwoo_product_discount_price', function($price, $product_id) {
    // Custom logic
    return $price;
}, 10, 2);
```

#### Actions

```php
// After rule is applied
add_action('dmwoo_rule_applied', function($rule_id, $product_id) {
    // Custom logic
}, 10, 2);

// After discount calculation
add_action('dmwoo_discount_calculated', function($discount_amount, $cart) {
    // Custom logic
}, 10, 2);
```

### REST API

Access discount rules programmatically:

```
GET /wp-json/dmwoo/v1/rules
GET /wp-json/dmwoo/v1/rules/{id}
POST /wp-json/dmwoo/v1/rules
PUT /wp-json/dmwoo/v1/rules/{id}
DELETE /wp-json/dmwoo/v1/rules/{id}
```

### Database Schema

The plugin creates two custom tables:

- `wp_dmwoo_rules` - Stores discount rules
- `wp_dmwoo_settings` - Stores plugin settings

## 🐛 Troubleshooting

### Discounts Not Applying

1. Check rule status is **Active**
2. Verify product matches rule filters
3. Check date range if configured
4. Verify usage limit not reached
5. Check rule priority order

### Conflicts with Other Plugins

1. Check **Coupon Behavior** setting
2. Disable third-party discount plugins temporarily
3. Clear WooCommerce cache
4. Check for theme conflicts

### Performance Issues

1. Limit number of active rules
2. Use specific product/category filters instead of "All Products"
3. Enable WordPress object caching
4. Optimize database tables

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Clone repository
git clone https://github.com/nazmunsakib/discount-manager-for-woocommerce.git

# Install dependencies
composer install

# Run tests
composer test
```

## 📝 Changelog

### Version 1.0.0
- Initial release
- Percentage discount support
- Fixed discount support
- Bulk discount support
- Product and category filtering
- Include/exclude logic
- Priority-based rule ordering
- Cart adjustment rules
- Settings management
- Usage tracking
- REST API endpoints
- Bulk pricing table display
- Sale badge control
- Strikethrough pricing

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Discount Manager for WooCommerce
Copyright (C) 2024 Nazmun Sakib

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👤 Author

**Nazmun Sakib**
- Website: [nazmunsakib.com](https://nazmunsakib.com)
- GitHub: [@nazmunsakib](https://github.com/nazmunsakib)

## 🙏 Support

- **Documentation**: [GitHub Wiki](https://github.com/nazmunsakib/discount-manager-for-woocommerce/wiki)
- **Issues**: [GitHub Issues](https://github.com/nazmunsakib/discount-manager-for-woocommerce/issues)
- **Discussions**: [GitHub Discussions](https://github.com/nazmunsakib/discount-manager-for-woocommerce/discussions)

## ⭐ Show Your Support

If you find this plugin helpful, please consider:
- Giving it a ⭐ on GitHub
- Sharing it with others
- Contributing to development
- Reporting bugs and suggesting features

---

**Made with ❤️ for the WooCommerce community**
