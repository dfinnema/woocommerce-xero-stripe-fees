<?php


namespace XEROSTRIPEFEES;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * The Xero Options functionality of the plugin.
 *
 * @link       https://itchef.nz
 * @since      2.0
 *
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/Admin
 */
class Xero_Options {

	/**
	 * Admin constructor.
	 */
	public function __construct() {

	    error_log('Boot');

		add_action( 'admin_init', array( $this , 'settings_page' ), 11);
	}

	/**
	 * Display a notice when incompatibilities are found.
	 *
	 * Looks at enabled options for WooCommerce Xero and tells the user about issues
	 *
	 * @since    1.1.0
	 */
	public function compatibility() {

		// Make sure the user is admin (no point displaying it otherwise)
		if (is_admin()) {
			// Get Current WooCommerce Xero Options
			$xero_send_payments = get_option( 'wc_xero_send_payments' , '');

			if ('on' == $xero_send_payments) {
				// Tell the user that sending Payments to Xero will not work with this plugin active (TODO in the future)
				add_action('admin_notices', function() {
					echo('<div class="notice notice-warning"><p>['.$this->plugin_name.'] '.__('Sending payments to Xero does not work with this plugin enabled. Please turn off this option to avoid getting errors','woocommerce-xero-stripe-fees').'</p></div>');
				});
			}
		}

	}

	/**
	 * Register the settings and fields for options page.
	 *
	 * Uses the existing WooCommerce Xero Extension options page, makes it easy for the user to manage in one place
	 *
	 * @since    1.1.0
	 */

	public function settings_page() {

		// Stripe Fee Xero Account
		register_setting(
			'woocommerce_xero',                 // settings page
			'wc_xero_dfc_stripe_fee_account'          // option name
		);


		add_settings_field(
			'wc_xero_dfc_stripe_fee_account',      // id
			__('Stripe Fee Account', 'woocommerce-xero-stripe-fees'),              // setting title
			array( $this, 'fees_input' ),    // display callback
			'woocommerce_xero',                 // settings page
			'wc_xero_settings'                  // settings section
		);

		// Xero / Stripe Country
		register_setting(
			'woocommerce_xero',                 // settings page
			'wc_xero_dfc_stripe_fee_country'          // option name
		);


		add_settings_field(
			'wc_xero_dfc_stripe_fee_country',      // id
			__('Stripe Country', 'woocommerce-xero-stripe-fees'),              // setting title
			array( $this, 'country_input' ),    // display callback
			'woocommerce_xero',                 // settings page
			'wc_xero_settings'                  // settings section
		);

		// Disable Stripe Fees, in case the user wishes ot use another extension.
		register_setting(
			'woocommerce_xero',                 // settings page
			'wc_xero_dfc_stripe_fee_enabled'          // option name
		);

		add_settings_field(
			'wc_xero_dfc_stripe_fee_enabled',      // id
			__('Calculate Stripe fees', 'woocommerce-xero-stripe-fees'),              // setting title
			array( $this, 'enabled_input' ),    // display callback
			'woocommerce_xero',                 // settings page
			'wc_xero_settings'                  // settings section
		);

	}

	/**
	 * The STRIPE FEE ACCOUNT field outputed as HTML
	 *
	 * Outputs the Stripe Account Text Field
	 *
	 * @since    1.1.0
	 */
	public function fees_input() {

		$value = get_option( 'wc_xero_dfc_stripe_fee_account' , '');
		// echo the field
		?>
		<input id='wc_xero_dfc_stripe_fee_account' name='wc_xero_dfc_stripe_fee_account' type='text' value='<?php echo esc_attr( $value ); ?>' />
		<p class="description"><?php _e('Code for Xero account to track Stripe Fees paid.', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}

	/**
	 * The STRIPE FEE Enabled option
	 *
	 * Outputs the Stripe Enabled Checkbox Field
	 *
	 * @since    1.3.2
	 */
	public function enabled_input() {

		$option = get_option('wc_xero_dfc_stripe_fee_enabled');

		if ('on' == $option) {
			$option = 'checked';
		} else {
			$option = '';
		}

		?>
		<input type="checkbox" name="wc_xero_dfc_stripe_fee_enabled" id="wc_xero_dfc_stripe_fee_enabled" <?php echo($option); ?> />
		<p class="description"><?php _e('Add Stripe Fees to Xero Invoices?', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}

	/**
	 * The Xero Country field outputed as HTML
	 *
	 * Outputs the Stripe Country Dropdown
	 *
	 *     Australia       GST 10%
	 *     New Zealand     GST 15%
	 *     United States   NO TAX
	 *     Canada          NO TAX
	 *     EU              NO TAX
	 *     Ireland         VAT 23%
	 *
	 * @since    1.1.0
	 */
	public function country_input() {

		$value = get_option( 'wc_xero_dfc_stripe_fee_country' , '');


		// If no country is set, automatically set it to what WooCommerce has been set to (if supported below)
		if ('' == $value) {
			$value = get_option( 'woocommerce_default_country', '');
			if ('' == $value) {
				$value = substr($value, 0, 2);
				if (!in_array($value, array('AU','NZ','US','CA','IE'))) {
					$value = '';
				}
			}


		}

		// echo the field
		?>
		<select id="wc_xero_dfc_stripe_fee_country" name="wc_xero_dfc_stripe_fee_country">
			<option value=""<?php if ('' == $value) {echo(' selected'); } ?> disabled><?php _e('Select Stripe Country', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="AU"<?php if ('AU' == $value) { echo(' selected'); } ?>><?php _e('Australia', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="NZ"<?php if ('NZ' == $value) { echo(' selected'); } ?>><?php _e('New Zealand', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="US"<?php if ('US' == $value) { echo(' selected'); } ?>><?php _e('United States of America', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="CA"<?php if ('CA' == $value) { echo(' selected'); } ?>><?php _e('Canada', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="EU"<?php if ('EU' == $value) { echo(' selected'); } ?>><?php _e('European Union', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="IE"<?php if ('IE' == $value) { echo(' selected'); } ?>><?php _e('Ireland', 'woocommerce-xero-stripe-fees'); ?></option>
			<option value="XX"<?php if ('XX' == $value) { echo(' selected'); } ?>><?php _e('Other (NO TAX Calculations)', 'woocommerce-xero-stripe-fees'); ?></option>
		</select>
		<p class="description"><?php _e('The country you have registered with Stripe.', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}

}

new Xero_Options();