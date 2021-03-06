=== Plugin Name ===
Donate link: https://itchef.nz
Tags: woocommerce,xero,stripe,fees
Requires at least: 4.8
Tested up to: 5.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a Plugin for Wordpress using Woocommerce Xero Extension and Stripe Gateway. Adds a stripe fee to the invoices send to Xero

== Description ==

This is a Plugin for Wordpress using **Woocommerce** with the **Woocommerce Xero** Extension and **Stripe**

**Please note this is a work in progress and probably has a lot of bugs in it. The code is quick and dirty so feel free to add / change to it.**

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `woocommerce-xero-stripe-fees.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Change settings under WooCommerce > Xero

== Frequently Asked Questions ==

= Can this be used with sending payments to Xero =

Yes

= I have a question or issue =

Please head over to https://github.com/dfinnema/woocommerce-xero-stripe-fees

== Changelog ==

= 2.1.5 =
* UPDATED Plugin updater dependency, now uses Github Releases
* FIXED Minor formatting and spelling in code

= 2.1.4 =
* FIXED Single item in order without Stripe fee was causing mismatch with Xero Account Codes

= 2.1.3 =
* ADDED Default Xero Contact can now be set for any invoices being sent to Xero
* FIXED Calculate Stripe fees option

= 2.1.2 =
* FIXED Xero Account codes now also work with product variations

= 2.1.1 =
* UPDATED auto-updater upgraded to 4.6
* FIXED Stripe Fee tax calculations based on Country

= 2.1 =
* ADDED each product can now have a different xero account code

= 2.0.1 =
* Entire Codebase re-written for faster and better performance
* Ensure you test before updating if using any hooks associated with this plugin as these have changed

= 1.3.4 =
* FIXED bug with stripe fee option not being displayed

= 1.3.3 =
* Added support for Stores not using tax calculations
* Translations Updated

= 1.3.2 =
* Added Option to not add Stripe Fees
* Tweak Skip some functions with certain extensions

= 1.3.1 = 
* Translations Updated

= 1.3 =
* Upped the WC version to 3.4.2
* Added Filter wc_xero_stripe_fee_data_final

= 1.2 = 
* Stripe Meta Names Updated
* Now Requires Stripe Gateway 4.1.3 or higher

= 1.1 =
* Rewrote the plugin
* Options build into the WooCommerce Xero options page
* Multiple Stripe Countries Supported with Tax Calculations

= 1.0 =
* Initial Release