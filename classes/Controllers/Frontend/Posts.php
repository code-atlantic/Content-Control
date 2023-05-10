<?php
/**
 * Frontend post setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package ContentControl
 */

namespace ContentControl\Controllers\Frontend;

use ContentControl\Base\Controller;

use function ContentControl\content_is_restricted;
use function ContentControl\protection_is_disabled;

defined( 'ABSPATH' ) || exit;

/**
 * Class Posts
 *
 * @package ContentControl
 */
class Posts extends Controller {

	/**
	 * Initiate functionality.
	 */
	public function init() {
		if ( \ContentControl\is_rest() || is_admin() ) {
			return;
		}

		add_action( 'the_content', [ $this, 'the_content' ], 1000 );
		add_action( 'get_the_excerpt', [ $this, 'get_the_excerpt' ], 1000, 2 );
	}

	/**
	 * Filter post content when needed.
	 *
	 * @param string $content Content of post being checked.
	 *
	 * @return string
	 */
	public function the_content( $content ) {
		global $post;

		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		// If this isn't a post type that can be restricted, bail.
		if ( ! $post || ! is_object( $post ) || $post->ID <= 0 ) {
			return $content;
		}

		if ( protection_is_disabled() ) {
			return $content;
		}

		if ( ! content_is_restricted( $post ) ) {
			return $content;
		}

		$restriction = $this->container->get( 'restrictions' )->get_applicable_restriction();

		return apply_filters(
			'content_control/post_restricted_content',
			$restriction->get_message(),
			$restriction
		);
	}

	/**
	 * Filter post excerpt when needed.
	 *
	 * @param string  $post_excerpt The post excerpt.
	 * @param WP_Post $post         Post object.
	 *
	 * @return string
	 */
	public function get_the_excerpt( $post_excerpt, $post ) {

		// If this isn't a post type that can be restricted, bail.
		if ( ! $post || ! is_object( $post ) || $post->ID <= 0 ) {
			return $post_excerpt;
		}

		if ( protection_is_disabled() ) {
			return $post_excerpt;
		}

		if ( ! content_is_restricted( $post ) ) {
			return $post_excerpt;
		}

		$restriction = $this->container->get( 'restrictions' )->get_applicable_restriction();

		return apply_filters(
			'content_control/post_restricted_excerpt',
			$restriction->get_message(),
			$restriction
		);
	}

}
