<?php
/*
Plugin Name: ReadyShip Elementor Ultimate Post List Template
Description: Enables to Build Post List (Archive) Template with Elementor Page Builder. You can create completely flexble layouts. Twig integration included.
Plugin URI: https://readyship.co/
Version: 1.0.0
Author: ReadyShip
Author URI: http://readyship.co/opensource
Text Domain: readyship-elementor-ultimate-post-list-template
Domain Path: /languages
License: GNU General Public License v3.0
*/


/**
 * Checking if WordPress is installed
 */
if ( ! function_exists( 'add_action' ) ) {
	die( 'WordPress not Installed' ); // if WordPress not installed kill the page.
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // No access of directly access


define( 'RS_EUPLT_FULLNAME', 'ReadyShip Elementor Ultimate Post List Template' );
define( 'RS_EUPLT_VERSION', '1.0.0' );
define( 'RS_EUPLT_TEXTDOMAIN', 'readyship-elementor-ultimate-post-list-template' );
define( 'RS_EUPLT_URL', plugins_url( '/', __FILE__ ) );
define( 'RS_EUPLT_PATH', plugin_dir_path( __FILE__ ) );
define( 'RS_EUPLT_FILE', __FILE__ );
define( 'RS_EUPLT_BASENAME', plugin_basename( __FILE__ ) );
define( 'RS_EUPLT_STABLE_VERSION', '1.0.0' );

if ( ! class_exists( 'RS_Elementor_Ultimate_Post_List_Template' ) ) {
	/*
	* Intialize and Sets up the plugin
	*/

	class RS_Elementor_Ultimate_Post_List_Template {

		private static $instance = null;

		/**
		 * Sets up needed actions/filters for the plug-in to initialize.
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'rseulpt_elementor_setup' ) );

			add_action( 'elementor/init', array( $this, 'create_ultimate_post_list_template_category' ) );


			add_action('elementor/documents/register', function($docsManagerInstance) {
				require_once( RS_EUPLT_PATH . 'includes/class-elementor-library-documents-post-list.php' );
				$docsManagerInstance->register_document_type(
					'rseuplt',
					Elementor\Modules\Library\Documents\RSEUPostListTemplate::get_class_full_name()
				);
				return $docsManagerInstance;
			});

//			add_action( 'elementor/elements/elements_registered', function( ) {
//				require_once( RS_EUPLT_PATH . 'includes/column-override.php' );
//				\Elementor\Plugin::$instance->elements_manager->unregister_element_type('column');
//				\Elementor\Plugin::$instance->elements_manager->register_element_type(new \Elementor\Element_Column_Extended());
//			} );

			add_filter( 'template_include', [ $this, 'template_include' ], 11 ); // 11 = after WooCommerce.

//			 add_action( 'elementor/init', array( $this, 'register_elementor_template_type_post_list' ) );

			// Load Elementor Widgets
			add_action( 'init', array( $this, 'init_rseuplt_widgets' ), - 999 );

		}

		/**
		 * Installs translation text domain and checks if Elementor is installed
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function rseulpt_elementor_setup() {
			$this->load_domain();

			$this->init_files();
//			$this->register_elementor_template_type_post_list();
		}

		/**
		 * Require initial necessary files
		 * @since 2.6.8
		 * @access public
		 * @return void
		 */
		public function init_files() {
			if ( is_admin() ) {
				require_once( RS_EUPLT_PATH . 'plugin.php' ); // TODO: Currently do nothing, but file is existing.
			}
		}

		/**
		 * Load plugin translated strings using text domain
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function load_domain() {
			load_plugin_textdomain( RS_EUPLT_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Load libraries from ./vendor/autoload.php
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function load_vendor() {
			// Load Twig
			include_once RS_EUPLT_PATH . 'vendor/autoload.php';
		}

		/**
		 * Create RS Elementor Ultimate Post List category on Elementor Panel
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function create_ultimate_post_list_template_category() {
			require_once( RS_EUPLT_PATH . 'includes/class-rseuplt-category.php' );
		}

		/**
		 * Load Elementor widgets with CSS & JS
		 * @since 1.0.0
		 * @return void
		 */
		public function init_rseuplt_widgets() {
			$this->load_vendor();
			require_once( RS_EUPLT_PATH . 'includes/class-rseuplt-load-widgets.php' );
			if (is_admin()) {
				// Check updates
				$this->activate_autoupdate();
			}
		}

		public function register_elementor_template_type_post_list () {
//			var_dump(\Elementor\Plugin::$instance);
			if(is_admin()){
				// Load Elementor New Post Types - "Ultimate Post List Template"
				require_once( RS_EUPLT_PATH . 'includes/class-elementor-library-documents-post-list.php' );
			}
		}

		/**
		 * Creates and returns an instance of the class
		 * @since 1.0.0
		 * @access public
		 * return object
		 */
		public static function get_instance() {
			if ( self::$instance == null ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function template_include($template) {
			$location = '';
			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$location = 'archive';
			} elseif ( is_archive() || is_tax() || is_home() || is_search() ) {
				$location = 'archive';
			} elseif ( is_singular() || is_404() ) {
				$location = 'single';
			}
			return $template;
		}

		// Enable autoupdate with Github public repo https://github.com/ReadyShip/elementor-ultimate-post-list-template
		private function activate_autoupdate() {
			$plugin_slug = plugin_basename( __FILE__ );
			$gh_user = 'ReadyShip';
			$gh_repo = 'elementor-ultimate-post-list-template';
			new Miya\WP\GH_Auto_Updater( $plugin_slug, $gh_user, $gh_repo );
		}

	}
}

if ( ! function_exists( 'rseuplt_init' ) ) {
	/**
	 * Returns an instance of the plugin class.
	 * @since  1.0.0
	 * @return object
	 */
	function rseuplt_init() {
		return RS_Elementor_Ultimate_Post_List_Template::get_instance();
	}
}
rseuplt_init();