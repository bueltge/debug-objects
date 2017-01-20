<?php
/**
 * Add small screen with information about hooks of WP
 *
 * @package        Debug Objects
 * @subpackage     Debug Hooks
 * @author         Frank Bültge
 * @since          2.0.0
 * @version        2017-01-20
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Debug_Hooks' ) ) {
	return;
}

class Debug_Objects_Debug_Hooks {

	static public $wp_func = 0;

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Debug_Hooks $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Debug_Hooks constructor.
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return;
		}

		self::$wp_func = 0;

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	public function get_conditional_tab( $tabs ) {

		/*
		$tabs[] = array( 
			'tab' => __( 'Debug Live Hooks', 'debug_objects' ),
			'function' => array( $this, 'list_live_hooks' )
		);
		*/
		$tabs[] = array(
			'tab'      => __( 'Debug Hooks', 'debug_objects' ),
			'function' => array( $this, 'list_hooks' )
		);

		return $tabs;
	}

	public function list_hooks( $filter = FALSE ) {

		global $wp_filter;

		$hooks = $wp_filter;
		ksort( $hooks );

		echo '<h2>Hooks Total: ' . count( $wp_filter ) . '</h2>';

		echo '<table class="tablesorter">';
		echo '<thead><tr><th>Hook</th><th>Priority &middot; Functions</th></tr></thead>';

		foreach ( $hooks as $tag => $hook ) {

			$hook = (array) $hook;

			if ( FALSE === $filter || FALSE !== strpos( $tag, $filter ) ) {
				$this->print_hooks( $tag, $hook );
			}
		}

		echo '</table>';

		echo '<p class="alternate">Register filter/actions total: ' . self::$wp_func . '</p>';
	}

	/**
	 * @param mixed $hook
	 */
	public function list_live_hooks( $hook = FALSE ) {

		if ( FALSE === $hook ) {
			$hook = 'all';
		}

		add_action( $hook, array( $this, 'list_hook_details' ), - 1 );
	}

	/**
	 * @param null $input
	 *
	 * @return null
	 */
	public function list_hook_details( $input = NULL ) {

		global $wp_filter;

		$tag = current_filter();
		if ( isset( $wp_filter[ $tag ] ) ) {
			$this->print_hooks( $tag, $wp_filter[ $tag ] );
		}

		return $input;
	}

	/**
	 * Print Hooks in table.
	 *
	 * @param string $tag
	 * @param array  $hook
	 */
	public function print_hooks( $tag, array $hook ) {

		//if ( $this->filter_plugin_hooks( $tag, $hook ) ) {
		//	return;
		//}

		// hook, priority, function
		$output = '<tr><td><code>' . $tag . '</code></td><td>';
		foreach ( $hook as $priority => $functions ) {

			$output .= $priority . ' &middot; ';

			foreach ( (array) $functions as $function ) {

				if ( isset( $function[ 'function' ] ) && $function[ 'function' ] !== 'list_hook_details' ) {

					$output .= "\t" . '<code>';

					if ( is_string( $function[ 'function' ] ) ) {
						$output .= $function[ 'function' ];
					} // @see https://github.com/jeremeamia/super_closure/blob/master/SuperClosure.class.php
					else if ( $function[ 'function' ] instanceOf Closure ) {
						$output .= '(object) <a href="http://php.net/manual/en/functions.anonymous.php" title="read more about Closures on php.net">Closure, Anonymous functions</a>';
					} else if ( is_object( $function[ 'function' ][ 0 ] ) ) {
						$output .= '(object) ' . get_class( $function[ 'function' ][ 0 ] )
						           . '::' . $function[ 'function' ][ 1 ];
					} else if ( is_string( $function[ 'function' ][ 0 ] ) ) {
						$output .= $function[ 'function' ][ 0 ] . '::' . $function[ 'function' ][ 1 ];
					} else {
						$output .= print_r( $function, TRUE );
					}

					$output .= '</code> (Accepted args: ' . $function[ 'accepted_args' ] . ') <br>';
				}
				self::$wp_func ++;
			}
		}
		$output .= '</td></tr>';

		echo $output;
	}

	/**
	 * Check if the filter to filter functions from the plugin active.
	 *
	 * @param string $tag
	 * @param array  $hook
	 *
	 * @return bool
	 */
	private function filter_plugin_hooks( $tag, array $hook ) {

		// Get settings
		$options = Debug_Objects_Settings::return_options();

		if ( ! isset( $options[ 'filter' ] ) ) {
			return FALSE;
		}

		if ( 1 !== (int) $options[ 'filter' ] ) {
			return FALSE;
		}

		// Filter Debug Objects Hooks
		if ( Debug_Objects::array_find( 'Debug_Objects', $hook ) ) {
			return TRUE;
		}

		if ( $hook[ array_search( 'Debug_Objects', $hook, TRUE ) ] ) {
			return TRUE;
		}

		// Filter hook, files from this plugin, not helpful
		if ( FALSE !== strpos( $tag, 'debug_objects' ) ) {
			return TRUE;
		}

		return FALSE;
	}

} // end class
