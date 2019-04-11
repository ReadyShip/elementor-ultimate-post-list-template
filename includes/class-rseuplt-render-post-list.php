<?php

namespace RSEUPLTemplate;

use Aws\CloudFront\Exception\Exception;
use Twig_Loader_Array;
use Twig_Environment;

if( ! defined( 'ABSPATH' ) ) exit();

/**
 * Shortcode [elementor_post_list_with_template template_post_id="111" numberposts="5"]
 *
 * [elementor_post_list_with_template template_post_id="111" numberposts="5" post_type=""]
 */
/*add_shortcode( 'elementor_post_list_with_template', 'elementor_post_list_with_template' );
function elementor_post_list_with_template( $attrs ) {
	$attrs  = shortcode_atts(
		array(
			'template_post_id' => '',
			'numberposts' => 5,
			'offset' => 0,
			'post_type' => 'post'
		),
		$attrs,
		'elementor_post_list_with_template'
	);

}*/


class RSEUPLT_Render_Post_List {

	private static $instance = null;

	public $template_post_id = null;
	public $get_posts_args = array('');
	public $rendering_mode = 'twig'; // twig or timber
	public $masonry = false;
	public $gutter_padding = '';
	public $list_item_class = '';
	public $list_item_is_linked = false;
	public $columns = null;

	/**
	 * Initialize integration hooks
	 *
	 * @return void
	 */
	public function __construct($options) {
		/*
		'template_post_id' => xxx,
		'get_posts_args' => array('post_type' => 'post', 'post_per_page' => 8, ...),
		'rendering_mode' => 'twig',
		'columns' => 3,
		'masonry' => false, // true or 'true' or another (false)
		'gutter_padding' => '15px',
		'list_item_class' => 'additional-class another-class', // can be an array
		'list_item_is_linked' => true, // bool
		 */
		$options = apply_filters('rseuplt_postlist_options', $options);

		$this->template_post_id = $options['template_post_id'];
		$this->get_posts_args = $options['get_posts_args'];

		$this->masonry = ($options['masonry'] === true || $options['masonry'] === 'true');

		// if Timber plugin is activated, then use it
		if($options['rendering_mode'] === 'timber' && class_exists('Timber') && defined('TIMBER_LOADED')) {
			$this->rendering_mode = $options['rendering_mode'];
		}
		$this->gutter_padding = $options['gutter_padding'];
		$this->list_item_class = is_array($options['list_item_class']) ? implode(' ', $options['list_item_class']) : $options['list_item_class'];
		$this->list_item_is_linked = (bool)($options['list_item_is_linked'] === true || $options['list_item_is_linked'] === 'true');
		// $this->columns = $options['columns'];

	}

	public function render() {
		$templatePost = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $this->template_post_id );
		$templateHtml = $templatePost;
		if(!$templateHtml) {
			return false; // Post not found or empty
		}
		$posts = get_posts($this->get_posts_args);
		$output = '';
		$additionalStyle = '';

		// Set Elementor as Twig template
		if($this->rendering_mode !== 'timber') {
			$twigInstance = new \Twig_Environment(new \Twig_Loader_Array(array('elementor_post_list' => $templateHtml)));
		} else {
			$twigInstance = null;
		}

		global $post, $authordata;

		$loop_index = 0;

