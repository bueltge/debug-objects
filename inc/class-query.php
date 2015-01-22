<?php
/**
 * Add small screen with informations about queries of WP
 *
 * @package	    Debug Queries
 * @subpackage  Cache
 * @author      Frank Bültge
 * @since       2.0.0
 * @deprecated  2.1.17
 */

if ( ! function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Deprecated. Use Debug_Objects_Db_Query (class-db_query.php) instead.
 */
$msg   = __( 'Class Debug_Objects_Query was replaced with Debug_Objects_Db_Query. Please re-save the settings of the Debug Objects Plugin.' );
_deprecated_file( basename( __FILE__ ), '2.1.17', 'class-db_query.php', $msg );

if ( ! class_exists( 'Debug_Objects_Query' ) ) {
	
	class Debug_Objects_Query {
	
		private static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @return Debug_Objects_Query|null $classobj
		 */
		public static function init() {
			
			if ( NULL === self::$classobj )
				self::$classobj = new self;
			
			return self::$classobj;
		}

		/**
		 * Init the message
		 *
		 */
		public function __construct() {
			
			add_action( 'init', array( $this, 'get_message' ) );
		}
		
		/**
		 * Get message to inform about deprecated class
		 * 
		 * @return  void
		 */
		public function get_message() {
			
			//$level = defined( 'E_USER_DEPRECATED' ) ? E_USER_DEPRECATED : E_USER_WARNING;
			$msg   = __( 'Class Debug_Objects_Query was replaced with Debug_Objects_Db_Query. Please re-save the settings of the Debug Objects Plugin.' );
			//trigger_error( $msg, $level );
			_deprecated_function( __CLASS__, '2.1.17', 'Debug_Objects_Db_Query' );
		}
	
	}
	$Debug_Objects_Query = Debug_Objects_Query::init();
	
}