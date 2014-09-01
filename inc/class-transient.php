<?php
/**
 * Return different information about transients
 *
 * @package     Debug Objects
 * @subpackage  Transient Information
 * @author      Frank Bültge
 * @since       2014-08-26
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_Transient {

	/**
	 * The class object
	 *
	 * @since  2014-08-26
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * List of log entries.
	 *
	 * @type array
	 */
	protected $log = array();

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 * @return String $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since
	 * @return \Debug_Objects_Transient
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return NULL;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Create tab for this data
	 *
	 * @param  Array $tabs
	 *
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[ ] = array(
			'tab'      => __( 'Transition' ),
			'function' => array( $this, 'dummy' )
		);

		return $tabs;
	}

	public function dummy() {

		echo 'test';
	}

} // end class