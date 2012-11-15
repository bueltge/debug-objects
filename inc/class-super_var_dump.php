<?php
/**
 * Add super var dump function to use in dev installs
 *
 * @package     Debug Objects
 * @subpackage  Super var dump
 * 
 * Kudos to Eric Lewis <http://www.ericandrewlewis.com/> for the function super_var_dump()
 * @see         https://github.com/ericandrewlewis/super-var-dump
 * @author      Frank B&uuml;ltge
 * @since       2.0.1
 */

if ( class_exists( 'Debug_Objects_Memory' ) )
	return NULL;

class Debug_Objects_Super_Var_Dump extends Debug_Objects {
	
	public static function init() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return;
		
		self::include_super_var_dump();
	}
	
	public static function include_super_var_dump() {
		
		require_once( plugin_dir_path( __FILE__ ) . 'super-var_dump.php' );
		super_var_dump( $_SERVER );
	}
	
} // end class