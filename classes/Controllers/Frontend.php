<?php
/**
 * Frontend general setup.
 *
 * @copyright (c) 2023, Code Atlantic LLC.
 * @package ContentControl
 */

namespace ContentControl\Controllers;

use ContentControl\Base\Controller;

use ContentControl\Controllers\Frontend\Blocks;
use ContentControl\Controllers\Frontend\Feeds;
use ContentControl\Controllers\Frontend\Posts;
use ContentControl\Controllers\Frontend\Redirects;
use ContentControl\Controllers\Frontend\Widgets;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend
 */
class Frontend extends Controller {

	/**
	 * Initialize Hooks & Filters
	 */
	public function init() {
		$this->container->register_controllers([
			'Frontend\Blocks'    => new Blocks( $this->container ),
			'Frontend\Feeds'     => new Feeds( $this->container ),
			'Frontend\Posts'     => new Posts( $this->container ),
			'Frontend\Redirects' => new Redirects( $this->container ),
			'Frontend\Widgets'   => new Widgets( $this->container ),
		]);

		$this->hooks();
	}

	/**
	 * Register general frontend hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'content_control/feed_restricted_message', '\ContentControl\append_post_excerpts', 9, 2 );
		add_filter( 'content_control/feed_restricted_message', 'wpautop', 10 );
		add_filter( 'content_control/feed_restricted_message', 'do_shortcode', 10 );

		add_filter( 'content_control/post_restricted_content', '\ContentControl\append_post_excerpts', 9, 2 );
		add_filter( 'content_control/post_restricted_content', 'wpautop', 10 );
		add_filter( 'content_control/post_restricted_content', 'do_shortcode', 10 );

		add_filter( 'content_control/post_restricted_excerpt', '\ContentControl\append_post_excerpts', 9, 2 );
		add_filter( 'content_control/post_restricted_excerpt', 'wpautop', 10 );
		add_filter( 'content_control/post_restricted_excerpt', 'do_shortcode', 10 );
	}

}
