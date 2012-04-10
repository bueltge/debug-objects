<?php
/**
 * Add small screen with informations about enqueued scripts and style in WP
 *
 * @package     Debug Objects
 * @subpackage  Enqueued Scripts and Stylesheets
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Enqueue_Stuff' ) ) {
	//add_action( 'admin_init', array( 'Debug_Objects_Enqueue_Stuff', 'init' ) );
	
	class Debug_Objects_Enqueue_Stuff extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Scripts & Styles', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_enqueued_stuff' )
			);
			
			return $tabs;
		}
		
		public static function get_enqueued_stuff( $handles = array(), $echo = TRUE ) {
			global $wp_scripts, $wp_styles;
			
			// scripts
			foreach ( $wp_scripts -> registered as $registered )
				$script_urls[ $registered -> handle ] = $registered -> src;
			// styles
			foreach ( $wp_styles -> registered as $registered )
				$style_urls[ $registered -> handle ] = $registered -> src;
			
			if ( empty( $handles ) ) {
				$handles = array_merge( $wp_scripts -> queue, $wp_styles -> queue );
				array_values( $handles );
			}
			$output = '';
			foreach ( $handles as $handle ) {
				if ( ! empty( $script_urls[ $handle ] ) )
					$output .= '<li>' . $script_urls[ $handle ] . '</li>';
				if ( ! empty( $style_urls[ $handle ] ) )
					$output .= '<li class="alternate">' . $style_urls[ $handle ] . '</li>';
			}
			
			$output = '<ul>' . $output . '</ul>';
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists