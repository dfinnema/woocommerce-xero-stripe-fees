<?php

/**
 * Fired during plugin activation
 *
 * @link       https://itchef.nz
 * @since      1.1.0
 *
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.1.0
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/includes
 * @author     IT Chef <hello@itchef.nz>
 */
class Woocommerce_Xero_Stripe_Fees_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.1.0
	 */
	public static function activate() {
        
        if (! in_array( 'woocommerce-xero/woocommerce-xero.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            
            // Nope not activate, lets deactive this plugin
            deactivate_plugins( getfile_woocommerce_xero_stripe_fees() );
            
            // Tell the user about it
            die('<p>'.__( '<a href="https://woocommerce.com/products/xero/">WooCommerce Xero Integration</a> is required to be installed and activated!', 'woocommerce-xero-stripe-currency' ).'</p>');
        }

	}

}
