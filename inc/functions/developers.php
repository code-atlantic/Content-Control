<?php
/**
 * Restriction utility & helper functions.
 *
 * @package ContentControl
 * @subpackage Functions
 * @copyright (c) 2023 Code Atlantic LLC
 */

namespace ContentControl;

use function ContentControl\plugin;
use function ContentControl\set_rules_query;

defined( 'ABSPATH' ) || exit;

/**
 * Check if content has restrictions.
 *
 * RENAME to post_has_restrictions()
 *
 * @since 2.0.0
 *
 * @param int|null $post_id Post ID.
 *
 * @return bool
 */
function content_has_restrictions( $post_id = null ) {
	$overload_post = setup_post( $post_id );

	$has_restrictions = plugin( 'restrictions' )->has_applicable_restrictions();

	// Clear post if we overloaded it.
	clear_post( $overload_post );

	/**
	 * Filter whether content has restrictions.
	 *
	 * @param bool $has_restrictions Whether content has restrictions.
	 * @param int|null $post_id Post ID.
	 *
	 * @return bool
	 */
	return (bool) apply_filters( 'content_control/content_has_restriction', $has_restrictions, $post_id );
}

/**
 * Get applicable restriction.
 *
 * @param int|null $post_id Post ID.
 *
 * @return \ContentControl\Models\Restriction|false
 */
function get_applicable_restriction( $post_id = null ) {
	$overload_post = setup_post( $post_id );

	$restriction = plugin( 'restrictions' )->get_applicable_restriction();

	// Clear post if we overloaded it.
	clear_post( $overload_post );

	return $restriction;
}

/**
 * Check if query has restricted posts.
 *
 * @param \WP_Query $query Query object.
 *
 * @return bool
 */
function queried_posts_have_restrictions( $query = null ) {
	return (bool) get_restriction_matches_for_queried_posts( $query );
}

/**
 * Check if query has restrictions.
 *
 * @param \WP_Query $query Query object.
 *
 * @return \ContentControl\Models\Restriction[]|false
 */
function get_restriction_matches_for_queried_posts( $query = null ) {
	if ( is_null( $query ) ) {
		global $wp_query;
		/**
		 * Global Query
		 *
		 * @var \WP_Query $wp_query
		 */
		$query = $wp_query;
	}

	// If its the main query, and not an archive-like page, bail.
	if ( $query->is_main_query() && ( ! $query->is_home() && ! $query->is_archive() && ! $query->is_search() ) ) {
		return false;
	}

	if ( empty( $query->posts ) ) {
		return false;
	}

	static $restrictions;

	// Generate cache key from hasing $wp_query.
	$cache_key = md5( wp_json_encode( $wp_query ) );

	if ( isset( $restrictions[ $cache_key ] ) ) {
		return $restrictions[ $cache_key ];
	}

	set_rules_query( $query );

	foreach ( $query->posts as $post ) {
		if ( content_is_restricted( $post->ID ) ) {
			$restriction = get_applicable_restriction( $post->ID );

			if ( ! isset( $restrictions[ $cache_key ][ $restriction->priority ] ) ) {
				// Handles deduplication & sorting.
				$restrictions[ $cache_key ][ $restriction->priority ] = [
					'restriction' => $restriction,
					'post_ids'    => [],
				];
			}

			// Add post to restriction.
			$restrictions[ $cache_key ][ $restriction->priority ]['posts'][] = $post->ID;
		}
	}

	set_rules_query( null );

	if ( empty( $restrictions[ $cache_key ] ) ) {
		$restrictions[ $cache_key ] = false;
	}

	return $restrictions[ $cache_key ];
}

/**
 * Check if user can view content.
 *
 * @param int|null $post_id Post ID.
 *
 * @return bool True if user meets requirements, false if not.
 */
function user_can_view_content( $post_id = null ) {
	// Called before setup_post because it does it internally already.
	if ( ! content_has_restrictions( $post_id ) ) {
		return true;
	}

	$overload_post = setup_post( $post_id );

	$restriction = plugin( 'restrictions' )->get_applicable_restriction();
	$can_view    = $restriction->user_meets_requirements();

	// Clear post if we overloaded it.
	clear_post( $overload_post );

	/**
	 * Filter whether user can view content.
	 *
	 * @param bool $can_view Whether user can view content.
	 * @param int|null $post_id Post ID.
	 *
	 * @return bool
	 */
	return (bool) apply_filters( 'content_control/user_can_view_content', $can_view, $post_id );
}

/**
 * Check if the current post is restricted.
 *
 * @param int|null $post_id Post ID.
 *
 * @return bool
 */
function content_is_restricted( $post_id = null ) {
	$is_restricted = content_has_restrictions( $post_id ) && ! user_can_view_content( $post_id );

	/**
	 * Filter whether content is restricted.
	 *
	 * @param bool $is_restricted Whether content is restricted.
	 * @param int|null $post_id Post ID.
	 *
	 * @return bool
	 */
	return (bool) apply_filters( 'content_control/content_is_restricted', $is_restricted, $post_id );
}

/**
 * Get restricted content message.
 *
 * @param int|null $post_id Post ID.
 *
 * @return string
 */
function get_restricted_content_message( $post_id = null ) {
	$restriction = get_applicable_restriction( $post_id );

	if ( ! $restriction ) {
		return '';
	}

	return $restriction->get_message();
}

/**
 * Check if protection methods should be disabled.
 *
 * Generally used to bypass protections when using page editors.
 *
 * @return bool
 */
function protection_is_disabled() {
	$checks = [
		// Disable protection when not on the frontend.
		! \ContentControl\is_frontend(),
		// Disable protection when user is excluded.
		user_is_excluded(),
		// Disable protection when viewing post previews.
		is_preview() && current_user_can( 'edit_post', get_the_ID() ),
	];

	/**
	 * Filter whether protection is disabled.
	 *
	 * @param bool $is_disabled Whether protection is disabled.
	 *
	 * @return bool
	 */
	return apply_filters(
		'content_control/protection_is_disabled',
		in_array( true, $checks, true )
	);
}
