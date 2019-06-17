<?php
namespace XEROSTRIPEFEES;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class XERO
 * @package XEROSTRIPEFEES
 */
class XERO {

	// XML
	const XML_URL = 'Url';
	const URL_CONTACTS = 'https://api.xero.com/api.xro/2.0/Contacts';

	// Data Types
	const DATA_TYPE_INVOICE = 'Invoice';
	const DATA_TYPE_PAYMENT = 'Payment';

	// Contact
	const CONTACT = 'Contact';
	const CONTACT_ID = 'ContactID';

	// Line Items
	const LINEITEMS = 'LineItems';
	const LINEITEM = 'LineItem';

	const DESCRIPTION = 'Description';
	const ACCOUNTCODE = 'AccountCode';
	const UNITAMOUNT = 'UnitAmount';
	const QUANTITY = 'Quantity';
	const TAXTYPE = 'TaxType';
	const TAXAMOUNT = 'TaxAmount';

	// Total
	const TOTAL = 'Total';
	const TOTAL_TAX = 'TotalTax';

	// Transients
	const XERO_CONTACT_TRANSIENT = 'wc_xero_contact_id_';

	/**
	 * Creates the needed Transient that the Woo Xero plugin uses to manage contacts
	 */
	static function set_default_contact() {

		if ('on' == get_option('wc_xero_dfc_stripe_fee_contact_override') ) {

			$email    = get_option( 'wc_xero_dfc_stripe_fee_contact_master' );
			$value_id = get_option( 'wc_xero_dfc_stripe_fee_contact_id' );

			if ( $email && $value_id ) {
				set_transient( self::XERO_CONTACT_TRANSIENT . md5( $email ), $value_id, YEAR_IN_SECONDS );
			}
		}
	}

	/**
	 * Replaces the Xero Contact ID in the Data Stream
	 * @param array $array
	 *
	 * @return array
	 */
	static function update_array_contact_id($array=array()) {

		if ('on' == get_option('wc_xero_dfc_stripe_fee_contact_override') ) {

			$value = get_option( 'wc_xero_dfc_stripe_fee_contact_master' );
			$value_id = get_option( 'wc_xero_dfc_stripe_fee_contact_id' );

			if ( $value && $value_id ) {
				if ( array_key_exists( self::DATA_TYPE_INVOICE, $array ) ) {
					if ( array_key_exists( self::CONTACT, $array[ self::DATA_TYPE_INVOICE ] ) ) {
						if ( array_key_exists( self::CONTACT_ID,
							$array[ self::DATA_TYPE_INVOICE ][ self::CONTACT ] ) ) {

							// Replace Xero Contact ID
							$array[ self::DATA_TYPE_INVOICE ][ self::CONTACT ][ self::CONTACT_ID ] = $value_id;

							// Update Transient
							self::set_default_contact();
						}
					}
				}
			}
		}

		return $array;
	}

}