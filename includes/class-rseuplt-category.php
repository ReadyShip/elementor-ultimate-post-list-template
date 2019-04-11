<?php

namespace RSEUPLTemplate;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class RSEUPLT_Category {

	private static $instance = null;

	public function __construct() {
		\Elementor\Plugin::instance()->elements_manager->add_category(
			RS_EUPLT_TEXTDOMAIN,
			array( 'title' => 'RS Elementor Ultimate Post List Template' ),
			1 );
	}

	/**
	 * Creates and returns an instance of the class
	 * @since  2.6.8
	 * @access public
	 * return object
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}


if ( ! function_exists( 'rseuplt_category' ) ) {

	/**
	 * Returns an instance of the plugin class.
	 * @since  2.6.8
	 * @return object
	 */
	function rseuplt_category() {
		return RSEUPLT_Category::get_instance();
	}
}
rseuplt_category();