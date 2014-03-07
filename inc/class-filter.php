<?php
 /**
 * Class for custom filter, class, scripts and styles from this plugin
 * 
 * @package     Debug Objects
 * @subpackage  options content
 * @author      Frank Bültge
 * @since       03/07/2014
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Filter' ) )
	return NULL;

class Debug_Objects_Filter {
	
	/**
	 * The class object
	 * 
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;
	
	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 * @return  $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object )
			self::$class_object = new self;
		
		return self::$class_object;
	}
	
	/**
	 * Init function to register all used hooks
	 * 
	 * @since   09/25/2013
	 * @return  void
	 */
	public function __construct() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
	}
	
}