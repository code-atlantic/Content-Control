<?php
/**
 * Query functions.
 *
 * @package ContentControl
 */

namespace ContentControl;

defined( 'ABSPATH' ) || exit;

/**
 * Get the main query.
 *
 * @return \WP_Query|null
 */
function get_main_wp_query() {
	global $wp_the_query;

	if ( ! is_null( $wp_the_query ) ) {
		/**
		 * WP Query object.
		 *
		 * @var \WP_Query $wp_the_query
		 */
		return $wp_the_query;
	}

	return null;
}

/**
 * Get the current wp query.
 *
 * Helper that returns the current query object, reguardless of if
 * it's the main query or not.
 *
 * @return \WP_Query|null
 */
function get_current_wp_query() {
	global $wp_query;

	if ( ! is_null( $wp_query ) ) {
		/**
		 * WP Query object.
		 *
		 * @var \WP_Query $wp_query
		 */
		return $wp_query;
	}

	return get_main_wp_query();
}

/**
 * Get the current query.
 *
 * @param \WP_Query|\WP_Term_Query|null $query Query object.
 *
 * @return \WP_Query|\WP_Term_Query|null
 */
function get_query( $query = null ) {
	if ( is_null( $query ) ) {
		if ( ! global_is_empty( 'current_query' ) ) {
			/**
			 * WP Query object.
			 *
			 * @var \WP_Query|\WP_Term_Query $query
			 */
			$query = get_global( 'current_query' );
		} else {
			$query = get_current_wp_query();
		}
	}

	return $query;
}

/**
 * Set the current query context.
 *
 * @param string $context 'main', 'main/posts', 'posts', 'main/blocks', 'blocks`.
 *
 * @return void
 */
function override_query_context( $context ) {
	set_global( 'current_query_context', $context );
}

/**
 * Reset the current query context.
 *
 * @return void
 */
function reset_query_context() {
	reset_global( 'current_query_context' );
}

/**
 * Get or set the current rule context (globaly accessible).
 *
 * 'main', 'main/posts', 'posts', 'main/blocks', 'blocks`
 *
 * Rules can work differently depending on the context they are being checked in.
 * This context allows us to handle the main query differently to other queries,
 * and blocks. It further allows us to handle blocks in several unique ways per
 * rule.
 *
 *  1. Main query is checked in the template_redirect action.
 *  2. Main query posts are checked in the the_posts filter & $wp_query->is_main_query().
 *  3. Alternate query posts are checked in the_posts or pre_get_posts & ! $wp_query->is_main_query().
 *  4. Blocks are checked in the content_control/should_hide_block filter.
 *
 * @param \WP_Query|null $query Query object.
 *
 * @return string 'main', 'main/posts', 'posts', 'main/blocks', 'blocks`.
 */
function current_query_context( $query = null ) {
	if ( ! global_is_empty( 'current_query_context' ) ) {
		return get_global( 'current_query_context' );
	}

	$query   = get_query( $query );
	$is_main = is_a( $query, '\WP_Query' ) && $query->is_main_query();

	// Blocks in the main page or other locations.
	if ( doing_filter( 'content_control/should_hide_block' ) ) {
		return $is_main ? 'main/blocks' : 'blocks';
	}

	// Main query (page/psst/home/search/archive etc) (template_redirect).
	if ( $is_main && doing_action( 'template_redirect' ) ) {
		return 'main';
	}

	// Before we process plain queries, we need to check if we're in a REST API request.
	if ( is_rest() ) {
		if ( doing_filter( 'get_terms' ) ) {
			return 'restapi/terms';
		}

		if ( doing_filter( 'pre_get_posts' ) || doing_filter( 'the_posts' ) ) {
			return 'restapi/posts';
		}

		return 'restapi';
	}

	// Process plain queries.
	if ( doing_filter( 'get_terms' ) ) {
		return 'terms';
	}

	if ( doing_filter( 'pre_get_posts' ) || doing_filter( 'the_posts' ) ) {
		return $is_main ? 'main/posts' : 'posts';
	}

	// Default to posts.
	return 'posts';
}

/**
 * Set the current rule (globaly accessible).
 *
 * Because we check posts in `the_posts`, we can't trust the global $wp_query
 * has been set yet, so we need to manage global state ourselves.
 *
 * @param \WP_Query|\WP_Term_Query|null $query WP_Query object.
 *
 * @return void
 */
function set_rules_query( $query ) {
	set_global( 'current_query', $query );
}

}

/**
 * Check and overload global post if needed.
 *
 * This has no effect when checking global queries ($post_id = null).
 *
 * @param int|\WP_Post|null $post_id Post ID.
 *
 * @return bool
 */
function setup_post( $post_id = null ) {
	$context = current_query_context();

	if ( 'restapi/terms' === $context || 'terms' === $context ) {
		/**
		 * Term ID.
		 *
		 * @var int|\WP_Term|null $post_id
		 */
		return setup_tax_object( $post_id );
	}

	global $post;
	
	// Return early if we don't have a post ID.
	if ( is_null( $post_id ) ) {
		return false;
	}

	$current_post_id = isset( $post ) ? $post->ID : null;

	$overload_post =
		( is_object( $post_id ) && $post_id->ID !== $current_post_id ) ||
		( is_int( $post_id ) && $post_id !== $current_post_id );

	if ( $overload_post ) {
		// Push the current $post to the stack so we can restore it later.
		push_to_global( 'overloaded_posts', $post ?? $current_post_id );

		// Overload the globals so conditionals work properly.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = get_post( $post_id );
		setup_postdata( $post );
	}

	return $overload_post;
}

