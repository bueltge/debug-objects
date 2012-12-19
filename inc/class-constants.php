<?php
/**
 * Add small screen with informations about constants of WP and PHP
 *
 * @package     Debug Objects
 * @subpackage  Constants
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Constants' ) ) {
	class Debug_Objects_Constants extends Debug_Objects {
		
		protected static $classobj = NULL;
		
		/**
		 * Handler for the action 'init'. Instantiates this class.
		 * 
		 * @access  public
		 * @return  $classobj
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
				'tab' => __( 'Constants', parent :: get_plugin_data() ),
				'function' => array( $this, 'view_def_constants' )
			);
			
			return $tabs;
		}
		
		public function view_def_constants( $echo = TRUE ) {
			global $wp_object;
			
			$output  = '';
			$output .= parent :: get_as_ul_tree( get_defined_constants(), '<strong class="h4">All Defined Constants</strong>' );
			$output .= '<p>' . __( 'Objects total:', parent :: get_plugin_data() ) . ' ' . $wp_object . '</p>';
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists
