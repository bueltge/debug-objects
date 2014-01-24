<?php
/**
 * Set WordPress in the default mode
 *
 * @package     Debug Objects
 * @subpackage  Default Mode
 * @author      Frank Bültge
 * @since       2.1.11
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Default_Mode' ) ) {
	class Debug_Objects_Default_Mode {
		
		private static $classobj = NULL;
		
		private static $disabled = array();
		
		private static $excludes = array( 'Debug Objects' );
		
		private static $mu_plugins = array();
		
		/**
		 * Handler for the action 'init'. Instantiates this class.
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  $classobj
		 */
		public function init() {
			
			if ( NULL === self::$classobj ) {
				self::$classobj = new self;
			}
			
			return self::$classobj;
		}
		
		public function __construct() {
			
			// only if the url param is active
			if ( ! isset( $_GET['default'] ) )
				return NULL;
			
			self::$mu_plugins = get_mu_plugins();
			
			// set default theme
			add_filter( 'template',   array( $this, 'disable_theme' ) );
			add_filter( 'stylesheet', array( $this, 'disable_theme' ) );
			
			// disable plugins
			self::create_active_plugin_list();
			add_filter( 'option_active_plugins', array( $this, 'disable_plugins' ) );
			// usable without the chance to filter for a plugin
			// add_filter( 'option_active_plugins', '__return_empty_array' );
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
		
		/**
		 * Adds a filename to the list of plugins to disable
		 * 
		 * @see  https://gist.github.com/1044546
		 */
		public function disable( $file ) {
			
			self::$disabled[] = $file;
		}
		
		public function get_plugins() {
			
			// Force WordPress to update the plugin list
			wp_update_plugins();
			
			$plugins    = get_plugins();
			$mu_plugins = get_mu_plugins();
			$plugins    = array_merge( $plugins, $mu_plugins );
			
			return $plugins;
		}
		
		/**
		 * get list of all active plugins
		 * 
		 * @todo: maybe via filter hook 'active_plugins'
		 * @todo: maybe via wp_get_active_and_valid_plugins()
		 */
		public function create_active_plugin_list() {
			// get plugins
			$plugins = self::get_plugins();
			$key = FALSE;
			// add to array of plugins, there deactivate
			foreach ( $plugins as $file => $plugin ) {
				$key = array_search( $plugin['Name'], self::$excludes );
				// if active, add to list
				if ( self::get_status( $file ) && 0 !== $key )
					self::disable( $file );
			}
		}
		
		private function get_status( $file ) {
			
			if ( is_plugin_active( $file ) )
				return TRUE;
			else if ( is_plugin_active_for_network( $file ) )
				return TRUE;
			else if ( isset( self::$mu_plugins[ $file ] ) )
				return TRUE;
			else
				return FALSE;
		}
		
		public function deactivate_plugins( $plugins ) {
			
			deactivate_plugins( $plugins, TRUE );
		}
		
		public function disable_plugins( $plugins ) {
			
			if ( count( self::$disabled ) ) {
				foreach ( (array) self::$disabled as $plugin ) {
					$key = array_search( $plugin, $plugins );
					if ( FALSE !== $key )
						unset( $plugins[$key] );
				}
			}
			
			return $plugins;
		}
		
	}
	
} // end class