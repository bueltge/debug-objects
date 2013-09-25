<?php
/**
 * Add small screen with informations about constants of WP and PHP
 *
 * @package     Debug Objects
 * @subpackage  Constants
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Constants' ) ) {
	class Debug_Objects_Constants {
		
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
				'tab' => __( 'Constants' ),
				'function' => array( $this, 'view_def_constants' )
			);
			
			return $tabs;
		}
		
		public function view_def_constants( $echo = TRUE ) {
			
			$get_def_constants = get_defined_constants();
			
			$export  = var_export( $get_def_constants, TRUE );
			$output  = '<h4>Total Actions: ' . count( $get_def_constants ) . '</h4>';
			$escape  = htmlspecialchars( $export, ENT_QUOTES, 'utf-8', FALSE );
			$output .= '<pre><code>' . $escape . '</code></pre>';
			//$output .= parent :: get_as_ul_tree( $get_def_constants, '<strong class="h4">All Defined Constants</strong>' );
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists
