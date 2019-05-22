<?php


namespace XEROSTRIPEFEES;


use LaLit\Array2XML;
use LaLit\XML2Array;
use WC_Tax;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class WOO {

	/**
	 * Checks if the payment method used for this order was Stripe
	 * @param $order_id
	 *
	 * @return bool
	 */
	public static function has_stripe_payment_method( $order_id ) {
		$order = self::get_order( $order_id );
		if (false == $order) {
			xerostripefees()->log('ERROR: getting order for checking payment method');
			return false;
		}

		// Check if Stripe is the payment method
		$payment_method = $order->get_payment_method();
		if ('stripe' == $payment_method) {
			return true;
		}
		return false;
	}

	/**
	 * Gets the order object
	 * @param $order_id
	 *
	 * @return bool|\WC_Order|\WC_Order_Refund
	 */
	public static function get_order( $order_id = 0 ) {
		if (function_exists('wc_get_order') && (0 !== $order_id)) {
			return wc_get_order( $order_id );
		}
		return false;
	}

	/**
	 * Add private order note
	 * Creates a new note for the order id
	 *
	 * @param int $order_id
	 * @param string $note
	 */
	public static function add_order_note( $order_id = 0 , $note = '') {
		// Add order note
		$order      = wc_get_order( $order_id );
		$comment_id = $order->add_order_note( $note );
	}
}