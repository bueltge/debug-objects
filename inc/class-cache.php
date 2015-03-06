<?php
/**
 * Add small screen with informations about cache of WP
 *
 * @package     Debug Objects
 * @subpackage  Cache
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Cache' ) ) {
	class Debug_Objects_Cache extends Debug_Objects {
		
		protected static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return Debug_Objects_Cache|null $classobj
		 */
		public static function init() {
			
			NULL === self::$classobj and self::$classobj = new self();
			
			return self::$classobj;
		}
		
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Cache', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_object_cache' )
			);
			
			return $tabs;
		}
		
		public function get_object_cache( $echo = TRUE ) {
			global $wp_object_cache, $wp_object;
			
			$output  = '';
			$output .= parent :: get_as_ul_tree( $wp_object_cache, '<strong class="h4">WordPress Object Cache</strong>' );
			$output .= '<p>' . __( 'Objects total:', parent :: get_plugin_data() ) . ' ' . $wp_object . '</p>';
			
			if ( $echo )
				echo $output;

			return $output;
		}
		
	} // end class
}// end if class exists
