<?php

namespace Elementor;
require_once RS_EUPLT_PATH . 'includes/class-rseuplt-render-post-list.php';

use Elementor\Core\Responsive\Responsive;

if (!defined('ABSPATH')) {
    exit;
} // If this file is called directly, abort.

class Rseuplt_Blog extends Widget_Base {
    public function get_name() {
        return RS_EUPLT_TEXTDOMAIN;
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
        return [RS_EUPLT_TEXTDOMAIN];
    }

    protected function _register_controls() {

        /* Start Layout Settings Section */
        $this->start_controls_section('rseuplt_post_lauout_settings',
          [
            'label' => esc_html__('Layout', RS_EUPLT_TEXTDOMAIN),
            'tab'   => Controls_Manager::TAB_CONTENT
          ]
        );

        /* Select Template Post */
        $this->add_control('rseuplt_template_post_selected_id',
          [
            'label'       => __('Select Template', RS_EUPLT_TEXTDOMAIN),
            'description' => esc_html__('Please Select List Template', RS_EUPLT_TEXTDOMAIN) . '&nbsp;&nbsp;<a href="' . admin_url('edit.php?post_type=elementor_library&tabs_group=library&elementor_library_type=rseuplt') . '" target="_blank">Manage > </a>',
            'type'        => Controls_Manager::SELECT,
            'default'     => '',
            'options'     => rseuplt_get_post_template_ids()
          ]
        );

        /* Select Template Post */
        $this->add_control('rseuplt_template_post_id',
          [
            'label'       => __('Or put Post ID', RS_EUPLT_TEXTDOMAIN),
            'description' => esc_html__('Or you can specify Post ID directly', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::NUMBER,
            'default'     => '',
            'conditions'  => [
              'terms' => [
                [
                  'name'  => 'rseuplt_template_post_selected_id',
                  'value' => '',
                ],
              ]
            ]
          ]
        );

        /* Rendering with Timber */
        if (class_exists('Timber')) {
            $this->add_control('rseuplt_timber_enabled',
              [
                'label'        => esc_html__('Rendering with Timber', RS_EUPLT_TEXTDOMAIN),
                'type'         => Controls_Manager::SWITCHER,
                'description'  => esc_html__('Replace rendering strategy with using Timber plugin', RS_EUPLT_TEXTDOMAIN),
                'return_value' => 'yes',
                'default'      => 'yes'
              ]
            );
        }

        /* Link to the Whole Element */
        $this->add_control('rseuplt_list_item_is_linked',
          [
            'label'        => esc_html__('Add Link?', RS_EUPLT_TEXTDOMAIN),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'true',
            'selectors'    => [
              '{{WRAPPER}} .rseuplt-post-list-container > *'       => 'cursor:pointer;',
              '{{WRAPPER}} .rseuplt-post-list-container > *:hover' => 'opacity:0.8;',
            ],
            'default'      => 'true'
          ]
        );

        /*Grid Number of Columns*/
        $this->add_responsive_control(
          'rseuplt_columns_number',
          [
            'label'           => __('Number of Columns', RS_EUPLT_TEXTDOMAIN),
            'type'            => Controls_Manager::SELECT,
            'options'         => [
              '100%'   => '1',
              '50%'    => '2',
              '33.33%' => '3',
              '25%'    => '4',
              '20%'    => '5',
              '16.66%' => '6',
              '14.28%' => '7',
              '12.5%'  => '8',
            ],
            'devices'         => ['desktop', 'tablet', 'mobile'],
            'desktop_default' => '33.33%',
            'tablet_default'  => '50%',
            'mobile_default'  => '100%',
            'selectors'       => [
              '{{WRAPPER}} .rseuplt-post-list-container' => 'width: {{VALUE}}; float:left;',
            ],
            'return_value'    => 'true'
          ]
        );

        /*Grid Spacing*/
        $this->add_responsive_control('rseuplt_post_spacing',
          [
            'label'      => esc_html__('Spacing (Item Padding)', RS_EUPLT_TEXTDOMAIN),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%', "em"],
            'default'    => [
              'top'      => 15,
              'right'    => 15,
              'bottom'   => 15,
              'left'     => 15,
              'unit'     => 'px',
              'isLinked' => false,
            ],
            'selectors'  => [
              '{{WRAPPER}} .rseuplt-post-list-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ]
          ]
        );

        /*Masonry*/
        $this->add_control('rseuplt_post_masonry',
          [
            'label'        => esc_html__('Masonry', RS_EUPLT_TEXTDOMAIN),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'true'
          ]
        );


        /* End Content Section */
        $this->end_controls_section();


        /* Start Query Settings Section */
        $this->start_controls_section('rseuplt_post_query_settings',
          [
            'label' => esc_html__('Query', RS_EUPLT_TEXTDOMAIN),
            'tab'   => Controls_Manager::TAB_CONTENT
          ]
        );

        /* Post Type Filter */
        $this->add_control('rseuplt_post_types',
          [
            'label'    => __('Post Types', RS_EUPLT_TEXTDOMAIN),
            'type'     => Controls_Manager::SELECT2,
            'default'  => 'post',
            'options'  => rseuplt_get_all_post_type_options(),
            'multiple' => true
          ]
        );

        /*Categories Filter*/
        $this->add_control('rseuplt_tax_query',
          [
            'label'       => esc_html__('Categories', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::SELECT2,
            'description' => esc_html__('Select the categories you want to show', RS_EUPLT_TEXTDOMAIN),
            'label_block' => true,
            'multiple'    => true,
            'options'     => rseuplt_get_all_taxonomy_options(),
          ]
        );

        /*Number of Posts*/
        $this->add_control('rseuplt_posts_per_page',
          [
            'label'       => esc_html__('Posts Per Page', RS_EUPLT_TEXTDOMAIN),
            'description' => esc_html__('Choose how many posts do you want to be displayed per page', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 1,
            'default'     => 6,
          ]
        );

        /*Posts Offset*/
        $this->add_control('rseuplt_offset',
          [
            'label'       => esc_html__('Offset Count', RS_EUPLT_TEXTDOMAIN),
            'description' => esc_html__('The index of post to start with', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::NUMBER,
            'default'     => '0',
            'min'         => '0',
          ]
        );

        $this->add_control(
          'rseuplt_orderby',
          [
            'label'   => __('Order By', RS_EUPLT_TEXTDOMAIN),
            'type'    => Controls_Manager::SELECT,
            'options' => array(
              'none'          => __('No order', RS_EUPLT_TEXTDOMAIN),
              'ID'            => __('Post ID', RS_EUPLT_TEXTDOMAIN),
              'author'        => __('Author', RS_EUPLT_TEXTDOMAIN),
              'title'         => __('Title', RS_EUPLT_TEXTDOMAIN),
              'date'          => __('Published date', RS_EUPLT_TEXTDOMAIN),
              'modified'      => __('Modified date', RS_EUPLT_TEXTDOMAIN),
              'parent'        => __('By parent', RS_EUPLT_TEXTDOMAIN),
              'rand'          => __('Random order', RS_EUPLT_TEXTDOMAIN),
              'comment_count' => __('Comment count', RS_EUPLT_TEXTDOMAIN),
              'menu_order'    => __('Menu order', RS_EUPLT_TEXTDOMAIN),
              'post__in'      => __('By include order', RS_EUPLT_TEXTDOMAIN),
            ),
            'default' => 'date',
          ]
        );

        $this->add_control(
          'rseuplt_order',
          [
            'label'   => __('Order', RS_EUPLT_TEXTDOMAIN),
            'type'    => Controls_Manager::SELECT,
            'options' => array(
              'ASC'  => __('Ascending', RS_EUPLT_TEXTDOMAIN),
              'DESC' => __('Descending', RS_EUPLT_TEXTDOMAIN),
            ),
            'default' => 'DESC',
          ]
        );

        $this->end_controls_section();


        /*Pagination Style*/
        $this->start_controls_section('rseuplt_post_pagination_Style',
          [
            'label' => esc_html__('Pagination', RS_EUPLT_TEXTDOMAIN)
          ]);

        $this->add_control('rseuplt_post_paging',
          [
            'label'       => esc_html__('Pagination', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::SWITCHER,
            'description' => esc_html__('Pagination is the process of dividing the posts into discrete pages', RS_EUPLT_TEXTDOMAIN),
          ]
        );

        $this->add_control('rseuplt_post_paging_text_next',
          [
            'label'       => esc_html__('Text for "Next > " link', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::TEXT,
            'default' => "Next &rsaquo;",
          ]
        );
        $this->add_control('rseuplt_post_paging_text_prev',
          [
            'label'       => esc_html__('Text for "Previous > " link', RS_EUPLT_TEXTDOMAIN),
            'type'        => Controls_Manager::TEXT,
            'default' => "&lsaquo; Previous",
          ]
        );

        $this->start_controls_tabs('rseuplt_post_pagination_colors');

        $this->start_controls_tab('rseuplt_post_pagination_nomral',
          [
            'label' => esc_html__('Normal', RS_EUPLT_TEXTDOMAIN),
          ]);

        $this->add_control('rseuplt_post_pagination_color',
          [
            'label'     => esc_html__('Color', RS_EUPLT_TEXTDOMAIN),
            'type'      => Controls_Manager::COLOR,
            'scheme'    => [
              'type'  => Scheme_Color::get_type(),
              'value' => Scheme_Color::COLOR_2,
            ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-pagination-container li a, {{WRAPPER}} .rseuplt-post-pagination-container li span' => 'color: {{VALUE}};'
            ]
          ]);

        $this->add_control('rseuplt_post_pagination_back_color',
          [
            'label'     => esc_html__('Background Color', RS_EUPLT_TEXTDOMAIN),
            'type'      => Controls_Manager::COLOR,
            'scheme'    => [
              'type'  => Scheme_Color::get_type(),
              'value' => Scheme_Color::COLOR_1,
            ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-pagination-container li a, {{WRAPPER}} .rseuplt-post-pagination-container li span' => 'background-color: {{VALUE}};'
            ]
          ]);

        $this->end_controls_tab();

        $this->start_controls_tab('rseuplt_post_pagination_hover',
          [
            'label' => esc_html__('Hover', RS_EUPLT_TEXTDOMAIN),

          ]);

        $this->add_control('rseuplt_post_pagination_hover_color',
          [
            'label'     => esc_html__('Color', RS_EUPLT_TEXTDOMAIN),
            'type'      => Controls_Manager::COLOR,
            'scheme'    => [
              'type'  => Scheme_Color::get_type(),
              'value' => Scheme_Color::COLOR_1,
            ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-pagination-container li:hover a, {{WRAPPER}} .rseuplt-post-pagination-container li span.page-numbers.current' => 'color: {{VALUE}};'
            ]
          ]);

        $this->add_control('rseuplt_post_pagination_back_hover_color',
          [
            'label'     => esc_html__('Background Color', RS_EUPLT_TEXTDOMAIN),
            'type'      => Controls_Manager::COLOR,
            'scheme'    => [
              'type'  => Scheme_Color::get_type(),
              'value' => Scheme_Color::COLOR_2,
            ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-pagination-container li:hover a, {{WRAPPER}} .rseuplt-post-pagination-container li span.page-numbers.current' => 'background-color: {{VALUE}};'
            ]
          ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        /*End Paging Style Section*/
        $this->end_controls_section();

        /* Results Text*/
        $this->start_controls_section('rseuplt_post_results',
          [
            'label' => esc_html__('Results Text', RS_EUPLT_TEXTDOMAIN),
            'conditions'  => [
              'terms' => [
                [
                  'name'  => 'rseuplt_post_paging',
                  'value' => 'yes',
                ],
              ]
            ]
          ]);
        $this->add_control(
          'rseuplt_post_results_text',
          [
            'label' => __( 'Title', 'elementor' ),
            'type' => Controls_Manager::TEXTAREA,
            'dynamic' => [
              'active' => true,
            ],
            'description' => __( '%1$d: Total count, %2$d: Current page\'s start offset, %3$d: Current page\'s end offset, %4$d: Current page\'s count', RS_EUPLT_TEXTDOMAIN ),
            'default' => __( 'Showing %2$d - %3$d of %1$d', RS_EUPLT_TEXTDOMAIN ),
          ]
        );
        $this->add_responsive_control(
          'rseuplt_post_results_text_align',
          [
            'label' => __( 'Alignment', 'elementor' ),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
              'left' => [
                'title' => __( 'Left', 'elementor' ),
                'icon' => 'fa fa-align-left',
              ],
              'center' => [
                'title' => __( 'Center', 'elementor' ),
                'icon' => 'fa fa-align-center',
              ],
              'right' => [
                'title' => __( 'Right', 'elementor' ),
                'icon' => 'fa fa-align-right',
              ],
              'justify' => [
                'title' => __( 'Justified', 'elementor' ),
                'icon' => 'fa fa-align-justify',
              ],
            ],
            'default' => '',
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-results' => 'text-align: {{VALUE}};',
            ],
          ]
        );

        $this->add_control(
          'rseuplt_post_results_text_color',
          [
            'label' => __( 'Text Color', 'elementor' ),
            'type' => Controls_Manager::COLOR,
            'scheme' => [
              'type' => Scheme_Color::get_type(),
              'value' => Scheme_Color::COLOR_1,
            ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-results' => 'color: {{VALUE}};',
            ],
          ]
        );

        $this->add_group_control(
          Group_Control_Typography::get_type(),
          [
            'name' => 'rseuplt_post_results_text_typography',
            'scheme' => Scheme_Typography::TYPOGRAPHY_1,
            'selector' => '{{WRAPPER}} .rseuplt-post-results',
          ]
        );

        $this->add_group_control(
          Group_Control_Text_Shadow::get_type(),
          [
            'name' => 'rseuplt_post_results_text_shadow',
            'selector' => '{{WRAPPER}} .rseuplt-post-results',
          ]
        );

        $this->add_responsive_control(
          'rseuplt_post_results_text_padding',
          [
            'label' => __( 'Padding', 'elementor' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors' => [
              '{{WRAPPER}} .rseuplt-post-results' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
          ]
        );

        /* End Results Text Section */
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

        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } else if (get_query_var('page')) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }
        $settings['paged'] = $paged;


        $get_posts_args = rseuplt_parse_get_posts_args_from_elementor_control($settings);
        $listRender     = \RSEUPLTemplate\RSEUPLT_Render_Post_List::get_instance(array_merge($settings, array(
          'template_post_id'    => $settings['rseuplt_template_post_selected_id'] ?: $settings['rseuplt_template_post_id'],
          'get_posts_args'      => $get_posts_args,
          'masonry'             => $settings['rseuplt_post_masonry'],
          'list_item_is_linked' => ($settings['rseuplt_list_item_is_linked'] === 'true'),
          'rendering_mode'      => $settings['rseuplt_timber_enabled'] === 'yes' ? 'timber' : 'twig'
        )))->render();

        echo $listRender;
    }
}

function rseuplt_get_post_template_ids() {
    // return
    $cacheKey = 'rseuplt_get_post_template_ids';
    $res = wp_cache_get($cacheKey);
    if ($res === false) {
        $posts = get_posts([
          'post_type'  => 'elementor_library',
          'meta_query' => [
            [
              'key'     => '_elementor_template_type',
              'value'   => 'rseuplt',
              'compare' => '=',
            ]
          ]
        ]);
        $res   = [ '' => '' ];
        foreach ($posts as $p) {
            $res[$p->ID] = $p->post_title . ' (ID: ' . $p->ID . ')';
        }
        wp_cache_set($cacheKey, $res);
    }

    return $res;
}

