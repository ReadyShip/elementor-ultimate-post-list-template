<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor column element.
 *
 * Elementor column handler class is responsible for initializing the column
 * element.
 *
 * @since 1.0.0
 */
class Element_Column_Extended extends Element_Column {

	/**
	 * Register column controls.
	 *
	 * Used to add new controls to the column element.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		parent::_register_controls();
		$this->start_controls_section(
			'section_extended',
			[
				'label' => __( 'Extended', 'elementor' ),
				'type'  => Controls_Manager::SECTION,
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'extended_link',
			[
				'label' => __( 'Link', 'plugin-domain' ),
				'type' => Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'plugin-domain' ),
				'show_external' => true,
				'default' => [
					'url' => 'https://readyship.co',
					'is_external' => true,
					'nofollow' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add column render attributes.
	 *
	 * Used to add attributes to the current column wrapper HTML tag.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function _add_render_attributes() {
		parent::_add_render_attributes();

		$settings = $this->get_settings();
		if ($settings['extended_link'] && $settings['extended_link']['url']) {
			$this->add_render_attribute( '_wrapper', 'data-col-link-url', $settings['extended_link']['url'] );
			$this->add_render_attribute( '_wrapper', 'class', ['elementor-column-extended--linked'] );
		}
	}
}