		// replace all the post object list
		foreach ($posts as $post) {
			$loop_index++;
			setup_postdata( $post );

			// set tags if template references
			if (strpos($templateHtml, 'post.categories') !== false) {
				$post->categories = wp_get_post_categories( $post->ID, array('fields' => 'all') );
			}

			// set tags if template references
			if (strpos($templateHtml, 'post.tags') !== false) {
				$post->tags = wp_get_post_tags( $post->ID, array('fields' => 'all') );
			}


			// set custom_taxonomies if template refer
			if (strpos($templateHtml, 'post.taxonomies.') && $taxs = preg_match_all('post\.custom_taxonomies\.(.+)?\.', $templateHtml)) {
				$terms = wp_get_post_terms( $post->ID, $taxs, array('fields' => 'all')  );
				$post->taxonomies = array();
				foreach ($terms as $t) {
					$post->taxonomies[$t->taxonomy] = $t;
				}
			}



			// Filter for customizing
			$listTagAdditionalAttr = '';
			if ($this->list_item_is_linked) {
				$this->list_item_class .= ' rseuplt-post-list-linking';
				$listTagAdditionalAttr = ' data-href="#post_permalink"';
			}
			$listTags = array("<div class='rseuplt-post-list-container {$this->list_item_class}' $listTagAdditionalAttr>", '</div>');

			$post = apply_filters('rseuplt_postlist_before_render_single_postdata', $post);
			$listTags = apply_filters('rseuplt_postlist_list_single_tags', $listTags);
			$wrappetTagAttr = $this->masonry ? " data-rseuplt-masonry='true' " : " style='display: flex; flex-wrap: wrap;' ";
			$wrapperTags = apply_filters('rseuplt_postlist_list_wrap_tags', array(
				"<div class='rseuplt-post-list-wrapper elementor-clearfix' $wrappetTagAttr>",
				'</div>'
			));

			$twigVariables = apply_filters('rseuplt_postlist_set_twig_variables', array(
				'post' => $post,
				'authordata' => $authordata,
				'index' => $loop_index
			));

			try {
				if( $this->rendering_mode === 'timber' ) {
					$tmp_output = \Timber::compile_string( $templateHtml, $twigVariables );
				} else {
					// default: twig
					$tmp_output = $twigInstance->render( 'elementor_post_list', $twigVariables );
				}
			} catch ( \Twig_Error_Loader $e ) {
				// TODO: Error logging
				echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Loader $e: ' . $e->getMessage() . '")</script>';
			} catch ( \Twig_Error_Runtime $e ) {
				echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Runtime $e: ' . $e->getMessage() . '")</script>';
			} catch ( \Twig_Error_Syntax $e ) {
				echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Syntax $e: ' . $e->getMessage() . '")</script>';
			} catch (\Exception $e) {
			}

			$tmp_output = $listTags[0] . $tmp_output . $listTags[1];
			$categories = get_the_category($post->ID);
			$replaces = array(
				'#post_permalink' => get_permalink($post->ID),
				'_rs_el_post_thumbmail_bg' => '_rs_el_post_thumbmail_bg _rs_el_post_thumbmail_bg--'.$post->ID,
				'#category_link' => get_category_link($categories[0]->cat_ID) // TODO: termlink
				// TODO: thumbnail image
			);

			if (strpos($templateHtml, '{{categories.') !== false) {
				// TODO: add categories
			}
			if (strpos($templateHtml, '{{tags.') !== false) {
				// TODO: add categories
			}

			// Replace links etc
			$tmp_output = str_replace(array_keys($replaces), array_values($replaces), $tmp_output);

			if (strpos($templateHtml, '_rs_el_post_thumbmail_bg') !== false) {
				// col or section or normal widget
				$imgUrl = get_the_post_thumbnail_url($post->ID, 'large' );
				if ($imgUrl) {
					$additionalStyle .= '._rs_el_post_thumbmail_bg--'.$post->ID.', ._rs_el_post_thumbmail_bg--'.$post->ID.' > .elementor-element-populated,._rs_el_post_thumbmail_bg--'.$post->ID.' > .elementor-widget-container{background-image:url(' . get_the_post_thumbnail_url($post->ID, 'large' ) . ') !important;}';
				}
			}

			// Filter for customizing
			$tmp_output = apply_filters('rseuplt_postlist_after_render_single', $tmp_output);
			$output .= $tmp_output;
		}
		wp_reset_postdata();

		// Filter for customizing
		$additionalStyle = apply_filters('rseuplt_postlist_after_render_css', $additionalStyle);

		// Filter for customizing
		return apply_filters('rseuplt_postlist_after_render_all',
			'<style>' . $additionalStyle . '</style>'. $wrapperTags[0] . $output . $wrapperTags[1]
		);
	}

	/**
	 * Creates and returns an instance of the class
	 * @since 1.0.0
	 * @access public
	 * return object
	 */
	public static function get_instance($options){
		self::$instance = new self($options);
		return self::$instance;
	}
}
