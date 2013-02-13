<?php
/**
 * Add small screen with informations about hooks on current page of WP
 *
 * @package     Debug Objects
 * @subpackage  Current Hooks
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Page_Hooks' ) )
	return NULL;

class Debug_Objects_Page_Hooks {
	
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
	
	/**
	 * Constructor, init the methods
	 * 
	 * @return  void
	 * @since   2.1.11
	 */
	public function __construct() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
		
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}
	
	/**
	 * Add content for tabs
	 * 
	 * @param  Array $tabs
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {
		
		$tabs[] = array( 
			'tab' => __( 'Page Hooks' ),
			'function' => array( $this, 'get_hooks' )
		);
		
		return $tabs;
	}
	
	/**
	 * Get hooks for current page
	 * 
	 * @return String
	 */
	public function get_hooks() {
		global $wp_actions;
		
		$output  = '<h2>Total Actions: ' . count( $wp_actions ) . '</h2>';
		$output .= '<ol>';
		
		foreach ( $wp_actions as $key => $val ) {
			$output .= "<li><code>{$key}</code></li>";
		}
		
		$output .= '</ol>';
		
		echo $output;
	}
	
} // end class
