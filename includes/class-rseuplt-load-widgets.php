<?php

namespace RSEUPLTemplate;

if( ! defined( 'ABSPATH' ) ) exit();

class RSEUPLT_Load_Widgets {

	private static $instance = null;

	/**
	 * Initialize integration hooks
	 *
	 * @return void
	 */
	public function __construct() {
		// load CSS files
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'rseuplt_required_assets' ) );

		// load Elementor Widgets (from ./widgets/* dir)
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'rseuplt_widget_register' ) );

		// load JS files
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'rseuplt_register_scripts' ) );
	}

	/**
	 * Enqueue required CSS files
	 * @since 1.0.0
	 * @access public
	 */
	public function rseuplt_required_assets() {
		wp_enqueue_style(
			RS_EUPLT_TEXTDOMAIN . '-css', // readyship-elementor-ultimate-post-list-template
			RS_EUPLT_URL . 'assets/css/rseuplt.css',
			array(),
			RS_EUPLT_VERSION,
			'all'
		);
	}

	/** Require widgets files
	 * @since 1.0.0
	 * @access private
	 */
	public function rseuplt_widget_register() {
		foreach ( glob( RS_EUPLT_PATH . 'widgets/' . '*.php' ) as $file ) {
			$this->register_addon( $file );
		}
	}

	/** Register required JS files
	 * @since 1.0.0
	 * @access public
	 */
	public function rseuplt_register_scripts() {
		wp_register_script(RS_EUPLT_TEXTDOMAIN . '-js', RS_EUPLT_URL . 'assets/js/rseuplt.js', array('jquery', 'masonry'), RS_EUPLT_VERSION, true);
	}

	/**
	 * Registers widgets by file name
	 *
	 * @param  string $file File name.
	 * @return void
	 */
	public function register_addon( $file ) {

		$base  = basename( str_replace( '.php', '', $file ) );
		$class = ucwords( str_replace( '-', ' ', $base ) );
		$class = str_replace( ' ', '_', $class );
		$class = sprintf( 'Elementor\%s', $class );

		require $file; // load widget class Elementor\Rseuplt_XXX

		require_once ( RS_EUPLT_PATH . 'query-functions.php' );

		if ( class_exists( $class ) ) {
			\Elementor\PLUGIN::instance()->widgets_manager->register_widget_type( new $class );
		}
	}

	/**
	 * Creates and returns an instance of the class
	 * @since 1.0.0
	 * @access public
	 * return object
	 */
	public static function get_instance(){
		if( self::$instance == null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}


if ( ! function_exists( 'rseuplt_enqueue_assets' ) ) {

	/**
	 * Returns an instance of the plugin class.
	 * @since  1.0.0
	 * @return object
	 */
	function rseuplt_load_widgets() {
		return RSEUPLT_Load_Widgets::get_instance();
	}
}
rseuplt_load_widgets();