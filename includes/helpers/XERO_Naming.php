<?php


namespace XEROSTRIPEFEES;


/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class XERO_Naming {

	// XML
	const XML_URL = 'Url';

	// Data Types
	const DATA_TYPE_INVOICE = 'Invoice';
	const DATA_TYPE_PAYMENT = 'Payment';

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

}