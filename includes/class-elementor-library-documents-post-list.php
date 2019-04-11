<?php
namespace Elementor\Modules\Library\Documents;

use Elementor\Core\DocumentTypes\Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor page library document.
 *
 * Elementor page library document handler class is responsible for
 * handling a document of a page type.
 *
 * @since 2.0.0
 */
class RSEUPostListTemplate extends Library_Document {

	/**
	 * Get document properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['library_view'] = 'list';
		$properties['group'] = 'blocks';
		return $properties;
	}

	/**
	 * Get document name.
	 *
	 * Retrieve the document name.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return string Document name.
	 */
	public function get_name() {
		return 'rseuplt';
	}

	/**
	 * Get document title.
	 *
	 * Retrieve the document title.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return string Document title.
	 */
	public static function get_title() {
		return __( 'Ultimate Post List Template', 'elementor' );
	}
}
