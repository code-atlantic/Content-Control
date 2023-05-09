<?php
/**
 * Frontend feed setup.
 *
 * @copyright (c) 2021, Code Atlantic LLC.
 * @package ContentControl
 */

namespace ContentControl\Controllers\Frontend;

use ContentControl\Base\Controller;

use function ContentControl\content_is_restricted;
use function ContentControl\get_restricted_content_message;

defined( 'ABSPATH' ) || exit;

/**
 * Feed content restriction management.
 */
class Feeds extends Controller {

	/**
	 * Reference to the restrictions service.
	 *
	 * @var \ContentControl\Services\Restrictions
	 */
	public $restrictions;

	/**
	 * Initiate functionality.
	 */
	public function init() {
		if ( \ContentControl\is_rest() ) {
			return;
		}

		add_action( 'the_excerpt', [ $this, 'filter_feed_post_content' ] );
		add_action( 'the_content', [ $this, 'filter_feed_post_content' ] );
	}

	/**
	 * Filter feed post content when needed.
	 *
	 * @param string $content Content of post being checked.
	 *
	 * @return string
	 */
	public function filter_feed_post_content( $content ) {
		if ( ! is_feed() || ! content_is_restricted() ) {
			return $content;
		}

		return apply_filters(
			'content_control/feed_restricted_message',
			get_restricted_content_message(),
			$this->container->get( 'restrictions' )->get_applicable_restriction()
		);
	}

}