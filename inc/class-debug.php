<?php
/**
 * Add small quick and dirty debug helper
 *
 * @package     Debug Objects
 * @subpackage  Post Meta Data
 * @author      Frank Bültge
 * @since       12/19/2012
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Debug' ) ) {
	class Debug_Objects_Debug extends Debug_Objects {
		
		protected static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return Debug_Objects_Debug|null $classobj
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
				'tab' => __( 'Debug', parent :: get_plugin_data() ),
				'function' => array( $this, 'debug' )
			);
			
			return $tabs;
		}
		
		/**
		 * Helper to debug a variable
		 * 
		 * @since 2.1.5 03/27/2012
		 * @param mixed $var the var to debug
		 * @param bool $echo True for echo
		 * @param bool $die  whether to die after outputting
		 */
		public function debug( $var = NULL, $echo = TRUE, $die = FALSE ) {
			
			if ( isset( $_GET['debug_var'] ) )
				$var = esc_attr( $_GET['debug_var'] );
			
			if ( ! isset( $var ) )
				$var = $GLOBALS['wp_version'];
			
			$var    = apply_filters( 'debug_objects_debug_var', $var );
			$output = '<h4>Debug</h4>' . Debug_Objects::pre_print( $var, '', TRUE );
			
			if ( $echo )
				echo $output;
			
			if ( $die )
				wp_die( __( 'Debug Objects wp_die on Debug method.', parent::get_plugin_data() ) );
		}
		
	} // end class
}// end if class exists