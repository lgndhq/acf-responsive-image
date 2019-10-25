<?php

/*
Plugin Name: Advanced Custom Fields: Responsive_Image
Plugin URI: https://github.com/lgndhq/acf-responsive-image
Description: Plugin to add responsive images with developer-specified sizes to ACF
Version: 1.0.0
Author: Ryan McCahan <ryan@lgnd.com>
Author URI: https://github.com/mccahan
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('lgnd_acf_plugin_responsive_image') ) :

class lgnd_acf_plugin_responsive_image {

	// vars
	var $settings;

	function __construct() {
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);

		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field')); // v4
		add_action('acf/update_value/type=responsive_image', function($value, $post, $field) {
			global $_wp_additional_image_sizes;
			$previousSizes = $_wp_additional_image_sizes;
			$uploadDir = wp_upload_dir();
			$metadata = wp_get_attachment_metadata($value);
			$file = $uploadDir['basedir'] . '/' . $metadata['file'];

			foreach (explode("\n", $field['image_sizes']) as $sizeLine) {
				list($width, $tag) = preg_split("/\s+/", $sizeLine);
				$_wp_additional_image_sizes[$field['key'] . '-' . $tag] = ['width' => $width, 'height' => 9999, 'crop' => false];
			}

			// The wp_generate_attachment_metadata function actually generates the missing sizes
			wp_update_attachment_metadata($value, wp_generate_attachment_metadata($value, $file));
			$_wp_additional_image_sizes = $previousSizes;
			return $value;
		}, 15, 3);
	}

	function include_field( $version = false ) {
		// support empty $version
		if( !$version ) $version = 4;
		if ($version != 5) throw new \Exception("Sorry, this plugin only supports ACF v5");


		// load textdomain
		load_plugin_textdomain( 'lgndacf', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );

		// include
		include_once('fields/class-lgnd-acf-field-responsive-image-v' . $version . '.php');
	}
}


// initialize
new lgnd_acf_plugin_responsive_image();


// class_exists check
endif;
