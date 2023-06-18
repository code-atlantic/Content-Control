<?php
/**
 * Plugin controller.
 *
 * @copyright (c) 2021, Code Atlantic LLC.
 *
 * @package ContentControl
 */

namespace ContentControl\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Base Upgrade class.
 */
abstract class Upgrade implements \ContentControl\Interfaces\Upgrade {

	/**
	 * Type.
	 *
	 * @var string Uses data versioning types.
	 */
	const TYPE = '';

	/**
	 * Version.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Stream.
	 *
	 * @var \ContentControl\Base\Stream
	 */
	public $stream;

	/**
	 * Upgrade constructor.
	 */
	public function __construct() {
	}

	/**
	 * Upgrade label
	 *
	 * @return string
	 */
	abstract public function label();

	/**
	 * Return full description for this upgrade.
	 *
	 * @return string
	 */
	public function description() {
		return '';
	}

	/**
	 * Check if the upgrade is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		$current_version = \ContentControl\get_data_version( static::TYPE );
		return $current_version && $current_version < static::VERSION;
	}

	/**
	 * Check if the prerequisites are met.
	 *
	 * @return bool
	 */
	public function prerequisites_met() {
		return true;
	}

	/**
	 * Get the dependencies for this upgrade.
	 *
	 * @return string[]
	 */
	public function get_dependencies() {
		return [];
	}

	/**
	 * Run the upgrade.
	 *
	 * @return void|WP_Error|false
	 */
	abstract public function run();

	/**
	 * Run the upgrade.
	 *
	 * @param \ContentControl\Base\Stream $stream Stream.
	 *
	 * @return void|WP_Error|false
	 */
	public function stream_run( $stream ) {
		$this->stream = $stream;

		$return = $this->run();

		unset( $this->stream );

		return $return;
	}

	/**
	 * Return the stream.
	 *
	 * @return \ContentControl\Base\Stream|Object $stream Stream.
	 */
	public function stream() {
		return isset( $this->stream ) ? $this->stream : (object) [
			'send_event' => function() {},
			'send_error' => function() {},
			'send_data'  => function() {},
		];
	}

}