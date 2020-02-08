<?php
/**
 * Plugin Name: WooCommerce Xero Stripe Fees
 * Plugin URI: https://github.com/dfinnema/woocommerce-xero-stripe-fees
 * Description: Extends the WooCommerce Xero Extension with Stripe Fees on Invoices
 * Version: 2.1.5
 * Author: IT Chef
 * Author URI: https://itchef.nz
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woocommerce-xero-stripe-fees
 * Domain Path: /languages
 *
 * @woocommerce-extension
 * WC requires at least: 3.6
 * WC tested up to: 3.9.1
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

define('XEROSTRIPEFEES_VERSION','2.1.5');
define('XEROSTRIPEFEES_FILE',__FILE__);

/**
 * The core plugin class
 */
require_once plugin_dir_path( XEROSTRIPEFEES_FILE ) . 'includes/class-xerostripefees.php';

/**
 * Gets the main Class Instance
 * @return XEROSTRIPEFEES\XEROSTRIPEFEES
 */
function xerostripefees() {

	// globals
	global $xerostripefees;

	// initialize
	if( !isset($xerostripefees) ) {
		$xerostripefees = new \XEROSTRIPEFEES\XEROSTRIPEFEES();
		$xerostripefees->init();
	}

	// return
	return $xerostripefees;
}
add_action( 'plugins_loaded', 'xerostripefees' );

/**
 * Updater
 */
require_once 'vendor/autoload.php';
$xerostripefees_updater = \Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/dfinnema/woocommerce-xero-stripe-fees',
	__FILE__,
	'woocommerce-xero-stripe-fees'
);
$xerostripefees_updater->getVcsApi()->enableReleaseAssets();