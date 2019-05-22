<?php


namespace XEROSTRIPEFEES;


use LaLit\Array2XML;
use LaLit\XML2Array;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class STRIPE {

	/**
	 * Gets the Stripe fee for an order
	 * @param int $order_id
	 *
	 * @return bool|mixed
	 */
	public static function get_fee_amount( $order_id = 0 ) {
		if (0 !== $order_id && !empty($order_id)) {
			$payment_captured = get_post_meta( $order_id , '_stripe_charge_captured',true);
			$payment_fee_amount = get_post_meta( $order_id , '_stripe_fee',true);

			// Make sure the payment has been captured, otherwise we dont know the amount
			if ('yes' == $payment_captured) {

				// Grab the Fee Amount
				if ( $payment_fee_amount && is_numeric( $payment_fee_amount ) && 0 !== $payment_fee_amount ) {
					return $payment_fee_amount;
				}
			}

		}
		return false;
	}

	/**
	 * Get the Stripe Fee Amount
	 *
	 * @param int $order_id
	 *
	 * @return bool|mixed
	 */
	public static function get_net_amount( $order_id = 0 ) {
		if (0 !== $order_id && !empty($order_id)) {
			$payment_captured = get_post_meta( $order_id , '_stripe_charge_captured',true);
			$payment_net = get_post_meta( $order_id , '_stripe_net',true);

			// Make sure the payment has been captured, otherwise we dont know the amount
			if ('yes' == $payment_captured) {

				// Grab the Fee Amount
				if ($payment_net && is_numeric($payment_net) && 0!==$payment_net) {
					return $payment_net;
				}
			}

		}
		return false;
	}

	/**
	 * Gets the Stripe Fee based on the Stripe Country.
	 * Calculates the Fee without any taxes added depending on if taxes are enabled
	 * and what Stripe Country the account is for
	 *
	 * @param int $stripe_fee
	 *
	 * @return float|int
	 */
	public static function get_fee_after_tax_calculations( $stripe_fee = 0 ) {

		// Get Stripe Country
		$stripe_country = get_option( 'wc_xero_dfc_stripe_fee_country','XX');

		// Are taxes enabled in WooCommerce?
		$woo_taxes_enabled = \wc_tax_enabled();

		// Stripe Fee TAX Calculations
		switch ( $stripe_country ) {

			case "AU":
				if (true == $woo_taxes_enabled) {
					// Australia, Remove 10% GST from the Stripe Fee (gets added later)
					$stripe_fee              = $stripe_fee / 1.1;
				}

				break;

			case "NZ":
				if (true == $woo_taxes_enabled) {
					// New Zealand, Remove 15% GST from the Stripe Fee (gets added later)
					// @based on https://www.classic.ird.govt.nz/gst/additional-calcs/calc-adjust/calc-sales/sales-income.html
					$stripe_fee              = $stripe_fee - ( ( $stripe_fee * 3 ) / 23 );
				}

				break;

			case "US":
				// USA, Leave it as is
				break;

			case "CA":
				// Canada, Leave it as is
				break;

			case "EU":
				// European Union, Leave it as is
				break;

			case "IE":
				if (true == $woo_taxes_enabled) {
					// Ireland, Remove 23% GST from the Stripe Fee (gets added later)
					// @based on https://vatcalculator.eu/ireland-vat-calculator/
					$stripe_fee              = ( $stripe_fee / 123 ) * 100;
				}

				break;

			case "XX":
				// Other, Leave it as is
				break;

		}

		return (float) $stripe_fee;
	}
}