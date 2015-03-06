<?php
/**
 * Add logging in chrome console
 *
 * @package     Debug Objects
 * @subpackage  Chrome PHP
 * 
 * Kudos to Craig Campbell for the project ChromePHP
 * @see         http://www.chromephp.com
 * @author      Frank B&uuml;ltge
 * @since       2.1.11
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Chromephp' ) )
	return NULL;

class Debug_Objects_Chromephp {
	
	protected static $classobj = NULL;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Chromephp|null $classobj
	 */
	public static function init() {
		
		NULL === self::$classobj and self::$classobj = new self();
		
		return self::$classobj;
	}
	
	/**
	 * Constructor, init the methods
	 * 
	 * @return  void
	 * @since   2.1.11
	 */
	public function __construct() {
		
		// use namespace, only PHP 5.3*
		if ( version_compare( phpversion(), '5.3a', '<' ) ) 
			return NULL;
		
		//if ( ! current_user_can( '_debug_objects' ) )
		//	return NULL;
		
		if ( class_exists( 'ChromePhp' ) )
			return;
		
		self::include_chromephp();
	}
	
	/**
	 * Inlcude the lib ChomePHP
	 * 
	 * @return  void
	 * @since   2.1.11
	 */
	public function include_chromephp() {
		
		require_once( plugin_dir_path( __FILE__ ) . 'chromephp/ChromePhp.php' );
	}
	
} // end class
