# Translation Files

This directory contains translation files for the Discount Manager for WooCommerce plugin.

## File Structure

- `discount-manager-woocommerce.pot` - Template file for translations
- `discount-manager-woocommerce-{locale}.po` - Translation files for specific languages
- `discount-manager-woocommerce-{locale}.mo` - Compiled translation files

## How to Translate

1. Copy `discount-manager-woocommerce.pot` to `discount-manager-woocommerce-{locale}.po`
   - Example: `discount-manager-woocommerce-es_ES.po` for Spanish (Spain)
   - Example: `discount-manager-woocommerce-fr_FR.po` for French (France)
   - Example: `discount-manager-woocommerce-de_DE.po` for German (Germany)

2. Use a translation tool like Poedit (https://poedit.net/) to translate the strings

3. Save the file - Poedit will automatically generate the `.mo` file

4. Upload both `.po` and `.mo` files to this directory

## Common Locale Codes

- `ar` - Arabic
- `de_DE` - German (Germany)
- `es_ES` - Spanish (Spain)
- `fr_FR` - French (France)
- `it_IT` - Italian (Italy)
- `ja` - Japanese
- `nl_NL` - Dutch (Netherlands)
- `pt_BR` - Portuguese (Brazil)
- `ru_RU` - Russian (Russia)
- `zh_CN` - Chinese (China)

## Contributing Translations

If you would like to contribute a translation, please submit a pull request to:
https://github.com/nazmunsakib/discount-manager-woocommerce

## WordPress.org Translations

Once the plugin is published on WordPress.org, translations can also be contributed through:
https://translate.wordpress.org/projects/wp-plugins/discount-manager-woocommerce
