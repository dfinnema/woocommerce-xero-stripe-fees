<?php
namespace XEROSTRIPEFEES;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class Options
 * @package XEROSTRIPEFEES
 */
class Options {

	/**
	 * Admin constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this , 'settings_page' ), 11);
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
			'woocommerce_xero',
			'wc_xero_dfc_stripe_fee_account'
		);

		add_settings_field(
			'wc_xero_dfc_stripe_fee_account',
			/* translators: Stripe Account Code Field Label in the Xero settings  */
			__('Stripe Fee Account', 'woocommerce-xero-stripe-fees'),
			array( $this, 'fees_input' ),
			'woocommerce_xero',
			'wc_xero_settings'
		);

		// Xero / Stripe Country
		register_setting(
			'woocommerce_xero',
			'wc_xero_dfc_stripe_fee_country'
		);

		add_settings_field(
			'wc_xero_dfc_stripe_fee_country',
			/* translators: Stripe Country selection in the Xero settings  */
			__('Stripe Country', 'woocommerce-xero-stripe-fees'),
			array( $this, 'country_input' ),
			'woocommerce_xero',
			'wc_xero_settings'
		);

		// Disable Stripe Fees, in case the user wishes ot use another extension.
		register_setting(
			'woocommerce_xero',
			'wc_xero_dfc_stripe_fee_enabled'
		);

		add_settings_field(
			'wc_xero_dfc_stripe_fee_enabled',
			/* translators: Calculate Stripe Fees Xero settings  */
			__('Calculate Stripe fees', 'woocommerce-xero-stripe-fees'),
			array( $this, 'enabled_input' ),
			'woocommerce_xero',
			'wc_xero_settings'
		);

		// Allow each product to have their own Xero Account assigned
		register_setting(
			'woocommerce_xero',
			'wc_xero_dfc_stripe_fee_accounts_enabled'
		);

		add_settings_field(
			'wc_xero_dfc_stripe_fee_accounts_enabled',
			/* translators: Enable Xero Account codes for each product in Xero settings  */
			__('Xero Account Codes per Product', 'woocommerce-xero-stripe-fees'),
			array( $this, 'enabled_accounts_input' ),
			'woocommerce_xero',
			'wc_xero_settings'
		);

	}

	/**
	 * The STRIPE FEE ACCOUNT field outputted as HTML
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
        <?php /* translators: Calculate Stripe Fees Xero settings description  */ ?>
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
		<?php /* translators: Enable Stripe Fees Xero settings description  */ ?>
		<p class="description"><?php _e('Add Stripe Fees to Xero Invoices?', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}

	/**
	 * The STRIPE FEE ACCOUNTS Enabled option
	 *
	 * Outputs the Stripe Accounts Enabled Checkbox Field
	 *
	 * @since    2.1
	 */
	public function enabled_accounts_input() {

		$option = get_option('wc_xero_dfc_stripe_fee_accounts_enabled');

		if ('on' == $option) {
			$option = 'checked';
		} else {
			$option = '';
		}

		?>
        <input type="checkbox" name="wc_xero_dfc_stripe_fee_accounts_enabled" id="wc_xero_dfc_stripe_fee_accounts_enabled" <?php echo($option); ?> />
		<?php /* translators: Description Enable Xero Account Codes per Product in Xero Settings  */ ?>
        <p class="description"><?php _e('Enable Xero Account Codes per Product?', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}

	/**
	 * The Xero Country field outputed as HTML
	 *
	 * Outputs the Stripe Country Dropdown
	 *
	 *     Australia       GST 10% - @url - https://support.stripe.com/questions/goods-and-services-tax-for-australia-based-businesses
	 *     New Zealand     NO TAX included in Stripe Fees
	 *     United States   NO TAX included in Stripe Fees
	 *     Canada          NO TAX included in Stripe Fees
	 *     EU              NO TAX included in Stripe Fees
	 *     Ireland         VAT 23% - @url - https://support.stripe.com/questions/value-added-tax-vat-for-ireland-based-businesses
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
			<?php /* translators: Dropdown for selecting Stripe Country  */ ?>
			<option value=""<?php if ('' == $value) {echo(' selected'); } ?> disabled><?php _e('Select Stripe Country', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: Australia in the dropdown for selecting Stripe Country  */ ?>
			<option value="AU"<?php if ('AU' == $value) { echo(' selected'); } ?>><?php _e('Australia ( Stripe Fee includes GST )', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: New Zealand in the dropdown for selecting Stripe Country  */ ?>
            <option value="NZ"<?php if ('NZ' == $value) { echo(' selected'); } ?>><?php _e('New Zealand', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: United States in the dropdown for selecting Stripe Country  */ ?>
            <option value="US"<?php if ('US' == $value) { echo(' selected'); } ?>><?php _e('United States of America', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: Canada in the dropdown for selecting Stripe Country  */ ?>
            <option value="CA"<?php if ('CA' == $value) { echo(' selected'); } ?>><?php _e('Canada', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: European Union in the dropdown for selecting Stripe Country  */ ?>
            <option value="EU"<?php if ('EU' == $value) { echo(' selected'); } ?>><?php _e('European Union', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: Ireland in the dropdown for selecting Stripe Country  */ ?>
            <option value="IE"<?php if ('IE' == $value) { echo(' selected'); } ?>><?php _e('Ireland ( Stripe Fee includes VAT )', 'woocommerce-xero-stripe-fees'); ?></option>
			<?php /* translators: Other in the dropdown for selecting Stripe Country  */ ?>
            <option value="XX"<?php if ('XX' == $value) { echo(' selected'); } ?>><?php _e('Other', 'woocommerce-xero-stripe-fees'); ?></option>
		</select>
		<?php /* translators: Description of the Stripe Country Dropdown Selection  */ ?>
		<p class="description"><?php _e('The country you have registered with Stripe. For customers in Ireland / Australia the tax will be removed from your stripe fee as Xero will add it back on.', 'woocommerce-xero-stripe-fees'); ?></p>
		<?php
	}
}

new Options();