<?php

namespace XEROSTRIPEFEES;

/**
 * Class Fees
 * @package XEROSTRIPEFEES
 */
class Contacts {

	/**
	 * Fees constructor.
	 */
	public function __construct() {

		// Make sure the option for calculating Stripe Fees is enabled
		if ('on' == get_option('wc_xero_dfc_stripe_fee_contact_override') ) {

			// Prevent Contact Updates to Xero
			add_filter('pre_http_request', array($this,'prevent_http_request_for_xero_contact') ,100,3);
		}
	}

	/**
	 * Prevents HTTP requests from being sent to Xero to avoid Contacts being created in Xero
	 * @param $preempt
	 * @param $r
	 * @param $url
	 *
	 * @return array
	 * @since 2.1.3
	 */
	public function prevent_http_request_for_xero_contact( $preempt, $r ,$url ) {
		// Check if we are about to make a request to Xero for creating a contact
		if (strpos($url, XERO::URL_CONTACTS) !== false) {

			$id = get_option('wc_xero_dfc_stripe_fee_contact_id');
			$email = get_option('wc_xero_dfc_stripe_fee_contact_master');

			// Make sure the required fields have been set
			if ($id && $email) {

				// Only prevent the request for contact requests that do not have the set Contact details, also any POST/PUT types
				if ( ( ! strpos( $url, urlencode( $email ) ) && ! strpos( $r['body'],
							'<ContactID>' . $id . '</ContactID>' ) ) || $r['method'] == 'POST' || $r['method'] == 'PUT' ) {

					// Return Dummy Data with only the ID and Email being set correctly
					$response = array(
						'body' => '<Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <Id>c3b460f7-ba3a-42c3-b1b8-f82d104700d1</Id>
  <Status>OK</Status>
  <ProviderName>DummyProvider</ProviderName>
  <DateTimeUTC>2019-06-16T04:01:46.134486Z</DateTimeUTC>
  <Contacts>
    <Contact>
      <ContactID>' . $id . '</ContactID>
      <ContactStatus>ACTIVE</ContactStatus>
      <Name>Dummy Name</Name>
      <FirstName>Dummy</FirstName>
      <LastName>Name</LastName>
      <EmailAddress>' . $email . '</EmailAddress>
      <Addresses>
        <Address>
          <AddressType>STREET</AddressType>
        </Address>
        <Address>
          <AddressType>POBOX</AddressType>
          <AddressLine1>1 Sample Street</AddressLine1>
          <City>Sample City</City>
          <Region>AK</Region>
          <PostalCode>1000</PostalCode>
          <Country>NZ</Country>
        </Address>
      </Addresses>
      <Phones>
        <Phone>
          <PhoneType>DDI</PhoneType>
        </Phone>
        <Phone>
          <PhoneType>DEFAULT</PhoneType>
          <PhoneNumber>00000000</PhoneNumber>
        </Phone>
        <Phone>
          <PhoneType>FAX</PhoneType>
        </Phone>
        <Phone>
          <PhoneType>MOBILE</PhoneType>
        </Phone>
      </Phones>
      <UpdatedDateUTC>2019-06-16T04:01:45.39</UpdatedDateUTC>
      <IsSupplier>false</IsSupplier>
      <IsCustomer>true</IsCustomer>
      <Balances>
        <AccountsReceivable>
          <Outstanding>0.00</Outstanding>
          <Overdue>0.00</Overdue>
        </AccountsReceivable>
        <AccountsPayable>
          <Outstanding>0.00</Outstanding>
          <Overdue>0.00</Overdue>
        </AccountsPayable>
      </Balances>
      <HasAttachments>false</HasAttachments>
    </Contact>
  </Contacts>
</Response>'
					);

					// Set Default Contact
					XERO::set_default_contact();

					// Prevent the request from being sent
					return $response;
				}
			}
		}
		return $preempt;
	}

	/**
	 * Log
	 * @param string $message
	 */
	private function log( $message = '' ) {
		xerostripefees()->log( $message );
	}

}
new Contacts();