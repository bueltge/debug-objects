<?php
/**
 * Set WordPress in the default mode
 *
 * @package     Debug Objects
 * @subpackage  Default Mode
 * @author      Frank B&uuml;ltge
 * @since       2.1.11
 */

if ( ! class_exists( 'Debug_Objects_Default_Mode' ) ) {
	
	class Debug_Objects_Default_Mode {
	
		public function init() {
			
			// only if the url param is active
			if ( ! isset($_GET['default']) )
				return NULL;
			
			// set default theme
			add_filter( 'template',   array( __CLASS__, 'disable_theme' ) );
			add_filter( 'stylesheet', array( __CLASS__, 'disable_theme' ) );
			// disable plugins
			add_filter( 'option_active_plugins', array( __CLASS__, 'disable_plugins' ) );
		}
		
		public function disable_theme( $template = '' ) {
			
			// get all themes
			$themes = wp_get_themes();
			
			if ( array_key_exists( 'twentytwelve', $themes ) )
				return 'twentytwelve';
			
			else if ( array_key_exists( 'twentyeleven', $themes ) )
				return 'twentyeleven';
			
			else if ( array_key_exists( 'twentyten', $themes ) )
				return 'twentyten';
			
			// No default of the list
			// return current active theme
			return $template;
		}
		
		public function disable_plugins() {
			
			return array();
		}
		
	}
	
}
