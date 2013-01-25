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
	
	public $output = '';
	
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
		
		$this->output = '';
		
		add_filter( 'wp_redirect', array( $this, 'redirect_debug' ), 1, 2 );
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}
	
	public function redirect_debug( $location, $status ) {
		
		ob_start();
		debug_print_backtrace();
		$this->output = ob_get_contents();
		update_option( 'debug_objects_rewrite_backtrace', $this->output );
		ob_end_clean();
		
		return $location;
	}
	
	public function get_conditional_tab( $tabs ) {
		
		$tabs[] = array( 
			'tab' => __( 'Rewrite Backtrace' ),
			'function' => array( $this, 'get_debug_backtrace' )
		);
		
		return $tabs;
	}
	
	public function get_debug_backtrace() {
		
		$export = var_export( get_option( 'debug_objects_rewrite_backtrace' ), TRUE );
		$escape = htmlspecialchars( $export, ENT_QUOTES, 'utf-8', FALSE );
		echo '<pre>' . $escape . '</pre>';
	}
	
} // end class