<?php

namespace XEROSTRIPEFEES;

use WC_Logger;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class XEROSTRIPEFEES
 * @package XEROSTRIPEFEES
 */
class XEROSTRIPEFEES {

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

		// Load Helpers
		$this->load_helpers(
			array(
				'XML',
				'WOO',
				'STRIPE',
				'XERO'
			)
		);

		// Load Modules
		$this->load_modules(
			array(
                'Options',
				'Fees',
				'Accounts',
                'Contacts'
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

		// Add the Woocommerce Xero Hooks
		add_filter('woocommerce_xero_invoice_to_xml', array( $this , 'process_invoice_data'), 10, 2 );
	}

	/**
	 * Convert XML to Array for data changes.
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

			// Overwrite Xero Contact if needed
			$xml_array = XERO::update_array_contact_id($xml_array);

            // Logic Check on the Array
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
     * Adds a settings link to the plugin row
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function plugin_settings_link( $actions ) {
		/* translators: Plugin Settings link */
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=woocommerce_xero' ), __( 'Settings', 'woocommerce-xero-stripe-fees' ) ) );

		return $actions;
	}

	/**
     * Logging in Debug Mode
	 * @param string $message
	 */
    public function log($message='') {

	    if (is_array($message) || is_object($message)) {
		    $message = print_r($message,1);
	    }

	    // Running in Xero Debug Mode?
	    $debug = get_option( 'wc_xero_debug' , false );
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
			if (!function_exists('deactivate_plugins')) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			\deactivate_plugins( XEROSTRIPEFEES_FILE );

			// Tell the user
			add_action('admin_notices', array( $this, 'show_needed_plugins_notice_xero' ) );

		}
	}

	/**
	 * Shows an admin notice if missing plugins
	 */
	public function show_needed_plugins_notice_xero_fees() {

		/* translators: Woocommerce Xero Stripe Fees Plugin Name for Missing plugin notices */
		$name = __('WooCommerce Xero Stripe Fees','woocommerce-xero-stripe-fees');

		/* translators: Woocommerce Xero Stripe Fees Plugin is required for this plugin to run */
		$why = __('is required to be installed and activated!','woocommerce-xero-stripe-fees');

		$link = sprintf('<a href="https://github.com/dfinnema/woocommerce-xero-stripe-fees">%s</a> %s', esc_html( $name ), esc_html( $why ) );

		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo($link); ?></p>
		</div>
		<?php
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