<?php
/**
 * Add small screen with informations about enqueued scripts and style in WP
 *
 * @package     Debug Objects
 * @subpackage  Enqueued Scripts and Stylesheets
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Enqueue_Stuff' ) ) {
	class Debug_Objects_Enqueue_Stuff extends Debug_Objects {
		
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
				'tab' => __( 'Scripts & Styles', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_enqueued_stuff' )
			);
			
			return $tabs;
		}
		
		public function get_enqueued_stuff( $handles = array(), $echo = TRUE ) {
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