/**
 * Check and overload global term if needed.
 *
 * This has no effect when checking global queries ($term_id = null).
 *
 * @param int|\WP_Term|null $term_id Term ID.
 *
 * @return bool
 */
function setup_tax_object( $term_id = null ) {
	global $cc_term;

	// Return early if we don't have a term ID.
	if ( is_null( $term_id ) ) {
		return false;
	}

	$current_term_id = isset( $cc_term ) ? $cc_term->term_id : null;

	$overload_term =
		( is_object( $term_id ) && $term_id->term_id !== $current_term_id ) ||
		( is_int( $term_id ) && $term_id !== $current_term_id );

	if ( $overload_term ) {
		// Push the current $post to the stack so we can restore it later.
		push_to_global( 'overloaded_terms', $cc_term ?? $current_term_id );

		// Overload the globals so conditionals work properly.
		$cc_term = get_term( $term_id );
		// Set the global term object (forward compatibility).
		set_global( 'term', $cc_term ?? $current_term_id );
	}

	return $overload_term;
}

/**
 * Check and clear global post if needed.
 *
 * @return void
 */
	if ( global_is_empty( 'overloaded_posts' ) ) {
		return;
	}

	global $post;

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$post = pop_from_global( 'overloaded_posts' );
	// Reset global post object.
	setup_postdata( $post );
}

/**
 * Check and clear global term if needed.
 *
 * @return void
 */
function reset_term() {
	if ( global_is_empty( 'overloaded_terms' ) ) {
		set_global( 'term', null );
		return;
	}

	global $cc_term;

	// Reset global post object.
	$cc_term = pop_from_global( 'overloaded_terms' );
	set_global( 'term', $cc_term );
}

/**
 * Get the endpoints for a registered post types.
 *
 * @return array<string,string>
 */
function get_post_type_endpoints() {
	$endpoints  = [];
	$post_types = get_post_types();

	foreach ( $post_types as $post_type ) {
		$object                                     = get_post_type_object( $post_type );
		$endpoints[ "/wp/v2/{$object->rest_base}" ] = $post_type;
	}

	return $endpoints;
}

/**
 * Get the endpoints for a registered taxonomies.
 *
 * @return array<string,string>
 */
function get_taxonomy_endpoints() {
	$endpoints  = [];
	$taxonomies = get_taxonomies();

	foreach ( $taxonomies as $taxonomy ) {
		$object = get_taxonomy( $taxonomy );

		if ( ! $object ) {
			continue;
		}

		$endpoints[ "/wp/v2/{$object->rest_base}" ] = $taxonomy;
	}

	return $endpoints;
}

/**
 * Get the intent of the current REST API request.
 *
 * @return array{type:'post_type'|'taxonomy'|'unknown',name:string,id:int,index:bool,search:string|false}
 */
function get_rest_api_intent() {
	global $wp;

	$intent = get_global( 'rest_intent' );

	$rest_route = null;

	if ( is_null( $intent ) ) {
		$result = [
			'type'   => 'unknown',
			'name'   => '',
			'id'     => 0,
			'index'  => false,
			'search' => false,
		];

		// Handle built-in REST API endpoints.
		if ( ! empty( $wp->query_vars['rest_route'] ) ) {
			$rest_route = $wp->query_vars['rest_route'];

			if ( strpos( $rest_route, '/wp/v2/' ) === 0 ) {
				$post_type_endpoints = get_post_type_endpoints();
				$taxonomy_endpoints  = get_taxonomy_endpoints();

				$endpoint_parts = explode( '/', str_replace( '/wp/v2/', '', $rest_route ) );

				// If we have a post type or taxonomy, the name is the first part (posts, categories).
				$result['name'] = sanitize_key( $endpoint_parts[0] );

				if ( count( $endpoint_parts ) > 1 ) {
					// If we have an ID, then the second part is the ID.
					$result['id'] = absint( $endpoint_parts[1] );
				} else {
					// If we have no ID, then we are either searching or indexing.
					$result['index']  = true;
					$result['search'] = isset( $wp->query_vars['s'] ) ? sanitize_title( $wp->query_vars['s'] ) : false;
				}

				// Build a matching route.
				$endpoint_route = "/wp/v2/{$result['name']}";

				if ( isset( $post_type_endpoints[ $endpoint_route ] ) ) {
					$result['type'] = 'post_type';
				}

				if ( isset( $taxonomy_endpoints[ $endpoint_route ] ) ) {
					$result['type'] = 'taxonomy';
				}
			} else {
				// We currently have no way of really dealing with non WP REST requests.
				// This filter allows us or others to correctly handle these requests in the future.
				apply_filters( 'content_control/determine_uknonwn_rest_api_intent', $result, $rest_route );
			}
		}

		set_global( 'rest_intent', $result );
	}

	return apply_filters( 'content_control/get_rest_api_intent', $intent, $rest_route );
}
