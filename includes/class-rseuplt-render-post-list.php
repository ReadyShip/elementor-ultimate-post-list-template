<?php

namespace RSEUPLTemplate;

use Aws\CloudFront\Exception\Exception;
use Twig_Loader_Array;
use Twig_Environment;
use DOMDocument;
use DomXPath;

if (!defined('ABSPATH')) {
    exit();
}

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
    public $posts = [];
    public $paged = 1;
    public $offset = 0;
    public $post_per_page = 10;
    public $wpQuery = null;
    public $options = null;
    public $paginationHtml = '';
    public $resultsCountHtml = '';

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
        $options       = apply_filters('rseuplt_postlist_options', $options);
        $this->options = $options;
        foreach (
          [
            'template_post_id',
            'get_posts_args',
            'gutter_padding',
            'rseuplt_offset',
            'paged',
            'rseuplt_posts_per_page',
            'rseuplt_post_paging'
          ] as $key
        ) {
            $this->{$key} = $options[$key];
        }
        $this->offset        = $options['rseuplt_offset'];
        $this->post_per_page = $options['rseuplt_posts_per_page'];
        $this->masonry       = ($options['masonry'] === true || $options['masonry'] === 'true');

        // if Timber plugin is activated, then use it
        if ($options['rendering_mode'] === 'timber' && class_exists('Timber') && defined('TIMBER_LOADED')) {
            $this->rendering_mode = $options['rendering_mode'];
        }

        $this->list_item_class     = is_array($options['list_item_class']) ? implode(' ', $options['list_item_class']) : $options['list_item_class'];
        $this->list_item_is_linked = (bool) ($options['list_item_is_linked'] === true || $options['list_item_is_linked'] === 'true');
        // $this->columns = $options['columns'];
    }

    public function render() {
        $this->wpQuery = new \WP_Query;
        $templateHtml  = $this::generateTemplateHtml($this->template_post_id);
        if (!$templateHtml) {
            return false; // Post not found or empty
        }
        $query = $_GET['query'];
        $query = apply_filters('rseuplt_postlist_query', $query);
        $this->posts     = $this->wpQuery->query(array_merge($this->get_posts_args, $query ?: []));
        $output          = '';
        $additionalStyle = '';

        // Set Elementor as Twig template
        if ($this->rendering_mode !== 'timber') {
            $twigInstance = new \Twig_Environment(new \Twig_Loader_Array(array('elementor_post_list' => $templateHtml)));
        } else {
            $twigInstance = null;
        }

        $loop_index = 0;
        // replace all the post object list
        foreach ($this->posts as $post) {
            $rendered        = $this::renderPostWithTemplate($post, $templateHtml, $this->rendering_mode, $this->list_item_is_linked, $this->list_item_class, $twigInstance, $loop_index);
            $output          .= $rendered['html'];
            $additionalStyle .= $rendered['css'];
            $loop_index ++;
        }

        $wrappetTagAttr = $this->masonry ? " data-rseuplt-masonry='true' " : " style='display: flex; flex-wrap: wrap;' ";
        $wrapperTags    = apply_filters('rseuplt_postlist_list_wrap_tags', array(
          "<div class='rseuplt-post-list-wrapper elementor-clearfix' $wrappetTagAttr>",
          '</div>'
        ));
        // Filter for customizing
        $additionalStyle = apply_filters('rseuplt_postlist_after_render_css', $additionalStyle);

        $this->generatePaginationLinks();
        // Filter for customizing
        $res = apply_filters('rseuplt_postlist_after_render_all',
          '<style>' . $additionalStyle . '</style>'
          . $this->resultsCountHtml
          . $wrapperTags[0]
          . $output
          . $wrapperTags[1]
          . $this->paginationHtml
        );

        wp_reset_postdata();

        return $res;
    }

    public static function renderPostWithTemplate($post, $templateHtml, $rendering_mode = 'twig', $list_item_is_linked = false, $list_item_class = '', $twigInstance = null, $loop_index = false, $isPostObject = true) {
        // set tags if template references
        $additionalStyle = '';
        if ($isPostObject && strpos($templateHtml, 'post.categories') !== false) {
            $post->categories = wp_get_post_categories($post->ID, array('fields' => 'all'));
        }

        // set tags if template references
        if ($isPostObject && strpos($templateHtml, 'post.tags') !== false) {
            $post->tags = wp_get_post_tags($post->ID, array('fields' => 'all'));
        }


        // set custom_taxonomies if template refer
        if ($isPostObject && strpos($templateHtml, 'post.taxonomies.') && $taxs = preg_match_all('post\.custom_taxonomies\.(.+)?\.', $templateHtml)) {
            $terms            = wp_get_post_terms($post->ID, $taxs, array('fields' => 'all'));
            $post->taxonomies = array();
            foreach ($terms as $t) {
                $post->taxonomies[$t->taxonomy] = $t;
            }
        }


        // Filter for customizing
        $listTagAdditionalAttr = '';
        if ($list_item_is_linked) {
            $list_item_class       .= ' rseuplt-post-list-linking';
            $listTagAdditionalAttr = ' data-href="#post_permalink"';
        }

        $post = apply_filters('rseuplt_postlist_before_render_single_postdata', $post);

        $twigVariables = apply_filters('rseuplt_postlist_set_twig_variables', array(
          'post'       => $post,
          'authordata' => $post->author,
          'index'      => $loop_index
        ));

        try {
            if ($rendering_mode === 'timber') {
                $tmp_output = \Timber::compile_string($templateHtml, $twigVariables);
            } else {
                // default: twig
                if ($twigInstance === null) {
                    $twigInstance = new \Twig_Environment(new \Twig_Loader_Array(array('elementor_post_list' => $templateHtml)));
                }
                $tmp_output = $twigInstance->render('elementor_post_list', $twigVariables);
            }
        } catch (\Twig_Error_Loader $e) {
            // TODO: Error logging
            echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Loader $e: ' . $e->getMessage() . '")</script>';
        } catch (\Twig_Error_Runtime $e) {
            echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Runtime $e: ' . $e->getMessage() . '")</script>';
        } catch (\Twig_Error_Syntax $e) {
            echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Twig_Error_Syntax $e: ' . $e->getMessage() . '")</script>';
        } catch (\Exception $e) {
            echo '<script>console.error("[' . RS_EUPLT_FULLNAME . '] PHP Error on Exception $e: ' . $e->getMessage() . '")</script>';
        }

        $listTags   = apply_filters('rseuplt_postlist_list_single_tags', array(
          "<div class='rseuplt-post-list-container {$list_item_class}' $listTagAdditionalAttr>",
          '</div>'
        ));
        $tmp_output = $listTags[0] . $tmp_output . $listTags[1];
        $categories = get_the_category($post->ID);
        $replaces   = array(
          '#post_permalink'          => get_permalink($post->ID),
          '_rs_el_post_thumbmail_bg' => '_rs_el_post_thumbmail_bg _rs_el_post_thumbmail_bg--' . $post->ID,
          '#category_link'           => get_category_link($categories[0]->cat_ID) // TODO: termlink
            // TODO: thumbnail image
        );

        // Replace links etc
        $tmp_output = str_replace(array_keys($replaces), array_values($replaces), $tmp_output);

        if (strpos($templateHtml, '_rs_el_post_thumbmail_bg') !== false) {
            // col or section or normal widget
            $imgUrl = get_the_post_thumbnail_url($post->ID, 'large');
            if ($imgUrl) {
                $additionalStyle = '._rs_el_post_thumbmail_bg--' . $post->ID . ', ._rs_el_post_thumbmail_bg--' . $post->ID . ' > .elementor-element-populated,._rs_el_post_thumbmail_bg--' . $post->ID . ' > .elementor-widget-container{background-image:url(' . get_the_post_thumbnail_url($post->ID, 'large') . ') !important;}';
            }
        }

        // Filter for customizing
        $tmp_output = apply_filters('rseuplt_postlist_after_render_single', $tmp_output);

        return ['html' => $tmp_output, 'css' => $additionalStyle];
    }

    public static function generateTemplateHtml($template_post_id) {
        $cacheKey = 'rseuplt_template_html_' . $template_post_id;
        $res      = wp_cache_get($cacheKey);
        if ($res !== false) {
            return $res;
        }
        $templateHtml = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_post_id);
        if (!$templateHtml) {
            return false; // Post not found or empty
        }

        // handle template html
        // TODO: object cache or DB cache
        $templateHtmlDom = new DOMDocument;
        $templateHtmlDom->loadHTML(mb_convert_encoding($templateHtml, 'HTML-ENTITIES', 'UTF-8'));
        $finder = new DomXPath($templateHtmlDom);

        // wrap with text (twig)
        // {% for tax in post.taxonomy_name %}
        // {% endfor %}​
        $iterateTaxos = [
          'categories' => ['category'],
          'tags'       => ['tag']
        ];
        $new_div      = $templateHtmlDom->createElement('div');
        foreach ($iterateTaxos as $label => $ta) {
            $tagContainers = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' __foreach_$label ')]");
            if ($tagContainers->length > 0) {
                foreach ($tagContainers as $tc) {
                    //Clone our created div
                    $new_div_clone = $new_div->cloneNode();
                    $new_div_clone->appendChild($templateHtmlDom->createTextNode("\n {% for $ta[0] in post.$label %} \n"));
                    //Replace image with this wrapper div
                    $tc->parentNode->replaceChild($new_div_clone, $tc);
                    $tc->setAttribute('style', 'margin-bottom: 0 !important;'. $tc->getAttribute('style'));
                    //Append this image to wrapper div
                    $new_div_clone->appendChild($tc);
                    $new_div_clone->appendChild($templateHtmlDom->createTextNode("\n {% endfor %} \n "));
                }
            }
        }

        $templateHtml = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array(
          '<html>',
          '</html>',
          '<body>',
          '</body>'
        ), array('', '', '', ''), $templateHtmlDom->saveHTML()));
        wp_cache_set($cacheKey, $templateHtml);

        return $templateHtml;
    }

    public function generatePaginationLinks() {
        if ($this->options['rseuplt_post_paging'] !== 'yes') {
            return '';
        }
        $count_posts = $this->wpQuery->found_posts;

        $page_tot  = ceil(($count_posts - $this->offset) / $this->post_per_page);
        $big       = 999999999;
        $linksHtml = paginate_links(array(
          'base'      => str_replace($big, '%#%', get_pagenum_link(999999999, false)),
          'format'    => '?paged=%#%',
          'current'   => max(1, $this->paged),
          'total'     => $page_tot,
          'prev_next' => true,
          'prev_text' => $this->options['rseuplt_post_paging_text_prev'],
          'next_text' => $this->options['rseuplt_post_paging_text_next'],
          'end_size'  => 1,
          'mid_size'  => 3,
          'type'      => 'list'
        ));

        $this->paginationHtml   = '<div class="rseuplt-post-pagination-container">' . $linksHtml . '</div>';
        $this->resultsCountHtml =
          '<div class="rseuplt-post-results">'
          . sprintf(
            $this->options['rseuplt_post_results_text'] ?: __('Showing %2$d - %3$d of %1$d', RS_EUPLT_TEXTDOMAIN),
            $count_posts,
            min(($this->paged - 1) * $this->post_per_page + 1, $count_posts),
            min(($this->paged * $this->post_per_page), $count_posts),
            min($this->post_per_page, $count_posts)
          )
          . '</div>';
    }

    public static function renderWithTemplateIdAndGlobalPostObject($templateId, $rendering_mode = 'twig', $list_item_is_linked = false, $list_item_class = '', $twigInstance = null, $loop_index = false, $isPostObject = true) {
        global $post;
        $templateHtml = \RSEUPLTemplate\RSEUPLT_Render_Post_List::generateTemplateHtml($templateId);
        $rendered = \RSEUPLTemplate\RSEUPLT_Render_Post_List::renderPostWithTemplate($post, $templateHtml);
        return '<style>' . $rendered['css'] . '</style>' . $rendered['html'];
    }

    /**
     * Creates and returns an instance of the class
     * @since 1.0.0
     * @access public
     * return object
     */
    public static function get_instance($options) {
        self::$instance = new self($options);

        return self::$instance;
    }
}
