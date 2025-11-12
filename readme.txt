=== Discount Manager for WooCommerce ===
Contributors: nazmunsakib
Tags: woocommerce, discount, pricing, bulk discount, cart discount
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create flexible discount rules for your WooCommerce store with support for percentage, fixed, and bulk pricing strategies.

== Description ==

Discount Manager for WooCommerce allows you to create and manage discount rules for your online store. The plugin provides a straightforward interface for setting up various discount types and applying them to specific products or categories.

= Features =

* Multiple discount types: percentage, fixed amount, and bulk pricing
* Product-based filtering with include/exclude options
* Category-based filtering with include/exclude options
* Configurable discount calculation from regular or sale prices
* Priority-based rule ordering
* Active/inactive rule status management
* Usage tracking for discount rules

= Rule Types =

* **Product Adjustment**: Apply discounts directly to individual product prices
* **Cart Adjustment**: Apply discounts to the entire cart total
* **Bulk Discount**: Create quantity-based pricing tiers with different discount levels

= Discount Types =

Within each rule type, you can choose how the discount is calculated:

* **Percentage**: Reduce price by a percentage (e.g., 20% off)
* **Fixed Amount**: Subtract a fixed amount from the price
* **Fixed Price**: Set a specific price for bulk discount tiers

= Product Selection =

* Include or exclude specific products
* Include or exclude entire categories
* Combine multiple filters for precise targeting

= Settings =

* Choose whether discounts calculate from regular prices or sale prices
* Configure global discount behavior
* Reset settings to defaults when needed

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/discount-manager-woocommerce` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure WooCommerce is installed and activated.
4. Navigate to WooCommerce > Discount Manager to configure your discount rules.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and activated for this plugin to function.

= Can I create multiple discount rules? =

Yes, you can create unlimited discount rules and set priorities to control which rule applies when multiple rules match.

= What is the difference between Rule Type and Discount Type? =

Rule Type determines where the discount applies:
- Product Adjustment: Modifies individual product prices
- Cart Adjustment: Applies to the entire cart subtotal
- Bulk Discount: Creates tiered pricing based on quantity

Discount Type determines how the discount is calculated:
- Percentage: Calculates discount as a percentage of the price
- Fixed Amount: Subtracts a specific amount from the price
- Fixed Price: Sets an exact price (available for bulk discounts)

= How do bulk discounts work? =

Bulk discounts allow you to set different prices based on quantity ranges. For example, 1-5 items at one price, 6-10 items at a lower price, and so on. You can choose between individual product quantity or cumulative quantity across all matching products.

= Can I exclude specific products from discounts? =

Yes, you can use the exclude option in product filters to prevent discounts from applying to specific products or categories.

= What happens if multiple rules apply to the same product? =

The plugin uses rule priority to determine which discount applies. Rules with lower priority numbers are evaluated first.

== Screenshots ==

1. Discount rules management interface
2. Create new discount rule with product selection
3. Bulk discount configuration
4. Plugin settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Percentage discount support
* Fixed discount support
* Bulk discount support
* Product and category filtering
* Include/exclude logic
* Priority-based rule ordering
* Settings management
* Usage tracking

== Upgrade Notice ==

= 1.0.0 =
Initial release of Discount Manager for WooCommerce.

== Additional Information ==

For support, feature requests, or bug reports, please visit the plugin's GitHub repository at https://github.com/nazmunsakib/discount-manager-woocommerce
