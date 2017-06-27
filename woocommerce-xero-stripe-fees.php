<?php

/**
 * @link              https://itchef.nz
 * @since             1.1.0
 * @package           Woocommerce_Xero_Stripe_Fees
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Xero Stripe Fees
 * Plugin URI:        https://github.com/dfinnema/woocommerce-xero-stripe-fees
 * Description:       Extends the WooCommerce Xero Extension with Stripe Fees on Invoices
 * Version:           1.1
 * Author:            IT Chef
 * Author URI:        https://itchef.nz
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-xero-stripe-fees
 * Domain Path:       /languages
 * 
 * @woocommerce-extension
 * WC requires at least: 3.0
 * WC tested up to: 3.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * 
 * Some hooks used in this plugin that can be used
 * 
 *  FILTER wc_xero_stripe_fee_text              = changes the Xero Line description for the a Stripe Fee
 *  FILTER wc_xero_stripe_fee_not_found_text    = changes the ORDER NOTE Text when no stripe fee is found in the order
 *  FILTER wc_xero_stripe_fee_order_note        = changes the ORDER NOTE text that a stripe has been added to the order
 *  
 */  

// Plugin Updater
require 'plugin-update-checker/plugin-update-checker.php';
$dfc_puc = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/dfinnema/woocommerce-xero-stripe-fees',
	__FILE__,
	'woocommerce-xero-stripe-fees'
);
$dfc_puc->setBranch('release');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-xero-stripe-fees-activator.php
 */
function activate_woocommerce_xero_stripe_fees() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-xero-stripe-fees-activator.php';
	Woocommerce_Xero_Stripe_Fees_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-xero-stripe-fees-deactivator.php
 */
function deactivate_woocommerce_xero_stripe_fees() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-xero-stripe-fees-deactivator.php';
	Woocommerce_Xero_Stripe_Fees_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_xero_stripe_fees' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_xero_stripe_fees' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-xero-stripe-fees.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function run_woocommerce_xero_stripe_fees() {

	$plugin = new Woocommerce_Xero_Stripe_Fees();
	$plugin->run();

}
run_woocommerce_xero_stripe_fees();

/**
 * Gets the Plugin Path.
 *
 * Some functions require this file's full path.
 *
 * @since    1.1.0
 */
function getfile_woocommerce_xero_stripe_fees() {
     
     return __FILE__;
 }
