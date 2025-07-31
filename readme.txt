=== Coupolic ===
Contributors: mainulsunvi
Tags: coupons, discount, bulk discount, woocommerce, woocommerce coupon
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Bulk coupon generator for WooCommerce. Create multiple discount coupons at once with customizable settings.

== Description ==

Coupolic is a powerful bulk coupon generator for WooCommerce that allows you to create multiple discount coupons simultaneously. Save time and effort by generating dozens of unique coupon codes with just a few clicks.

= Features =

* **Bulk Generation**: Create up to 100 coupons at once
* **Customizable Prefix**: Add custom prefixes to your coupon codes
* **Multiple Discount Types**: Support for fixed cart, percentage, and fixed product discounts
* **Usage Limits**: Set usage limits per coupon
* **Expiry Dates**: Configure expiration dates for your coupons
* **Minimum Spend**: Set minimum order amounts for coupon usage
* **Individual Use**: Option to restrict coupons from being used with other coupons
* **Export to CSV**: Export generated coupon codes for easy distribution
* **User-Friendly Interface**: Clean and intuitive admin interface

= Requirements =

* WordPress 6.0 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher

= Usage =

1. Navigate to WooCommerce → Bulk Coupons in your WordPress admin
2. Configure your coupon settings:
   - Set a prefix for your coupon codes
   - Choose the number of coupons to generate
   - Select discount type and amount
   - Configure usage limits and restrictions
3. Click "Generate Coupons"
4. View your generated coupons and export to CSV if needed

== Installation ==

1. Upload the `coupolic` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure WooCommerce is installed and activated
4. Navigate to WooCommerce → Bulk Coupons to start generating coupons

== Frequently Asked Questions ==

= How many coupons can I generate at once? =

You can generate up to 100 coupons in a single batch to prevent server timeouts and ensure smooth operation.

= Can I customize the coupon codes? =

Yes, you can add a custom prefix to all generated coupon codes. The plugin will append a unique random string to ensure each code is unique.

= Are the generated coupon codes unique? =

Yes, the plugin checks for existing coupon codes to ensure all generated codes are unique.

= Can I export the generated coupons? =

Yes, after generating coupons, you can export them to a CSV file for easy distribution or record-keeping.

= What happens if WooCommerce is not installed? =

The plugin will show a notice asking you to install and activate WooCommerce, as it's required for the plugin to function.

== Screenshots ==

1. Bulk coupon generator interface
2. Generated coupons list with export option
3. Individual coupon edit screen in WooCommerce

== Changelog ==

= 1.0.1 =
* Minor Bug Resolved

= 1.0.0 =
* Initial release
* Bulk coupon generation
* CSV export functionality
* Support for all WooCommerce discount types
* Customizable coupon settings

== Upgrade Notice ==

= 1.0.0 =
Initial release of Coupolic - Bulk Coupon Generator for WooCommerce.

== Privacy Policy ==

Coupolic does not collect or store any personal data. All coupon data is stored locally in your WordPress database.