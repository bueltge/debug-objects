<?php
/**
 * Deprecated functions that are being phased out 
 *   completely or should be replaced with other functions.
 * 
 * @package     Debug Objects
 * @subpackage  Deprecated functions
 * @author      Frank Bültge
 * @since       2.1.17
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Query' ) ) {
	
	class Debug_Objects_Query {
	
		private static $classobj = NULL;
		
		public $callee;
		
		/**
		 * Handler for the action 'init'. Instantiates this class.
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  $classobj
		 */
		public static function init() {
			
			if ( NULL === self::$classobj )
				self::$classobj = new self;
			
			return self::$classobj;
		}
	
		public function __construct() {
			
			add_action( 'init', array( $this, 'get_message' ) );
		}
		
		public function get_message() {
			
			_deprecated_function( __CLASS__, '2.1.17', 'Debug_Objects_Db_Query' );
			
			$level = defined( 'E_USER_DEPRECATED' ) ? E_USER_DEPRECATED : E_USER_WARNING;
			$error_msg =  htmlentities( 'Class Debug_Objects_Query was replaced with Debug_Objects_Db_Query. Please re-save the settings of the Debug Objects Plugin.' );
			//trigger_error( $error_msg, $level );
		}
	
	}
	$Debug_Objects_Query = Debug_Objects_Query::init();
}
