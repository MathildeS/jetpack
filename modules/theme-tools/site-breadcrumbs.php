<?php
/**
 * Plugin Name: Site Breadcrumbs
 * Plugin URI: http://wordpress.com
 * Description: Quickly add breadcrumbs to the single view of a hierarchical post type or a hierarchical taxonomy.
 * Author: Automattic
 * Version: 1.0
 * Author URI: http://wordpress.com
 * License: GPL2 or later
 */

function jetpack_breadcrumbs() {
	$taxonomy = is_category() ? 'category' : get_query_var( 'taxonomy' );
	$is_taxonomy_hierarchical = is_taxonomy_hierarchical( $taxonomy );

	$post_type = is_page() ? 'page' : get_query_var( 'post_type' );
	$is_post_type_hierarchical = is_post_type_hierarchical( $post_type );

	if ( ! ( $is_post_type_hierarchical || $is_taxonomy_hierarchical ) || is_front_page() ) {
		return;
	}

	$breadcrumb = '';

	if ( $is_post_type_hierarchical ) {
		$post_id = get_queried_object_id();
		$ancestors = array_reverse( get_post_ancestors( $post_id ) );
		if ( $ancestors ) {
			foreach ( $ancestors as $ancestor ) {
				$breadcrumb .= '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
			}
		}
		$breadcrumb .= '<span class="current-page">' . esc_html( get_the_title( $post_id ) ) . '</span>';
	} elseif ( $is_taxonomy_hierarchical ) {
		$current = get_term( get_queried_object_id(), $taxonomy );

		if ( is_wp_error( $current ) ) {
			return;
		}

		if ( $current->parent ) {
			$breadcrumb = jetpack_get_term_parents( $current->parent, $taxonomy );
		}

		$breadcrumb .= '<span class="current-category">' . esc_html( $current->name ) . '</span>';
	}

	$home = '<a href="' . esc_url( home_url( '/' ) ) . '" class="home-link" rel="home">' . esc_html__( 'Home', 'jetpack' ) . '</a>';

	echo '<nav class="entry-breadcrumbs">' . $home . $breadcrumb . '</nav>';
}

/**
 * Return the parents for a given taxonomy term ID.
 *
 * @param int $term Taxonomy term whose parents will be returned.
 * @param string $taxonomy Taxonomy name that the term belongs to.
 * @param array $visited Terms already added to prevent duplicates.
 *
 * @return string A list of links to the term parents.
 */
function jetpack_get_term_parents( $term, $taxonomy, $visited = array() ) {
	$parent = get_term( $term, $taxonomy );

	if ( is_wp_error( $parent ) ) {
		return $parent;
	}

	$chain = '';

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= jetpack_get_term_parents( $parent->parent, $taxonomy, $visited );
	}

	$chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">' . $parent->name . '</a>';

	return $chain;
}