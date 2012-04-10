<?php
/**
 * Add small screen with informations about constants of WP and PHP
 *
 * @package     Debug Objects
 * @subpackage  Constants
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Constants' ) ) {
	//add_action( 'admin_init', array( 'Debug_Objects_Constants', 'init' ) );
	
	class Debug_Objects_Constants extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Constants', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'view_def_constants' )
			);
			
			return $tabs;
		}
		
		public static function view_def_constants( $echo = TRUE ) {
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
