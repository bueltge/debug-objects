<?php
/**
 * Add php error function to use in dev installs
 *
 * @package     Debug Objects
 * @subpackage  PHP Error
 * 
 * Kudos to JosephLenton for the project PHP Error
 * @see         http://phperror.net/
 * @author      Frank Bültge
 * @since       2.1.11
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Php_Error' ) )
	return NULL;

class Debug_Objects_Php_Error {
	
	protected static $classobj = NULL;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_Php_Error|null $classobj
	 */
	public static function init() {
		
		NULL === self::$classobj and self::$classobj = new self();
		
		return self::$classobj;
	}

	/**
	 *
	 */
	public function __construct() {
		
		// use namespace, only PHP 5.3*
		if ( version_compare( phpversion(), '5.3a', '<' ) ) 
			return NULL;
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
		
		self::include_php_error();
		self::set_php_error();
	}

	/**
	 *
	 */
	public function include_php_error() {
		
		require_once( plugin_dir_path( __FILE__ ) . 'PHP-Error-master/src/php_error.php' );
	}

	/**
	 *
	 */
	public function set_php_error() {
		
		if ( ! isset( $_GET['php_error'] ) )
			$defaults = array( 'wordpress' => TRUE );
		else
			$defaults = array( 'wordpress' => FALSE );
		
		// see all options on https://github.com/JosephLenton/PHP-Error/wiki/Options
		$args = apply_filters( 'debug_objects_php_error_args', $defaults );
		\php_error\reportErrors( $args );
	}
	
} // end class