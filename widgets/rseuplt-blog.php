<?php

namespace Elementor;
require_once RS_EUPLT_PATH . 'includes/class-rseuplt-render-post-list.php';

use Elementor\Core\Responsive\Responsive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If this file is called directly, abort.

class Rseuplt_Blog extends Widget_Base {
	public function get_name() {
		return 'readyship-elementor-ultimate-post-list-template';
	}

	public function get_title() {
		return 'Ultimate Post List with Elementor Template';
	}

	public function is_reload_preview_required() {
		return true;
	}

	public function get_script_depends() {
		return [
			'isotope-js',
			RS_EUPLT_TEXTDOMAIN . '-js'
		];
	}

	public function get_icon() {
		return 'eicon-posts-masonry';
	}

	public function get_categories() {
		return [ RS_EUPLT_TEXTDOMAIN ];
	}

	protected function _register_controls() {

		/* Start Layout Settings Section */
		$this->start_controls_section( 'rseuplt_post_lauout_settings',
			[
				'label' => esc_html__( 'Layout', RS_EUPLT_TEXTDOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT
			]
		);

		/* Select Template Post */
		$this->add_control( 'rseuplt_template_post_id',
			[
				'label'    => __( 'List Item Template ID *', RS_EUPLT_TEXTDOMAIN ),
				'description' => esc_html__( 'Please Put Your Elementor Template ID', 'readyship-elementor-ultimate-post-list-template' ),
				'type'     => Controls_Manager::NUMBER,
				'default'  => '',
				// 'options'  => rseuplt_get_post_template_ids(), // TODO: Implement Post List Template Editing
			]
		);

		/* Rendering with Timber */
		if(class_exists('Timber')) {
			$this->add_control( 'rseuplt_timber_enabled',
				[
					'label'       => esc_html__( 'Rendering with Timber', 'readyship-elementor-ultimate-post-list-template' ),
					'type'        => Controls_Manager::SWITCHER,
					'description' => esc_html__( 'Replace rendering strategy with using Timber plugin', 'readyship-elementor-ultimate-post-list-template' ),
					'return_value' => 'yes',
					'default'     => 'yes'
				]
			);
		}

		/* Link to the Whole Element */
		$this->add_control( 'rseuplt_list_item_is_linked',
			[
				'label'       => esc_html__( 'Add Link?', 'readyship-elementor-ultimate-post-list-template' ),
				'type'        => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-list-container > *' => 'cursor:pointer;',
					'{{WRAPPER}} .rseuplt-post-list-container > *:hover' => 'opacity:0.8;',
				],
				'default'     => 'true'
			]
		);

		/*Grid Number of Columns*/
		$this->add_responsive_control(
			'rseuplt_columns_number',
			[
				'label' => __( 'Number of Columns', 'readyship-elementor-ultimate-post-list-template' ),
				'type' => Controls_Manager::SELECT,
				'options'   => [
					'100%'    => '1',
					'50%'    => '2',
					'33.33%' => '3',
					'25%'    => '4',
					'20%'    => '5',
					'16.66%'    => '6',
					'14.28%'    => '7',
					'12.5%'    => '8',
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => '33.33%',
				'tablet_default' => '50%',
				'mobile_default' => '100%',
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-list-container' => 'width: {{VALUE}}; float:left;',
				],
				'return_value' => 'true'
			]
		);

		/*Grid Spacing*/
		$this->add_responsive_control( 'rseuplt_post_spacing',
			[
				'label'      => esc_html__( 'Spacing (Item Padding)', 'readyship-elementor-ultimate-post-list-template' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', "em" ],
				'default'      => [
					'top' => 15,
					'right' => 15,
					'bottom' => 15,
					'left' => 15,
					'unit' => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .rseuplt-post-list-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		/*Masonry*/
		$this->add_control( 'rseuplt_post_masonry',
			[
				'label'        => esc_html__( 'Masonry', 'readyship-elementor-ultimate-post-list-template' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true'
			]
		);


		/* End Content Section */
		$this->end_controls_section();


		/* Start Query Settings Section */
		$this->start_controls_section( 'rseuplt_post_query_settings',
			[
				'label' => esc_html__( 'Query', RS_EUPLT_TEXTDOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT
			]
		);

		/* Post Type Filter */
		$this->add_control( 'rseuplt_post_types',
			[
				'label'    => __( 'Post Types', RS_EUPLT_TEXTDOMAIN ),
				'type'     => Controls_Manager::SELECT2,
				'default'  => 'post',
				'options'  => rseuplt_get_all_post_type_options(),
				'multiple' => true
			]
		);

		/*Categories Filter*/
		$this->add_control( 'rseuplt_tax_query',
			[
				'label'       => esc_html__( 'Categories', RS_EUPLT_TEXTDOMAIN ),
				'type'        => Controls_Manager::SELECT2,
				'description' => esc_html__( 'Select the categories you want to show', RS_EUPLT_TEXTDOMAIN ),
				'label_block' => true,
				'multiple'    => true,
				'options'     => rseuplt_get_all_taxonomy_options(),
			]
		);

		/*Number of Posts*/
		$this->add_control( 'rseuplt_posts_per_page',
			[
				'label'       => esc_html__( 'Posts Per Page', 'readyship-elementor-ultimate-post-list-template' ),
				'description' => esc_html__( 'Choose how many posts do you want to be displayed per page', 'readyship-elementor-ultimate-post-list-template' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'default'     => 6,
			]
		);

		/*Posts Offset*/
		$this->add_control( 'rseuplt_offset',
			[
				'label'       => esc_html__( 'Offset Count', 'readyship-elementor-ultimate-post-list-template' ),
				'description' => esc_html__( 'The index of post to start with', 'readyship-elementor-ultimate-post-list-template' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '0',
				'min'         => '0',
			]
		);

		$this->add_control(
			'rseuplt_orderby',
			[
				'label' => __('Order By', 'readyship-elementor-ultimate-post-list-template'),
				'type' => Controls_Manager::SELECT,
				'options' => array(
					'none' => __('No order', 'readyship-elementor-ultimate-post-list-template'),
					'ID' => __('Post ID', 'readyship-elementor-ultimate-post-list-template'),
					'author' => __('Author', 'readyship-elementor-ultimate-post-list-template'),
					'title' => __('Title', 'readyship-elementor-ultimate-post-list-template'),
					'date' => __('Published date', 'readyship-elementor-ultimate-post-list-template'),
					'modified' => __('Modified date', 'readyship-elementor-ultimate-post-list-template'),
					'parent' => __('By parent', 'readyship-elementor-ultimate-post-list-template'),
					'rand' => __('Random order', 'readyship-elementor-ultimate-post-list-template'),
					'comment_count' => __('Comment count', 'readyship-elementor-ultimate-post-list-template'),
					'menu_order' => __('Menu order', 'readyship-elementor-ultimate-post-list-template'),
					'post__in' => __('By include order', 'readyship-elementor-ultimate-post-list-template'),
				),
				'default' => 'date',
			]
		);

		$this->add_control(
			'rseuplt_order',
			[
				'label' => __('Order', 'readyship-elementor-ultimate-post-list-template'),
				'type' => Controls_Manager::SELECT,
				'options' => array(
					'ASC' => __('Ascending', 'readyship-elementor-ultimate-post-list-template'),
					'DESC' => __('Descending', 'readyship-elementor-ultimate-post-list-template'),
				),
				'default' => 'DESC',
			]
		);

		$this->end_controls_section();


		/*Pagination Style*/
		$this->start_controls_section( 'rseuplt_post_pagination_Style',
			[
				'label'     => esc_html__( 'Pagination', 'readyship-elementor-ultimate-post-list-template' )
			] );
		/*Pagination*/
		$this->add_control( 'rseuplt_post_paging',
			[
				'label'       => esc_html__( 'Pagination', 'readyship-elementor-ultimate-post-list-template' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Pagination is the process of dividing the posts into discrete pages', 'readyship-elementor-ultimate-post-list-template' ),
			]
		);

		$this->start_controls_tabs( 'rseuplt_post_pagination_colors');

		$this->start_controls_tab( 'rseuplt_post_pagination_nomral',
			[
				'label' => esc_html__( 'Normal', 'readyship-elementor-ultimate-post-list-template' ),
			] );

		$this->add_control( 'prmeium_blog_pagination_color',
			[
				'label'     => esc_html__( 'Color', 'readyship-elementor-ultimate-post-list-template' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-pagination-container li a, {{WRAPPER}} .rseuplt-post-pagination-container li span' => 'color: {{VALUE}};'
				]
			] );

		$this->add_control( 'prmeium_blog_pagination_back_color',
			[
				'label'     => esc_html__( 'Background Color', 'readyship-elementor-ultimate-post-list-template' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-pagination-container li a, {{WRAPPER}} .rseuplt-post-pagination-container li span' => 'background-color: {{VALUE}};'
				]
			] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'rseuplt_post_pagination_hover',
			[
				'label' => esc_html__( 'Hover', 'readyship-elementor-ultimate-post-list-template' ),

			] );

		$this->add_control( 'prmeium_blog_pagination_hover_color',
			[
				'label'     => esc_html__( 'Color', 'readyship-elementor-ultimate-post-list-template' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-pagination-container li:hover a, {{WRAPPER}} .rseuplt-post-pagination-container li:hover span' => 'color: {{VALUE}};'
				]
			] );

		$this->add_control( 'prmeium_blog_pagination_back_hover_color',
			[
				'label'     => esc_html__( 'Background Color', 'readyship-elementor-ultimate-post-list-template' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .rseuplt-post-pagination-container li:hover a, {{WRAPPER}} .rseuplt-post-pagination-container li:hover span' => 'background-color: {{VALUE}};'
				]
			] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		/*End Paging Style Section*/
		$this->end_controls_section();

	}

	protected function render() {

		/**
		 * Render with Twig
		 * - [ ] カラム数制御
		 * - [ ] カラム毎 横padding制御 (Gutter Options)
		 * - [ ] Masonry
		 * - [ ] Slider  (Carousel)
		 * - [ ] get_pages 絞込 & 制御系
		 * - Post Types => 複数選択可能に
		 * - Taxonomies => post_tags:tricks みたいになっている方が良い (例: `addons-for-elementor:Post Grid`)
		 * - Order By
		 * - Order (DESC, ASC)
		 * - Post Per Page
		 * - Offset
		 * - [ ] Excerpt Length
		 * - [ ] 表示系: Author, Tag, Comment, etc
		 * - [ ] Pagination <= おー...
		 * - Hover Image Color Effect (不要かな?)
		 */

		$settings = $this->get_settings();

		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} else if ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
		$settings['paged'] = $paged;

		$get_posts_args = rseuplt_parse_get_posts_args_from_elementor_control($settings);
		$listRender = \RSEUPLTemplate\RSEUPLT_Render_Post_List::get_instance(array(
		    'template_post_id' => $settings['rseuplt_template_post_id'],
        'get_posts_args' => $get_posts_args,
				'masonry' => $settings['rseuplt_post_masonry'],
				'list_item_is_linked' => ($settings['rseuplt_list_item_is_linked'] === 'true'),
				'rendering_mode' => $settings['rseuplt_timber_enabled'] === 'yes' ? 'timber' : 'twig'
    ))->render();

		echo $listRender;

		$offset = $settings['rseuplt_offset'];
		$post_per_page = $settings['rseuplt_posts_per_page'];

		if ( $settings['rseuplt_post_paging'] === 'yes' ) : ?>
			<div class="rseuplt-post-pagination-container">
				<?php
				$count_posts     = wp_count_posts();
				$published_posts = $count_posts->publish;

				$page_tot = ceil( ( $published_posts - $offset ) / $post_per_page );
				if ( $page_tot > 1 ) {
					$big = 999999999;
					echo paginate_links( array(
						'base'      => str_replace( $big, '%#%', get_pagenum_link( 999999999, false ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, $paged ),
						'total'     => $page_tot,
						'prev_next' => true,
						'prev_text' => esc_html__( "&lsaquo; Previous" ),
						'next_text' => esc_html__( "Next &rsaquo;" ),
						'end_size'  => 1,
						'mid_size'  => 2,
						'type'      => 'list'
					) );
				}
				?>
			</div>
		<?php
		endif;
	}
}

