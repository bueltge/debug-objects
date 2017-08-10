<?php
/**
 * Class for custom filter, class, scripts and styles from this plugin
 *
 * @package     Debug Objects
 * @subpackage  options content
 * @author      Frank Bültge
 * @since       03/07/2014
 * @version     2017-01-21
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Filter' ) ) {
	return;
}

class Debug_Objects_Filter extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object;

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 */
	public static function init() {

		if ( NULL === self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since   09/25/2013
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}
	}

}
