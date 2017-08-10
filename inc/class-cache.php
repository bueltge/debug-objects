<?php
/**
 * Add small screen with information about cache of WP
 *
 * @package     Debug Objects
 * @subpackage  Cache
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-20
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_Cache extends Debug_Objects {

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return  Debug_Objects_Cache|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Cache constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Get string for the tab and get function to display data.
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Cache', 'debug_objects' ),
			'function' => array( $this, 'get_object_cache' )
		);

		return $tabs;
	}

	/**
	 * Get data from global cache and print.
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function get_object_cache( $echo = TRUE ) {

		global $wp_object_cache, $wp_object;

		$output  = Debug_Objects::get_as_ul_tree( $wp_object_cache, '<strong class="h4">WordPress Object Cache</strong>' );
		$output .= '<p>' . esc_attr__( 'Objects total:', 'debug_objects' ) . ' ' . $wp_object . '</p>';

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

} // end class

