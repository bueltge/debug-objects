<?php
/**
 * Add temporary debug view of rewrite requests
 *
 * @package     Debug Objects
 * @subpackage  Rewrite_Backtrace requests
 * 
 * @author      Frank Bültge
 * @since       2.1.12 (01/25/2013)
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Rewrite_Backtrace' ) )
	return NULL;

class Debug_Objects_Rewrite_Backtrace {
	
	protected static $classobj = NULL;
	
	public $transient_key = 'debug_objects_rewrite_backtrace';

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_Rewrite_Backtrace|null $classobj
	 */
	public static function init() {
		
		NULL === self::$classobj and self::$classobj = new self();
		
		return self::$classobj;
	}

	/**
	 * Constructor, init the methods
	 *
	 * @since   2.1.11
	 */
	public function __construct() {
		
		add_filter( 'wp_redirect', array( $this, 'redirect_debug' ), 1, 2 );
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}
	
	/**
	 * Parse data and save in transient data in DB
	 * 
	 * @param  $location
	 * @return $location
	 */
	public function redirect_debug( $location ) {
		
		ob_start();
		debug_print_backtrace();
		$output['debug_backtrace'] = ob_get_contents();
		$output['_get']            = $_GET;
		$output['_post']           = $_POST;
		$output['global_post']     = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : '';
		if ( is_network_admin() )
			set_site_transient( $this->transient_key, $output, 120 );
		else
			set_transient( $this->transient_key, $output, 120 );
		ob_end_clean();
		
		return $location;
	}
	
	/**
	 * Create Tab in Debug Objects list
	 * 
	 * @param   Array $tabs
	 * @return  Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {
		
		$tabs[] = array( 
			'tab' => __( 'Rewrite Backtrace' ),
			'function' => array( $this, 'get_debug_backtrace' )
		);
		
		return $tabs;
	}
	
	/**
	 * Get data from transient to data befre rewrite
	 * 
	 * @param   Boolean $echo
	 * @return  String  $output
	 */
	public function get_debug_backtrace( $echo = TRUE ) {
		
		if ( is_network_admin() )
			$data = get_site_transient( $this->transient_key );
		else
			$data = get_transient( $this->transient_key );
		
		$output  = '';
		$output .= '<h4>$_POST</h4>';
		if ( empty( $data['_post'] ) ) {
			$output .= 'Empty';
		} else {
			$output .= Debug_Objects::pre_print( $data['_post'], '', TRUE );
		}
		
		$output .= '<h4>$_GET</h4>';
		if ( empty( $data['_get'] ) ) {
			$output .= 'Empty';
		} else {
			$output .= Debug_Objects::pre_print( $data['_get'], '', TRUE );
		}
		
		$output .= '<h4>Debug Backtrace</h4>';
		if ( empty( $data['debug_backtrace'] ) ) {
			$output .= 'Empty';
		} else {
			$output .= Debug_Objects::pre_print( $data['debug_backtrace'], '', TRUE );
		}
		
		if ( $echo )
			echo $output;

		return $output;
	}
	
} // end class