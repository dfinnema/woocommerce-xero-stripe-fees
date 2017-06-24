<?php

/*
Plugin Name: Woocommerce Xero Stripe Fees
Plugin URI: https://github.com/dfinnema/WoocommerceXeroStripe
Description: Calculates and adds the Stripe Fees to your Xero Invoice
Author: IT Chef
Author URI: https://itchef.nz
Version: 0.2

*/

if (!function_exists('untrailingslashit') || !defined('WP_PLUGIN_DIR')) {
    // WordPress is probably not bootstrapped.
    exit;
}


// Plugin Updater
require 'plugin-update-checker/plugin-update-checker.php';
$dfc_puc = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/dfinnema/woocommerce-xero-stripe-fees',
	__FILE__,
	'woocommerce-xero-stripe-fees'
);
$dfc_puc->setBranch('release');

// Add Stripe Fees to Invoices send to Xero
function dfc_xero_stripe_fee( $in ) {
    
// DEBUG STUFF
$logger = new WC_Logger();
    
// This is the Xero account code for the Stripe Fee (that you create under Chart of accounts in Xero)
$xero_fees_account = get_option('wc_xero_dfc_stripe_fee_account', 0);

if (0 == $xero_fees_account) {

    // DEBUG
    $logger->log( "debug","Abort Stripe Fee -> No Xero Stripe Fee Account Code Set");

    return $in;
}
      
// Read XML to object, with Wrappers, otherwise the xml parser panics...
$xml = new SimpleXMLElement('<root>'.$in.'</root>');

// Object to Array (the dirty way)
$data_array = json_decode(json_encode($xml),true);
    
// Get Order ID from Data
$arr = explode('post.php?post=', $data_array['Invoice']['Url']);
$order_id = $arr[1];
$arr = explode('&', $order_id);
$order_id = $arr[0];

if (empty($order_id)) {
    
    // DEBUG
    $logger->log( "debug","Abort Stripe Fee -> Unable to find order id" );
    
    return $in;
}
    
    
// Not using Stripe, lets abort
if ('stripe' !== get_post_meta( $order_id, '_payment_method', true ) ) {
    
    // DEBUG
    $logger->log( "debug","Abort Stripe Fee -> Payment Method (id: ".$order_id."): ".get_post_meta( $order_id, '_payment_method', true ) );
    
    return $in;
}

// Has the payment been proccssed? (to get Stripe Fee)
$stripe_fee = get_post_meta( $order_id , 'Stripe Fee', true );

// No fee found? lets abort
if (empty($stripe_fee)) {
    
    // DEBUG
    $logger->log( "debug","Abort Stripe Fee -> No Fee Found" );
    
    return $in;
}
    
// Keep Orginal fee for changing the totals later
$stripe_fee_org = $stripe_fee;
    
// Remove GST from the Stripe Fee (15% for NZ as per IRD website)
$stripe_fee = $stripe_fee - (( $stripe_fee * 3 ) / 23 );

// Turn the stripe fee negative (to remove from the total)
$stripe_fee_n = 0-$stripe_fee;


// Add Stripe Fee
if (array_key_exists('Invoice',$data_array)) {
    if (array_key_exists('LineItems',$data_array['Invoice'])) {
        if (array_key_exists('Total',$data_array['Invoice'])) {
            
            // Set TMP ARRAY
            $dd = array('LineItems');

            // Grab Exisiting Data
            foreach($data_array['Invoice']['LineItems'] as $item) {
                
                // Add it to the TMP array
                $dd['LineItems'][] = $item;
            }
            
            // Lastly add the Stripe Fee, Xero can calculate tax...
             $dd['LineItems'][] = array(
                                'Description'=>'Stripe Fee',
                                'AccountCode'=>"$xero_fees_account",
                                'UnitAmount'=>"$stripe_fee_n",
                                'Quantity'=>'1');

            // Merge it back into the main Data Stream
            $data_array['Invoice']['LineItems'] = $dd['LineItems'];

            // Change the Total
            $data_array['Invoice']['Total'] = $data_array['Invoice']['Total'] - $stripe_fee_org;
            
            // Change it back to a string (xml issues...)
            $data_array['Invoice']['Total'] = (string)$data_array['Invoice']['Total'];
        }
        
    }
}


// Create XML
$xml_output = DFC_Helpers::arrayToXML($data_array);

// Give it back to Woocommerce Xero Extension to Send
return $xml_output;
    
}
add_filter( 'woocommerce_xero_invoice_to_xml', 'dfc_xero_stripe_fee', 11);



// Make it easy to change settings without editing plugin files
// Register and define the settings
// Register and define the settings
add_action('admin_init', 'dfc_xero_stripe_fee_setting', 11);
function dfc_xero_stripe_fee_setting(){
    
    
	register_setting(
		'woocommerce_xero',                 // settings page
		'wc_xero_dfc_stripe_fee_account'          // option name
    );
	
	add_settings_field(
		'wc_xero_dfc_stripe_fee_account',      // id
		'Stripe Fee Account',              // setting title
		'dfc_xero_stripe_fee_setting_input',    // display callback
		'woocommerce_xero',                 // settings page
		'wc_xero_settings'                  // settings section
	);
    

}

// Display and fill the form field
function dfc_xero_stripe_fee_setting_input() {
	$value = get_option( 'wc_xero_dfc_stripe_fee_account' , '');
	
	// echo the field
	?>
<input id='wc_xero_dfc_stripe_fee_account' name='wc_xero_dfc_stripe_fee_account'
 type='text' value='<?php echo esc_attr( $value ); ?>' /> 
    <p class="description">Code for Xero account to track Stripe Fees paid.</p>
	<?php
}


// The XML Class (yep straight from XERO PHP)
/**
 * Unfortunate class for methods that don't really have a home.
 * This is to avoid external dependencies.
 *
 * Class Helpers
 * @package XeroPHP
 */
class DFC_Helpers
{
    /**
     * Convert a multi-d assoc array into an xml representation.
     * Straightforward <key>val</key> unless there are numeric keys,
     * in which case, the parent key is singularised and used.
     *
     * @param array $array
     * @param null $key_override
     * @return string
     */
    public static function arrayToXML(array $array, $key_override = null)
    {
        $xml = '';
        foreach ($array as $key => $element) {
            if (is_array($element)) {
                //recurse and replace.
                if (self::isAssoc($element)) {
                    $element = self::arrayToXML($element);
                } else {
                    //Dirty dirty hack to make the 1.x branch work for tracking categories
                    //This is the only instance in the whole app of 'Tracking' so should be ok for BC.
                    if ($key === 'Tracking') {
                        $element = self::arrayToXML($element, 'TrackingCategory');
                    } else {
                        $element = self::arrayToXML($element, self::singularize($key));
                    }
                }
            } else {
                //Element escaping for the http://www.w3.org/TR/REC-xml/#sec-predefined-ent
                //Full DOMDocument not really necessary as we don't use attributes (which are more strict)
                $element = strtr(
                    $element,
                    [
                        '<' => '&lt;',
                        '>' => '&gt;',
                        '"' => '&quot;',
                        "'" => '&apos;',
                        '&' => '&amp;',
                    ]
                );
            }

            if ($key_override !== null) {
                $key = $key_override;
            }
            $xml .= sprintf('<%1$s>%2$s</%1$s>', $key, $element);
        }

        return $xml;
    }

    public static function XMLToArray(\SimpleXMLElement $sxml)
    {
        $output = [];
        $singular_node_name = self::singularize($sxml->getName());

        foreach ($sxml->children() as $child_name => $child) {
            /**
             * @var \SimpleXMLElement $child
             */
            if ($child->count() > 0) {
                $node = self::XMLToArray($child);
            } else {
                $node = (string) $child;
            }

            //don't make it assoc, as the keys will all be the same
            if ($child_name === $singular_node_name ||
                //Handle strange XML
                ($singular_node_name === 'Tracking' && $child_name === TrackingCategory::getRootNodeName())) {
                $output[] = $node;
            } else {
                $output[$child_name] = $node;
            }
        }

        return $output;
    }

    /**
     * This function is based on Wave\Inflector::singularize().
     * It only contains a fraction of the rules from its predecessor,
     * so only good for a quick basic singularisation.
     *
     * @param $string
     * @return mixed
     */
    public static function singularize($string)
    {
        $singular = [
            '/(vert|ind)ices$/i'    => "$1ex",
            '/(alias)es$/i'         => "$1",
            '/(x|ch|ss|sh)es$/i'    => "$1",
            '/(s)eries$/i'          => "$1eries",
            '/(s)tatus$/i'          => "$1tatus",
            '/([^aeiouy]|qu)ies$/i' => "$1y",
            '/([lr])ves$/i'         => "$1f",
            '/([ti])a$/i'           => "$1um",
            '/(us)es$/i'            => "$1",
            '/(basis)$/i'           => "$1",
            '/([^s])s$/i'           => "$1"
        ];

        // check for matches using regular expressions
        foreach ($singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        //Else return
        return $string;
    }

    public static function pluralize($string)
    {
        $plural = [
            '/(quiz)$/i'                     => "$1zes",
            '/(matr|vert|ind)ix|ex$/i'       => "$1ices",
            '/(x|ch|ss|sh)$/i'               => "$1es",
            '/([^aeiouy]|qu)y$/i'            => "$1ies",
            '/(hive)$/i'                     => "$1s",
            '/(?:([^f])fe|([lr])f)$/i'       => "$1$2ves",
            '/(shea|lea|loa|thie)f$/i'       => "$1ves",
            '/sis$/i'                        => "ses",
            '/([ti])um$/i'                   => "$1a",
            '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
            '/(bu)s$/i'                      => "$1ses",
            '/(alias)$/i'                    => "$1es",
            '/(ax|test)is$/i'                => "$1es",
            '/(us)$/i'                       => "$1es",
            '/s$/i'                          => "s",
            '/$/'                            => "s"
        ];

        // check for matches using regular expressions
        foreach ($plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    public static function isAssoc(array $array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Generic function to flatten an associative array into an arbitrarily
     * delimited string.
     *
     * @param array $array
     * @param string $format
     * @param string|null $glue
     * @param bool $escape
     * @return string|array If no glue provided, it won't be imploded.
     */
    public static function flattenAssocArray(
        array $array,
        $format,
        $glue = null,
        $escape = false
    ) {
        $pairs = [];
        foreach ($array as $key => $val) {
            if ($escape) {
                $key = self::escape($key);
                $val = self::escape($val);
            }
            $pairs[] = sprintf($format, $key, $val);
        }

        //Return array if no glue provided
        if ($glue === null) {
            return $pairs;
        } else {
            return implode($glue, $pairs);
        }
    }

    /**
     * OAuth compliant escaping functions.
     * In php, as simple as rawurlencode().
     * There were a lot more seemingly redundant transformations in
     * the SimpleOAuth class.
     *
     * @param $string
     * @return string
     */
    public static function escape($string)
    {
        return rawurlencode($string);
    }
}