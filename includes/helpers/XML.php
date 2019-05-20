<?php


namespace XEROSTRIPEFEES;


use LaLit\Array2XML;
use LaLit\XML2Array;

/* Ensure WP is Running */
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class XML {

	/**
	 * Converts the XML to ARRAY
	 * @param string $xml
	 *
	 * @return array|bool
	 * @throws \Exception
	 */
	public static function to_array( $xml = '' ) {
		if (!empty($xml)) {
			return XML2Array::createArray( $xml );
		}
		return false;
	}

	/**
	 * Converts the ARRAY back to XML
	 * @param array $array
	 * @param string $output_filter need the node to strip, use get_main_node() on the orginal xml to find it.
	 *
	 * @return bool|mixed|string|string[]|null
	 * @throws \Exception
	 */
	public static function to_xml( $array = array() , $output_filter = '') {

		if (!empty($array) || !is_array($array)) {
			// Convert back to XML
			$xml_new_obj = Array2XML::createXML( 'xerostripefees' , $array );
			$xml_new = $xml_new_obj->saveXML();

			// Filter XML
			$xml_new = self::filter_output($xml_new,$output_filter);
			if (empty($xml_new)) {
				error_log('Error Has occurred with filtering');
				return false;
			}

			// Filter Html and Spacing
			$xml_new = self::filter_line_breaks( $xml_new );
			$xml_new = self::filter_html( $xml_new );

			return $xml_new;
		}

		return false;
	}

	/**
	 * Gets the main node, if it cannot find it it will return default
	 * @param string $xml
	 * @param bool $default
	 *
	 * @return bool|string
	 */
	public static function get_main_node( $xml = '', $default = false ) {
		$re = '/^<([^\/].*?)>/m';
		preg_match($re, $xml, $matches, PREG_OFFSET_CAPTURE, 0);

		if (isset($matches[1][0])) {
			return $matches[1][0];
		}
		return $default;
	}

	/**
	 * Grab the Order from XML based on the URL
	 * @param string $xml
	 * @param bool $default
	 *
	 * @return bool
	 */
	public static function get_order_id( $xml = '', $default = false ) {
		$url = XERO_Naming::XML_URL;

		// Find the order ID from the url as the Reference number can be changed in the settings and filters.
		// Url can also be changed in filters but less likely to.
		$re = '/<'.$url.'>.*post\.php\?post=(\d*)&.*<\/'.$url.'>/m';

		preg_match($re, $xml, $matches, PREG_OFFSET_CAPTURE, 0);

		if (isset($matches[1][0])) {
			return $matches[1][0];
		}

		if (isset($matches[1]) && !is_array($matches[1])) {
			return $matches[1];
		}
		return $default;
	}

	/**
	 * Filters out any XML tags not inside the tags specified
	 * @param string $xml
	 * @param string $tag_name 'Invoice' as default
	 *
	 * @return string
	 */
	private static function filter_output( $xml = '', $tag_name = '' ) {

		$re = '/<'.$tag_name.'>[\s\S]*?<\/'.$tag_name.'>/m';
		preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);

		if (isset($matches[0][0])) {
			return $matches[0][0];
		}
		return '';
	}

	/**
	 * Removes any line breaks and spacing
	 * @param string $xml
	 *
	 * @return string|string[]|null
	 */
	private static function filter_line_breaks( $xml = '' ) {
		return preg_replace('/(\>)\s*\n*(\<)/m', '$1$2', $xml);
	}

	/**
	 * Replaces several url html entities
	 * @param string $xml
	 *
	 * @return mixed|string
	 */
	private static function filter_html( $xml = '' ) {
		$html_to_replace = array(
			'&amp;'=>'&#038;'
		);

		foreach ($html_to_replace as $org=>$new) {
			$xml = str_replace($org,$new,$xml);
		}

		return $xml;
	}
}