<?php


namespace XEROSTRIPEFEES;

/**
 * Class Accounts
 * @package XEROSTRIPEFEES
 */
class Accounts {

	/**
	 * Accounts constructor.
	 */
	public function __construct() {

		// Is this Module Enabled?
		$enabled = get_option( 'wc_xero_dfc_stripe_fee_accounts_enabled' , false );
		if ( 'on' == $enabled ) {

			// Display WooCommerce Product Custom Field
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_custom_product_field_add' ), 10 );

			// Saves the custom fields
			add_action( 'woocommerce_process_product_meta', array( $this, 'woo_custom_product_field_save' ), 10, 1 );

			// Update the account code
			//add_filter( 'woocommerce_xero_stripe_fees_added_array', array( $this, 'update_product_account_codes' ), 25, 2 );
			add_filter( 'woocommerce_xero_stripe_fees_array', array( $this, 'update_product_account_codes' ), 25, 2 );
		}
	}

	/**
	 * Updates the account codes if one is set
	 * @param $data
	 * @param $order_id
	 *
	 * @return mixed
	 */
	public function update_product_account_codes( $data , $order_id ) {

		// Invoice Data
		if (array_key_exists(XERO::DATA_TYPE_INVOICE,$data)) {
			$invoice = $data[ XERO::DATA_TYPE_INVOICE ];

			// Make sure we have line items
			if (array_key_exists(XERO::LINEITEMS,$invoice) && !empty($invoice[XERO::LINEITEMS])) {
				if ( isset( $invoice[ XERO::LINEITEMS ][ XERO::LINEITEM ] ) ) {

					$line_items = $invoice[XERO::LINEITEMS][XERO::LINEITEM];

					// Load Order Items
					$order = wc_get_order( $order_id );
					if ($order) {

						$this->log(' + Found Order');

						$order_items = $order->get_items();
						$items = array();
						if ($order_items) {
							foreach ($order_items as $order_item) {
								$items[ $order_item['name'] ] = $order_item->get_product_id();
							}
						}

						$this->log(' + Items'.print_r($items,1));

						// Go through each line item and update the account code if one is found
						foreach ($line_items as &$line_item) {
							$description = $line_item[XERO::DESCRIPTION];

							$this->log(' + Looking to Match "'.$description.'"');

							if (array_key_exists($description,$items)) {
								$this->log('   MATCHED');
								// Get Account Code (if it has one)
								$account_code_tmp = get_post_meta( $items[$description] , '_xero_account_code',true);
								if ($account_code_tmp && !empty($account_code_tmp)) {
									// Update Account Code
									$this->log(' + Updated Account Code');
									$line_item[XERO::ACCOUNTCODE] = $account_code_tmp;
								}
							}
						}

						// Add Line Items back to invoice
						$invoice[XERO::LINEITEMS][XERO::LINEITEM] = $line_items;

						// Merge Data back into Xero Data Packet
						$data[XERO::DATA_TYPE_INVOICE] = $invoice;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Adds a custom Field to each product for setting the Xero Account code
	 * @since 1.0.0
	 */
	public function woo_custom_product_field_add() {

		echo '<div class="product_custom_field">';
		woocommerce_wp_text_input(
			array(
				'id'          => '_xero_account_code',
				/* translators: Account Code Field Label in the product settings  */
				'label'       => __( 'Xero Account Code', 'woocommerce-xero-stripe-fees' ),
				/* translators: Input Field for Account Code in the product settings  */
				'description' => __('Sets the account code in Xero when sending the invoice, leave blank to use the default', 'woocommerce-xero-stripe-fees' ),
				'desc_tip'    => 'true'
			)
		);
		echo '</div>';
	}

	/**
	 * Saves the custom field
	 * @param $post_id
	 * @since 1.0.0
	 */
	public function woo_custom_product_field_save( $post_id ) {
		// Custom Product Text Field
		$woocommerce_custom_product_xero_account = $_POST['_xero_account_code'];
		if (!empty($woocommerce_custom_product_xero_account))
			update_post_meta($post_id, '_xero_account_code', esc_attr($woocommerce_custom_product_xero_account));

	}

	/**
	 * Log
	 * @param string $message
	 */
	private function log( $message = '' ) {
		xerostripefees()->log( $message );
	}
}
new Accounts();