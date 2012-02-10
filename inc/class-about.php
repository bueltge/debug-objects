<?php
/**
 * Add small screen with informations about the plugin
 *
 * @package     Debug Objects
 * @subpackage  About plugin
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_About' ) ) {
	//add_action( 'admin_init', array( 'Debug_Objects_About', 'init' ) );
	
	class Debug_Objects_About extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'About', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_plugin_data' )
			);
			
			return $tabs;
		}
		
		public static function get_plugin_data( $echo = TRUE ) {
			
			$output  = '';
			$output .= '<h3>' . parent :: get_plugin_data( 'Title' ) . '</h3>';
			$output .= '<p>';
			$output .= '<strong>' . __( 'Description:', parent :: get_plugin_data() ) . '</strong> ';
			$output .= parent :: get_plugin_data( 'Description' ) . '</p>';
			$output .= '<p>';
			$output .= '<strong>' . __( 'Version:', parent :: get_plugin_data() ) . '</strong> ';
			$output .= parent :: get_plugin_data( 'Version' ) . '</p>';
			
			$output .= '<p><strong>' . __( 'Here\'s how you can give back:', parent :: get_plugin_data() ) . '</strong></p>';
			$output .= '<ul>';
			$output .= '<li><a href="http://wordpress.org/extend/plugins/debug-objects/" title="' 
				. esc_attr__( 'The Plugin on the WordPress plugin repository', parent :: get_plugin_data() ) 
				. '">' . __( 'Give the plugin a good rating.', parent :: get_plugin_data() ) . '</a></li>';
			$output .= '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6069955" title="' 
				. esc_attr__( 'Donate via PayPal', parent :: get_plugin_data() ) 
				. '">' . __( 'Donate a few euros.', parent :: get_plugin_data() ) . '</a></li>';
			$output .= '<li><a href="http://www.amazon.de/gp/registry/3NTOGEK181L23/ref=wl_s_3" title="' 
				. esc_attr__( 'Frank Bültge\'s Amazon Wish List', parent :: get_plugin_data() ) 
				. '">' . __( 'Get me something from my wish list.', parent :: get_plugin_data() ) . '</a></li>';
			$output .= '</ul>';
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists
