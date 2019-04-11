<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



function rseuplt_get_all_post_type_options() {

	$post_types = get_post_types(array('public' => true), 'objects');

	$options = ['' => ''];

	foreach ($post_types as $post_type) {
		$options[$post_type->name] = $post_type->label;
	}

	return apply_filters('rseuplt_post_type_options', $options);
}

function rseuplt_parse_get_posts_args_from_elementor_control ($settings) {
	$query_args = [
		'orderby' => $settings['rseuplt_orderby'],
		'order' => $settings['rseuplt_order'],
		'ignore_sticky_posts' => 1,
		'post_status' => 'publish',
	];

	if (!empty($settings['rseuplt_post_in'])) {
		$query_args['post_type'] = 'any';
		$query_args['post__in'] = explode(',', $settings['rseuplt_post_in']);
		$query_args['post__in'] = array_map('intval', $query_args['post__in']);
	} else {
		if (!empty($settings['rseuplt_post_types'])) {
			$query_args['post_type'] = $settings['rseuplt_post_types'];
		}

		if (!empty($settings['rseuplt_tax_query'])) {
			$tax_queries = $settings['rseuplt_tax_query'];

			$query_args['tax_query'] = array();
			$query_args['tax_query']['relation'] = 'OR';

			// Related Post
			global $post;
			foreach ($tax_queries as $tq) {
				if (strpos($tq, 'related_by:') === 0) {
					if(!$post->ID) {
						continue;
					}
					list($d, $tax) = explode(':', $tq);
					$terms = get_the_terms( $post->ID, $tax );
					if ( empty( $terms ) ) {
						continue;
					}
					$term = wp_list_pluck( $terms, 'slug' );
				} else {
					list($tax, $term) = explode(':', $tq);
				}

				if (empty($tax) || empty($term))
					continue;
				$query_args['tax_query'][] = array(
					'taxonomy' => $tax,
					'field' => 'slug',
					'terms' => $term
				);
			}
		}
	}

	$query_args['posts_per_page'] = $settings['rseuplt_posts_per_page'];

	$query_args['paged'] = $settings['paged'];

	return apply_filters('rseuplt_posts_query_args', $query_args, $settings);
}


function rseuplt_get_all_taxonomy_options() {
	global $wpdb;

	$results = array();

	foreach ($wpdb->get_results("
		SELECT terms.slug AS 'slug', terms.name AS 'label', termtaxonomy.taxonomy AS 'type'
		FROM $wpdb->terms AS terms
		JOIN $wpdb->term_taxonomy AS termtaxonomy ON terms.term_id = termtaxonomy.term_id
	") as $result) {
		$results['related_by:' . $result->type] = 'related_by:' . $result->type;
		$results[$result->type . ':' . $result->slug] = $result->type . ':' . $result->label;
	}

	return apply_filters('rseuplt_taxonomy_options', $results);
}

