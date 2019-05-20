<?php

namespace XEROSTRIPEFEES;

use WC_Logger;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class XEROSTRIPEFEES {

	/**
	 * DB Key Name
	 * @var string
	 */
	private $options_name = 'xerostripefees_options';

	/**
     * Debug Mode?
	 * @var bool
	 */
	private $debug=false;

	/**
	 * Holds Options
	 * @var array
	 */
	private $options = array();

	/**
	 * XEROSTRIPEFEES constructor.
	 */
	public function __construct() {
		/* Do Nothing Here */
	}

	/**
	 * Init XEROSTRIPEFEES
	 */
	public function init() {

	    // Check on other needed plugins
		$this->has_needed_plugins();

		// Add Text domain Support
        $this->add_textdomain_support();

        // Load Composer ( XML Conversion Functions )
		$this->composer_load();

		// Load Options
		$this->options_load();

		// Get Helpers
		$this->load_helpers(
			array(
				'XML',
				'WOO',
				'STRIPE',
				'XERO_Naming'
			)
		);

		// Get Modules
		$this->load_modules(
			array(
                'Xero_Options'
			)
		);


		// Load Hooks
		$this->hooks();

	}

	/**
	 * Adds the needed Hooks for this plugin to function
	 */
	private function hooks() {

		// Settings Page Link in Plugin Row
		add_filter( 'plugin_action_links_' . plugin_basename( XEROSTRIPEFEES_FILE ), array( $this , 'plugin_settings_link' ) );

		// Convert Data, priority 15 in case another plugin needs to access the data
		add_filter('woocommerce_xero_stripe_fees_array', array( $this , 'add_stripe_fee' ), 15, 2 );

		// Add the Woocommerce Xero Hooks
		add_filter('woocommerce_xero_invoice_to_xml', array( $this , 'process_invoice_data'), 10, 2 );
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
		if (array_key_exists(XERO_Naming::DATA_TYPE_INVOICE,$data)) {
			$invoice = $data[XERO_Naming::DATA_TYPE_INVOICE];

			// Ensure we are using Stripe as a Payment Method
			if (WOO::has_stripe_payment_method( $order_id )) {
				error_log(' + Stripe Payment Method');

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
					$stripe_fee_account_code = get_option( 'wc_xero_dfc_stripe_fee_account' , 0);
					if (false !== $stripe_fee_account_code || 0 !== $stripe_fee_account_code) {

						// Make sure we have line items
						if (array_key_exists(XERO_Naming::LINEITEMS,$invoice) && !empty($invoice[XERO_Naming::LINEITEMS])) {
							if (isset($invoice[XERO_Naming::LINEITEMS][XERO_Naming::LINEITEM])) {

								$this->log(' + Line Items Found');
								$line_items = $invoice[XERO_Naming::LINEITEMS][XERO_Naming::LINEITEM];
								$x = count($line_items);

								// Add Line Item for the Stripe Fee
								$line_items[$x] = array(
									XERO_Naming::DESCRIPTION    => __('Stripe Fee','woocommerce-xero-stripe-fees'),
									XERO_Naming::ACCOUNTCODE    => $stripe_fee_account_code,
									XERO_Naming::UNITAMOUNT     => $stripe_fee_negative,
									XERO_Naming::QUANTITY       => 1,
									XERO_Naming::TAXTYPE        => 'NONE',
									XERO_Naming::TAXAMOUNT      => 0,
								);

								// Remove Tax Type and set tax amount when  Calculating Taxes
								if (true == $woo_taxes_enabled) {

									$this->log(' + Taxes Enabled');

									// Floatval just in case
									$stripe_fee_tax_amount = floatval($stripe_fee_orginal) -  floatval($stripe_fee_net);

									// Remove the Type as it gets added by Xero
									unset($line_items[$x][XERO_Naming::TAXTYPE]);

									// Add the Tax Amount to the item (ensure it is a negative)
									$line_items[$x][XERO_Naming::TAXAMOUNT] = 0 - $stripe_fee_tax_amount;

									// Add Tax to total
									$invoice[XERO_Naming::TOTAL_TAX] = $invoice[XERO_Naming::TOTAL_TAX] + $stripe_fee_tax_amount;
								}

								// Add Line Items back to invoice
								$invoice[XERO_Naming::LINEITEMS][XERO_Naming::LINEITEM] = $line_items;

								// Adjust Invoice Total
								$invoice[XERO_Naming::TOTAL] = $invoice[XERO_Naming::TOTAL] - $stripe_fee;

								// Merge Data back into Xero Data Packet
								$data[XERO_Naming::DATA_TYPE_INVOICE] = $invoice;

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
	 * Preps the Data for the Invoice getting send to Xero
	 * @param $xml
	 * @param $obj
	 *
	 * @return bool|mixed|string|string[]|null
	 * @throws \Exception
	 */
	public function process_invoice_data( $xml, $obj ) {
		if (!empty($xml)) {

			// Convert to Array
			$xml_array = XML::to_array( $xml );

			// Grab the main node from XML (eg; Invoice )
			$xml_node = XML::get_main_node( $xml );
			if (false == $xml_node) {
				$this->log('Could not locate main node');
				wp_die('TEST');
				return $xml;
			}

			// Grab Order ID
			$order = $obj->get_order();
			if (!is_object($order)) {
				$this->log(' Could not get order ID');
				return $xml;
			}
			$order_id = $order->get_id();

			// Add the Fees, uses a filter for easy expansion options
			$xml_array = apply_filters('woocommerce_xero_stripe_fees_array', $xml_array , $order_id );
			if (false === $xml_array || !is_array($xml_array) || empty($xml_array)) {
				$this->log('Failed to Update Data');
				return $xml;
			}

			// Convert to XML
			$new_xml = XML::to_xml( $xml_array , $xml_node);
			if (false == $new_xml) {
				$this->log('XML Failed');
				return $xml;
			}

			// Send the new XML Back
			return $new_xml;

		}
		return $xml;
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

				// Add the Order Note
				//WOO::add_order_note( $order_id, $log_item_text);

			}
		}

		return $amount;
	}

	/**
     * Adds a settings link to the plugin row
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function plugin_settings_link( $actions ) {
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=woocommerce_xero' ), __( 'Settings', 'woocommerce-xero-stripe-fees' ) ) );

		return $actions;
	}

	/**
     * Running in Debug Mode?
	 * @return bool
	 */
	public function is_debug_mode() {
	    return $this->debug;
    }

	/**
     * Logging in Debug Mode
	 * @param string $message
	 */
    public function log($message='') {

	    if (is_array($message) || is_object($message)) {
		    $message = print_r($message,1);
	    }

	    if ( $this->is_debug_mode() ) {
		    error_log('[Xero Sripe Fees] - '.$message);
        }

	    // Running in Xero Debug Mode?
	    $debug = get_option( 'wc_xero_debug' , '' );
	    if (('on' == $debug) && (!empty($message))) {
		    if ( class_exists( 'WC_Logger' ) ) {
			    $logger = new WC_Logger();
			    $logger->add( 'xero', '[Xero Sripe Fees] - '.$message );
		    }
	    }
    }

	/**
	 * Load Composer
	 */
	private function composer_load() {
		$file = plugin_dir_path( XEROSTRIPEFEES_FILE ) . 'vendor/autoload.php';
		if (file_exists( $file )) {
			require_once $file;
		}
	}

	/**
	 * Load Options
	 * JSON in DB to minimize unserializing risks
	 */
	private function options_load() {
		$options = get_option( $this->options_name );
		if ($options) {
			$options = json_decode($options,1);
			$this->options = $options;
		}

		// Debug Mode
        if (defined('XEROSTRIPEFEES_DEBUG') && XEROSTRIPEFEES_DEBUG) {
            $this->debug=true;
        }

	}

	/**
	 * Save Options
	 * JSON in DB to minimize unserializing risks
	 */
	private function options_save() {
		if (!empty($this->options)) {
			$options = $this->options;
			$options = json_encode( $options );
			update_option( $this->options_name , $options );
		}
	}

	/**
	 * Loads the relevant option
	 * @param string $name
	 * @param bool $default false
	 *
	 * @return bool|mixed
	 */
	public function get($name='',$default=false) {
		if (!empty($name) && !empty($this->options) ) {
			if (array_key_exists($name,$this->options)) {
				return $this->options[$name];
			}
		}
		return $default;
	}

	/**
	 * Update Option
	 * @param string $name
	 * @param string $value optional
	 * @param bool $permanent defaults to true otherwise the option is discarded after request
	 */
	public function set($name='',$value='',$permanent=true) {
		if (!empty($name)) {
			$this->options[$name]=$value;
			if ($permanent) {
				$this->options_save();
			}
		}
	}

	/**
	 * Loads any needed Modules
	 * @param array $modules
	 */
	private function load_modules($modules = array() ) {
		$path = plugin_dir_path( XEROSTRIPEFEES_FILE ) . 'includes/modules/';
		foreach ($modules as $module) {
			if (file_exists($path.$module.'.php')) {
				require_once($path.$module.'.php');
			}
		}
	}

	/**
	 * Loads any needed Helpers
	 * @param array $helpers
	 */
	private function load_helpers($helpers = array() ) {
		$path = plugin_dir_path( XEROSTRIPEFEES_FILE ) . 'includes/helpers/';
		foreach ($helpers as $helper) {
			if (file_exists($path.$helper.'.php')) {
				require_once($path.$helper.'.php');
			}
		}
	}

	/**
	 * In case dependent plugins are removed, lets self-deactivate if needed.
	 */
	private function has_needed_plugins() {
		if (! in_array( 'woocommerce-xero/woocommerce-xero.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			// Nope not activate, lets deactivate this plugin
			deactivate_plugins( XEROSTRIPEFEES_FILE );

			$link = sprintf('<a href="https://woocommerce.com/products/xero/">%s</a> %s', esc_html(__('WooCommerce Xero Integration','woocommerce-xero-stripe-fees') ), esc_html(__('is required to be installed and activated!','woocommerce-xero-stripe-fees') ) );

			// Tell the user about it
			die('<p>'.$link.'</p>');
		}
    }

	/**
	 * Adds Textdomain Support
	 */
    private function add_textdomain_support() {
	    load_plugin_textdomain(
		    'woocommerce-xero-stripe-fees',
		    false,
		    plugin_dir_path( XEROSTRIPEFEES_FILE ) . 'languages/'
	    );
    }
}