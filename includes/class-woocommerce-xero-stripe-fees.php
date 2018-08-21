<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://itchef.nz
 * @since      1.1.0
 *
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.1.0
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/includes
 * @author     IT Chef <hello@itchef.nz>
 */
class Woocommerce_Xero_Stripe_Fees {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      Woocommerce_Xero_Stripe_Fees_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {

		$this->plugin_name = 'woocommerce-xero-stripe-fees';
		$this->version = '1.3.4';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Update Options on pLugin Update
		add_action('plugins_loaded', array( $this, 'stripe_fee_update_options' ) );
		add_action('upgrader_process_complete', array( $this, 'stripe_fee_updated' ), 10, 2);


	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Xero_Stripe_Fees_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Xero_Stripe_Fees_i18n. Defines internationalization functionality.
	 * - Woocommerce_Xero_Stripe_Fees_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Xero_Stripe_Fees_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-xero-stripe-fees-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-xero-stripe-fees-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-xero-stripe-fees-admin.php';
        
        /**
		 * The class responsible for doing all the XML Heavy Lifting 
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dfcxml.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-xero-stripe-fees-public.php';

		$this->loader = new Woocommerce_Xero_Stripe_Fees_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Xero_Stripe_Fees_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_Xero_Stripe_Fees_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_Xero_Stripe_Fees_Admin( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        
        // Checks for Compatibility issues
        $plugin_admin->compatibility();
        
        // Adds the options page
        $this->loader->add_action( 'admin_init', $plugin_admin, 'settings_page', 11);
        
        // Add the WooCommerce Xero Extension Hook (Priority 15 so if they use another Xero Extension it should let that go first (11))
        $this->loader->add_filter( 'woocommerce_xero_invoice_to_xml', $plugin_admin, 'stripe_fees' , 15);
        
        // Adds the settings link to plugin
        $this->loader->add_filter( 'plugin_action_links_' . plugin_basename(getfile_woocommerce_xero_stripe_fees()), $plugin_admin, 'plugin_links');        
    
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		//$plugin_public = new Woocommerce_Xero_Stripe_Fees_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}
    
    /**
	 * Adds a line to the WooCommerce Xero Debug Log.
	 * 
	 * Debug option must be ticked in the WooCommerce Xero Extension
	 *
	 * @since    1.0.1
	 * @access   public static
	 */
    public static function log( $message = '' ) {
        
        $debug = get_option( 'wc_xero_debug' , '' );
        
        if (('on' == $debug) && (!empty($message))) {
            
            if (class_exists('WC_Logger')) {
                
                $logger = new WC_Logger();
                $logger->add( 'xero' , $message );
                
            }
        } 
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.1.0
	 * @return    Woocommerce_Xero_Stripe_Fees_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Runs when a Plugin update has been completed. Checks if it is this plugin and sets a transient
	 * @param WP_Upgrader $upgrader_object
	 * @param $hook_extra
	 * @since 1.3.4
	 */
	public function stripe_fee_updated(\WP_Upgrader $upgrader_object, $hook_extra)
	{
		if (is_array($hook_extra) && array_key_exists('action', $hook_extra) && array_key_exists('type', $hook_extra) && array_key_exists('plugins', $hook_extra)) {
			// check first that array contain required keys to prevent undefined index error.
			if ($hook_extra['action'] == 'update' && $hook_extra['type'] == 'plugin' && is_array($hook_extra['plugins']) && !empty($hook_extra['plugins'])) {
				// if this action is update plugin.
				$this_plugin = plugin_basename( \getfile_woocommerce_xero_stripe_fees() );

				foreach ($hook_extra['plugins'] as $each_plugin) {
					if ($each_plugin == $this_plugin) {
						// if this plugin is in the updated plugins.
						// set transient to let it run later.
						set_transient('woocommerce_xero_stripe_fees_updated', 1);
					}
				}
				unset($each_plugin);
			}
		}
	}


	/**
	 * Runs when a transient is detected and applies options update for default values for new options
	 * @since 1.3.4
	 */
	public function stripe_fee_update_options()
	{
		if (get_transient('woocommerce_xero_stripe_fees_updated') && current_user_can('manage_options')) {

			// update code here.
			if (false == get_option( 'wc_xero_dfc_stripe_fee_account') ) {
				// Ensure the default is set
				add_option( 'wc_xero_dfc_stripe_fee_account', 'on' );
			}

			// delete transient.
			delete_transient('woocommerce_xero_stripe_fees_updated');
		}
	}

}
