<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://itchef.nz
 * @since      1.1.0
 *
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Xero_Stripe_Fees
 * @subpackage Woocommerce_Xero_Stripe_Fees/admin
 * @author     IT Chef <hello@itchef.nz>
 */
class Woocommerce_Xero_Stripe_Fees_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.1.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Xero_Stripe_Fees_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Xero_Stripe_Fees_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-xero-stripe-fees-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Xero_Stripe_Fees_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Xero_Stripe_Fees_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-xero-stripe-fees-admin.js', array( 'jquery' ), $this->version, false );

	}
    
    /**
	 * Display a notice when incompatibilities are found.
	 * 
	 * Looks at enabled options for WooCommerce Xero and tells the user about issues 
	 *
	 * @since    1.1.0
	 */
    public function compatibility() {
        
        // Make sure the user is admin (no point displaying it otherwise)
        if (is_admin()) {
            // Get Current WooCommerce Xero Options
            $xero_send_payments = get_option( 'wc_xero_send_payments' , '');

            if ('on' == $xero_send_payments) {
                // Tell the user that sending Payments to Xero will not work with this plugin active (TODO in the future)
                add_action('admin_notices', function() {
                    echo('<div class="notice notice-warning"><p>['.$this->plugin_name.'] '.__('Sending payments to Xero does not work with this plugin enabled. Please turn off this option to avoid getting errors','woocommerce-xero-stripe-fees').'</p></div>');
                });
            }
        }
        
    }
    
    /**
	 * Register the settings and fields for options page.
	 * 
	 * Uses the existing WooCommerce Xero Extension options page, makes it easy for the user to manage in one place
	 *
	 * @since    1.1.0
	 */
    
    public function settings_page() {

        // Stripe Fee Xero Account
        register_setting(
            'woocommerce_xero',                 // settings page
            'wc_xero_dfc_stripe_fee_account'          // option name
        );


        add_settings_field(
            'wc_xero_dfc_stripe_fee_account',      // id
            __('Stripe Fee Account', 'woocommerce-xero-stripe-fees'),              // setting title
            array( $this, 'fees_input' ),    // display callback
            'woocommerce_xero',                 // settings page
            'wc_xero_settings'                  // settings section
        );
        
        // Xero / Stripe Country
        register_setting(
            'woocommerce_xero',                 // settings page
            'wc_xero_dfc_stripe_fee_country'          // option name
        );


        add_settings_field(
            'wc_xero_dfc_stripe_fee_country',      // id
            __('Stripe Country', 'woocommerce-xero-stripe-fees'),              // setting title
            array( $this, 'country_input' ),    // display callback
            'woocommerce_xero',                 // settings page
            'wc_xero_settings'                  // settings section
        );

    }
    
    /**
	 * The STRIPE FEE ACCOUNT field outputed as HTML 
	 * 
	 * Outputs the Stripe Account Text Field
	 *
	 * @since    1.1.0
	 */
    public function fees_input() {
        
        $value = get_option( 'wc_xero_dfc_stripe_fee_account' , '');
        // echo the field
        ?>
        <input id='wc_xero_dfc_stripe_fee_account' name='wc_xero_dfc_stripe_fee_account' type='text' value='<?php echo esc_attr( $value ); ?>' /> 
        <p class="description"><?php _e('Code for Xero account to track Stripe Fees paid.', 'woocommerce-xero-stripe-fees'); ?></p>
        <?php
    }
    
    /**
	 * The Xero Country field outputed as HTML 
	 * 
	 * Outputs the Stripe Country Dropdown
	 * 
	 *     Australia       GST 10%
	 *     New Zealand     GST 15%
	 *     United States   NO TAX
	 *     Canada          NO TAX
	 *     EU              NO TAX
	 *     Ireland         VAT 23%
	 *
	 * @since    1.1.0
	 */
    public function country_input() {
        
        $value = get_option( 'wc_xero_dfc_stripe_fee_country' , '');
        
        
        // If no country is set, automaticly set it to what WooCommerce has been set to (if supported below)
        if ('' == $value) {
            $value = get_option( 'woocommerce_default_country', '');
            if ('' == $value) {
                $value = substr($value, 0, 2);
                if (!in_array($value, array('AU','NZ','US','CA','IE'))) {
                    $value = '';
                }
            }
            
            
        }
        
        // echo the field
        ?>
        <select id="wc_xero_dfc_stripe_fee_country" name="wc_xero_dfc_stripe_fee_country">
          <option value=""<?php if ('' == $value) {echo(' selected'); } ?> disabled><?php _e('Select Stripe Country', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="AU"<?php if ('AU' == $value) { echo(' selected'); } ?>><?php _e('Australia', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="NZ"<?php if ('NZ' == $value) { echo(' selected'); } ?>><?php _e('New Zealand', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="US"<?php if ('US' == $value) { echo(' selected'); } ?>><?php _e('United States of America', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="CA"<?php if ('CA' == $value) { echo(' selected'); } ?>><?php _e('Canada', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="EU"<?php if ('EU' == $value) { echo(' selected'); } ?>><?php _e('European Union', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="IE"<?php if ('IE' == $value) { echo(' selected'); } ?>><?php _e('Ireland', 'woocommerce-xero-stripe-fees'); ?></option>
          <option value="XX"<?php if ('XX' == $value) { echo(' selected'); } ?>><?php _e('Other (NO TAX Calculations)', 'woocommerce-xero-stripe-fees'); ?></option>
        </select>
        <p class="description"><?php _e('The country you have registered with Stripe.', 'woocommerce-xero-stripe-fees'); ?></p>
        <?php
    }
    
    /**
	 * The actual code that makes this plugin work.
	 * 
	 * Takes the XML that the WooCommerce Xero Extension creates and changes it 
	 * with the required code before handing it back to the Extension to Send to Xero. Uses a filter to apply it.
	 *
	 * @since     1.1.0
	 * @return    string    XML output used for Xero.
	 */
    public function stripe_fees( $xml_input ) {
        
        // Debug Log
        $this->log('PLUGIN START - '.$this->plugin_name.' ('.$this->version.')');
        
        // This is the Xero account code for the Stripe Fee (that you create under Chart of accounts in Xero)
        $xero_fees_account = get_option('wc_xero_dfc_stripe_fee_account', 0);

        if (0 == $xero_fees_account) {

            // DEBUG
            $this->log("Abort Stripe Fee -> No Xero Stripe Fee Account Code Set");

           return $xml_input;
        }

        // Read XML to object, with Wrappers, otherwise the xml parser panics...
        $xml = new SimpleXMLElement('<root>'.$xml_input.'</root>');

        // Object to Array (the dirty way)
        $data_array = json_decode(json_encode($xml),true);
        
        // Data Validation
        /*
        if (!$this->array_validate('Invoice','LineItems','Total','Url')) {
            
            // Required data points not found, aborting
            $this->log('Abort Stripe Fee -> Invalid input data');
            return $xml_input;
        }*/

        // Get Order ID from Data
        $arr = explode('post.php?post=', $data_array['Invoice']['Url']);
        $order_id = $arr[1];
        $arr = explode('&', $order_id);
        $order_id = $arr[0];

        // None found?, lets abort
        if (empty($order_id)) {

            // DEBUG
            $this->log("Abort Stripe Fee -> Unable to find order id" );

           return $xml_input;
        }


        // Not using Stripe, lets abort
        if ('stripe' !== get_post_meta( $order_id, '_payment_method', true ) ) {

            // DEBUG
            $this->log("Abort Stripe Fee -> Payment Method (id: ".$order_id."): ".get_post_meta( $order_id, '_payment_method', true ));

           return $xml_input;
        }

        // Has the payment been proccessed? (to get the Stripe Fee)
        $stripe_fee = get_post_meta( $order_id , 'Stripe Fee', true );

        // No fee found? lets abort
        if (empty($stripe_fee)) {
            
            // Add Order Note
            $this->add_order_note($order_id,
                                  apply_filters( 'wc_xero_stripe_fee_not_found_text' , __('ERROR: No Stripe Fee found for order','woocommerce-xero-stripe-fees'))
                                 );

            // DEBUG
            $this->log("Abort Stripe Fee -> No Fee Found");

           return $xml_input;
        }

        // Keep Orginal fee for changing the totals later
        $stripe_fee_org = $stripe_fee;
        
        // Stripe Fee Description (translation and DEV friendly)
        $stripe_fee_txt = apply_filters( 'wc_xero_stripe_fee_text' , __('Stripe Fee','woocommerce-xero-stripe-fees'));        
        
        // Get Stripe Country
        $stripe_country = get_option('wc_xero_dfc_stripe_fee_country', '');
        
        // Defaults to NO TAX for the stripe fees
        $stripe_fee_includes_tax=false;
        $stripe_fee_tax_code='NONE';
        
        // Stripe Fee TAX Calculations
        switch($stripe_country) {
                
            case "AU":
                // Australia, Remove 10% GST from the Stripe Fee 
                $stripe_fee = $stripe_fee / 1.1;
                $stripe_fee_tax_code = "INPUT";
                $stripe_fee_includes_tax=true;
                break;
                
            case "NZ":
                // New Zealand, Remove 15% GST from the Stripe Fee 
                $stripe_fee = $stripe_fee - (( $stripe_fee * 3 ) / 23 );
                $stripe_fee_tax_code = "INPUT2";
                $stripe_fee_includes_tax=true;
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
                // Ireland, Remove 23% GST from the Stripe Fee 
                $stripe_fee = $stripe_fee / 1.23;
                $stripe_fee_tax_code = "INPUT2";
                $stripe_fee_includes_tax=true;
                break;
                
            case "XX":
                // Other, Leave it as is 
                break;

        }
        
        // DEBUG
        $this->log('> '.$stripe_fee_txt.': '.$stripe_fee_org. ' (GST/VAT ex. '.$stripe_fee.')');

        // Turn the stripe fee negative (to remove from the total)
        $stripe_fee_n = 0-$stripe_fee;

        
        // Add the Stripe Fee into the Line Items
        
        //DEBUG
        $this->log('RAW: '.json_encode($data_array['Invoice']['LineItems']));
        
        // Setup the Group
        $group = array();
        
        // How many items to we have in the order?
        $order_items_i = count($data_array['Invoice']['LineItems']);
        
        // If more then 1, use the group method, else just add.
        if (1 < $order_items_i) {
            // Split the products
            foreach($products['LineItem'] as $item) { 
                $group[] = $item;
            }
        } else {
            // Just add the product (just 1) to the group
            $group[] = $data_array['Invoice']['LineItems'];
        }

        // Create the Stripe Fee
        $stripe_line = array(
                        'Description'=>"$stripe_fee_txt",
                        'AccountCode'=>"$xero_fees_account",
                        'UnitAmount'=>"$stripe_fee_n",
                        'Quantity'=>'1',
                        'TaxType'=>"$stripe_fee_tax_code"
                    );

        // Add the Stripe Fee
        $group[] = $stripe_line;

        // Add the Line Item per Array (for XML Keys) (if more then 1 product)
        if (1 < $order_items_i) {
            $group = array('LineItem'=>$group);
        }
        
        // Merge it back into the main Data Stream
        $data_array['Invoice']['LineItems'] = $group;
        
        //DEBUG
        $this->log('MERGED: '.json_encode($data_array['Invoice']['LineItems']));
        
        // Change the Total
        $data_array['Invoice']['Total'] = $data_array['Invoice']['Total'] - $stripe_fee_org;

        // Change it back to a string (xml issues...)
        $data_array['Invoice']['Total'] = (string)$data_array['Invoice']['Total'];
        
        // Create XML
        $xml_output = DFC_XML::arrayToXML($data_array);
        
        // DEBUG
        $this->log('> UNFILTERED XML');
        $this->log('  '.$xml_output);
        $this->log('');
        
        // Safeguard for empty tags in Tax Amount (array issues) - TODO avoid getting them in the first instance
        $xml_output = str_replace("<TaxAmount></TaxAmount>", "<TaxAmount>0</TaxAmount>", $xml_output);
        
        // Safeguard for <0> and </0> tags (array issues) - TODO avoid getting them in the first instance
        $xml_output = str_replace("<0>", "", $xml_output);
        $xml_output = str_replace("</0>", "", $xml_output);
        
        // Dirty Safeguard for double/triple/quadruple LineItem tags (array issues) - TODO avoid getting them in the first instance
        $xml_output = str_replace("<LineItem><LineItem><LineItem><LineItem>", "<LineItem>", $xml_output);
        $xml_output = str_replace("</LineItem></LineItem></LineItem></LineItem>", "</LineItem>", $xml_output);
        $xml_output = str_replace("<LineItem><LineItem><LineItem>", "<LineItem>", $xml_output);
        $xml_output = str_replace("</LineItem></LineItem></LineItem>", "</LineItem>", $xml_output);
        $xml_output = str_replace("<LineItem><LineItem>", "<LineItem>", $xml_output);
        $xml_output = str_replace("</LineItem></LineItem>", "</LineItem>", $xml_output);
            
        // DEBUG
        $this->log('> XML Returned');
        $this->log('  '.$xml_output);
        $this->log('PLUGIN END - '.$this->plugin_name.' ('.$this->version.')');
        
        //throw new Exception('DEBUG');
        
        // Add Order Note
        $this->add_order_note($order_id, apply_filters( 'wc_xero_stripe_fee_order_note' , 
                                        sprintf(esc_html__('Stripe Fee (%s) added to Xero invoice','woocommerce-xero-stripe-fees'), round($stripe_fee_org,2)
                                               )));
        

        // Give it back to Woocommerce Xero Extension to Send
        return $xml_output;
        
    }
    
    /**
	 * Debug functions. 
	 * 
	 * Sends it to main class debug logger
	 *
	 * @since    1.1.0
	 */
    private function log($message='') {
        
        Woocommerce_Xero_Stripe_Fees::log($message);
    }
    
    /**
     * Data Validation of ARRAY
     * 
     * The values in this arrays contains the names of the indexes (keys) that should 
     * exist in the data array. Returns true if all matching
     *
     * @since    1.1.0
     * @returns boolean
     * @credit https://stackoverflow.com/posts/18250308/revisions
     * 
     */
    private function array_validate( $needed_keys , $data) {

        // Check input is an array and not empty
        if (!is_array($needed_keys) || !is_array($data) || empty($needed_keys) || empty($data)) {
            return false;
        }

        // Check if the needed keys are present in the array
        if(count(array_intersect_key(array_flip($needed_keys), $data_array)) === count($needed_keys)) {
            // Yep all keys present!
            return true;
        }

        return false;

    }
    
    /**
	 * Add private order note 
	 * 
	 * Creates a new note for the order id
	 *
	 * @since    1.1.0
	 */
    private function add_order_note( $order_id , $note ) {
        // Add order note 
        $order      = wc_get_order( $order_id );
        $comment_id = $order->add_order_note( $note );
    }
    
    /**
	 * Add Settings link to plugin 
	 * 
	 * Creates a settings link on the plugins list
	 *
	 * @since    1.1.0
	 */
    public function plugin_links( $links ) {
        
       array_unshift($links, '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=woocommerce_xero') ) .'">'.__( 'Settings' ).'</a>');
        
       return $links;
    }

}
