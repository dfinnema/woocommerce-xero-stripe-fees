<?php

namespace XEROSTRIPEFEES;

/**
 * Class Fees
 * @package XEROSTRIPEFEES
 */
class Fees {

	/**
	 * Fees constructor.
	 */
	public function __construct() {

		// Convert Data, priority 20 in case another plugin needs to access the data
		add_filter('woocommerce_xero_stripe_fees_array', array( $this , 'add_stripe_fee' ), 20, 2 );

		// Add the Woocommerce Xero Hooks
		add_filter('woocommerce_xero_payment_amount', array( $this , 'payment_remove_stripe_fee'), 10, 2 );
	}

	/**
	 * Adds the Stripe fee to an invoice
	 *
	 * @param $data
	 * @param $order_id
	 *
	 * @return mixed
	 */
	public function add_stripe_fee( $data, $order_id ) {

		// Invoice Data
		if (array_key_exists(XERO::DATA_TYPE_INVOICE,$data)) {
			$invoice = $data[XERO::DATA_TYPE_INVOICE];

			// Ensure we are using Stripe as a Payment Method
			if (WOO::has_stripe_payment_method( $order_id )) {

				// Get Stripe Fee Amount
				$stripe_fee_orginal = STRIPE::get_fee_amount( $order_id );
				if (false !== $stripe_fee_orginal) {

					// Are taxes enabled in WooCommerce?
					$woo_taxes_enabled = \wc_tax_enabled();

					// Remove any taxes from the Stripe Fee (if needed)
					$stripe_fee_net = STRIPE::get_fee_after_tax_calculations( $stripe_fee_orginal );

					// Check Fee Net Difference (if any)
					if ($stripe_fee_orginal !== $stripe_fee_net) {
						$stripe_fee = $stripe_fee_net;
					} else {
						$stripe_fee = $stripe_fee_orginal;
					}

					// Make the charge negative
					$stripe_fee_negative = 0 - $stripe_fee;

					// Get the Stripe Fee Account
					$stripe_fee_account_code = get_option( 'wc_xero_dfc_stripe_fee_account' , false);
					if (false !== $stripe_fee_account_code || 0 !== $stripe_fee_account_code) {

						// Make sure we have line items
						if (array_key_exists(XERO::LINEITEMS,$invoice) && !empty($invoice[XERO::LINEITEMS])) {
							if (array_key_exists(XERO::LINEITEM,$invoice[XERO::LINEITEMS])) {
								$this->log(' + Line Item Found');

								// Create Line Item for Stripe Fee
								$line_item_stripe_fee = array(
									XERO::DESCRIPTION    => STRIPE::get_fee_description(),
									XERO::ACCOUNTCODE    => $stripe_fee_account_code,
									XERO::UNITAMOUNT     => $stripe_fee_negative,
									XERO::QUANTITY       => 1,
									XERO::TAXTYPE        => 'NONE',
									XERO::TAXAMOUNT      => 0,
								);

								$line_items = $invoice[XERO::LINEITEMS][XERO::LINEITEM];
								$x = count($line_items);
								// Check if we have just one item, otherwise the array goes funny and will not work for Xero
								if (array_key_exists(XERO::DESCRIPTION, $line_items ) ) {

									//Only have 1 item here
									$this->log(' + Single Item');

									// Single Item, ensure the count var is set to 1 to effect the stripe fee line only
									$x = 1;

									// Convert Array Structure for multiple items
									$line_items_tmp = array();
									$line_items_tmp[0] = $line_items;
									$line_items_tmp[1] = $line_item_stripe_fee;
									$line_items = $line_items_tmp;

								} else {

									// Add Line Item to Array for the Stripe Fee
									$line_items[$x] = $line_item_stripe_fee;

								}

								// Remove Tax Type and set tax amount when  Calculating Taxes
								if (true == $woo_taxes_enabled && true == STRIPE::is_tax_charged_on_stripe_fee() ) {
									$this->log(' + Taxes Enabled, Stripe Fee includes tax');

									// Floatval just in case
									$stripe_fee_tax_amount = floatval($stripe_fee_orginal) -  floatval($stripe_fee_net);

									// Remove the Type as it gets added by Xero
									unset($line_items[$x][XERO::TAXTYPE]);

									// Add the Tax Amount to the item (ensure it is a negative)
									$line_items[$x][XERO::TAXAMOUNT] = 0 - $stripe_fee_tax_amount;

									// Add Tax to total
									$invoice[XERO::TOTAL_TAX] = $invoice[XERO::TOTAL_TAX] + $stripe_fee_tax_amount;

								}
								// Add Line Items back to invoice
								$invoice[XERO::LINEITEMS][XERO::LINEITEM] = $line_items;

								// Adjust Invoice Total
								$invoice[XERO::TOTAL] = $invoice[XERO::TOTAL] - $stripe_fee;

								// Merge Data back into Xero Data Packet
								$data[XERO::DATA_TYPE_INVOICE] = $invoice;

								// Filter for other plugins to make changes to our additions
								$data = apply_filters('woocommerce_xero_stripe_fees_added_array', $data, $order_id );

								/* translators: Order note added when the Stripe Fee has been added */
								//WOO::add_order_note( $order_id , sprintf(__('Added Stripe Fee ( $%d ) to Invoice','woocommerce-xero-stripe-fees'), round(floatval($stripe_fee_orginal),2) ) );
								// Debug Log
								$this->log(' + Added Stripe Fee to Invoice');
							}
						}
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Remove Stripe Fee from Payment
	 * @param $amount
	 * @param $obj
	 *
	 * @return float
	 */
	public function payment_remove_stripe_fee( $amount , $obj ) {

		// Load Order ID
		$order = $obj->get_order();
		if (is_object($order)) {
			$order_id = $order->get_id();

			// Check it has a Stripe Payment Method
			if (WOO::has_stripe_payment_method( $order_id )) {
				// Grab the Stripe Fee
				$stripe_fee = STRIPE::get_fee_amount( $order_id );

				// Remove it from the payment amount
				$amount = $amount - floatval($stripe_fee);

				/* translators: Log Item when Stripe Fee has been removed from the Payment send to Xero */
				$log_item_text = sprintf(__('Removed Stripe Fee ($%d) from Payment','woocommerce-xero-stripe-fees'), round( floatval($stripe_fee),2 ) );

				// Log it
				$this->log( $log_item_text );

			}
		}

		return $amount;
	}

	/**
	 * Log
	 * @param string $message
	 */
	private function log( $message = '' ) {
		xerostripefees()->log( $message );
	}

}
new Fees();