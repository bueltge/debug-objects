<?php
/**
 * Add small screen with information about hooks on current page of WP
 *
 * @package     Debug Objects
 * @subpackage  Current Hooks
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Page_Hooks' ) ) {
	return NULL;
}

class Debug_Objects_Page_Hooks {

	protected static $classobj = NULL;

	public $filters_storage = array();

	// define strings for important hooks to easier identify
	public $my_important_hooks = array();

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_Page_Hooks|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Constructor, init the methods
	 *
	 * @return \Debug_Objects_Page_Hooks
	@since   2.1.11
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return NULL;
		}

		add_action( 'all', array( $this, 'store_fired_filters' ) );
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Add content for tabs
	 *
	 * @param  Array $tabs
	 *
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[ ] = array(
			'tab'      => __( 'Page Hooks' ),
			'function' => array( $this, 'get_hooks' )
		);

		return $tabs;
	}

	public function store_fired_filters( $tag ) {

		global $wp_filter;

		if ( ! isset( $wp_filter[ $tag ] ) ) {
			return NULL;
		}

		$hooked = $wp_filter[ $tag ];

		// Usable since WP 3.9
		$fired = '';
		if ( function_exists( 'doing_filter' ) ) {
			$fired = 'Fired: FALSE, ';
			if ( doing_filter( $tag ) ) {
				$fired = 'Fired: TRUE, ';
			}

		}

		ksort( $hooked );

		foreach ( $hooked as $priority => $function ) {

			//prevent buffer overflows of PHP_INT_MAX on array keys
			//so reset the array keys
			$hooked = array_values( $hooked );
			array_push( $hooked, $function );
		}

		$this->filters_storage[ ] = array(
			'tag'    => $tag,
			'hooked' => $wp_filter[ $tag ],
			'fired'  => $fired
		);
	}

	/**
	 * Get hooks for current page
	 *
	 * @return String
	 */
	public function get_hooks() {

		global $wp_actions;

		// Use this hook for remove Action Hook, like custom action hooks
		$wp_actions = apply_filters( 'debug_objects_wp_actions', $wp_actions );

		$callbacks        = array();
		$hooks            = array();
		$filter_hooks     = '';
		$filter_callbacks = '';

		// Use this hook for remove Filter Hook from the completely array, like custom filter hooks
		$filters_storage = apply_filters( 'debug_objects_wp_filters', $this->filters_storage );

		foreach ( $filters_storage as $index => $the_ ) {

			// Use this hook for remove Filter Hook, like custom filter hooks
			$filter_hook = apply_filters( 'debug_objects_filter_tag', array() );
			// Filter the Filter Hooks
			if ( in_array( $the_[ 'tag' ], $filter_hook ) ) {
				break;
			}

			$hook_callbacks = array();

			if ( ! in_array( $the_[ 'tag' ], $hooks ) ) {
				$hooks[ ] = $the_[ 'tag' ];
				$filter_hooks .= "<tr><td><code>{$the_['tag']}</code></td></tr>";
			}

			foreach ( $the_[ 'hooked' ] as $priority => $hooked ) {

				foreach ( $hooked as $id => $function ) {
					if ( is_string( $function[ 'function' ] ) ) {
						// as array
						$hook_callbacks[ ] = array(
							'name'     => $function[ 'function' ],
							'args'     => $function[ 'accepted_args' ],
							'priority' => $priority
						);
						// readable
						$filter_callbacks = "{$the_['fired']}Function: {$function['function']}(), Arguments: {$function['accepted_args']}, Priority: {$priority}";
					}
				}

			}
			$callbacks[ $the_[ 'tag' ] ][ ] = $filter_callbacks;
		}

		// Format important hooks, that you easier identifier this hooks
		$this->my_important_hooks = apply_filters(
			'debug_objects_important_hooks',
			array( 'admin_print_', 'admin_head-', 'admin_footer-', 'add_meta_boxes' )
		);

		$output = '';
		$output .= '<table>';

		$output .= '<tr class="nohover">';
		$output .= "\t" . '<th>Total Action Hooks: ' . count( $wp_actions ) . '</th>';
		//$output .= '<th>Total Filter Hooks: ' . count( $hooks ) . '</th>';
		$output .= "\t" . '<th>Total Filter Hooks & Callback: ' . count( $callbacks ) . '</th>';
		$output .= '</tr>';

		$output .= '<tr class="nohover">';

		$output .= "\t" . '<td><table class="tablesorter">';

		// Usable since WP 3.9
		if ( function_exists( 'did_action' ) ) {
			$count_fired = '<th>Count Fired</th>';
		}
		$output .= "\t" . '<thead><tr><th>Fired in order</th><th>Action Hook</th>' . $count_fired . '</tr></thead>';

		$order = 1;

		foreach ( $wp_actions as $key => $val ) {
			// Format, if the key is inside the important list of hooks
			foreach ( $this->my_important_hooks as $hook ) {

				if ( FALSE !== strpos( $key, $hook ) ) {
					$key = '<span>' . $key . ' </span>';
				}
			}

			// Usable since WP 3.9
			if ( function_exists( 'did_action' ) ) {
				$count_fired = (int) did_action( $key );
			} else {
				$count_fired = (int) $val;
			}

			$output .= '<tr><td>' . $order . '.</td><td><code>' . $key . '</code></td><td>' . $count_fired . '</td></tr>';
			$order ++;
		}
		$output .= '</table>';
		$output .= '</td>';

		$output .= "\t" . '<td>';
		$output .= "\t\t" . '<table class="tablesorter">';
		$output .= "\t" . '<thead><tr><th>Fired in order</th><th>Filter Hook & Callback</th></tr></thead>';

		$order = 1;
		foreach ( $callbacks as $hook => $values ) {

			// remove duplicate items
			$values = array_unique( $values );
			foreach ( $values as $key => $value ) {
				$escape = htmlspecialchars( $value, ENT_QUOTES, 'utf-8', FALSE );

				if ( empty( $escape ) ) {
					$escape = __( 'Empty' );
				}

				$output .= '<tr>';
				$output .= "\t" . '<td>' . $order . '.</td><td>' . __( 'Hook:' )
					. ' <code>' . $hook . '</code><br> ' . $escape . '</td>';
				$output .= '</tr>';
			}

			$order ++;
		}
		$output .= "\t\t" . '</table>';
		$output .= "\t" . '</td>';

		$output .= '</tr>';
		$output .= '</table>';

		echo $output;
	}

	public function search_string( $haystack ) {

		$needle = $this->needle;

		return ( strpos( $haystack, $needle ) ); // or stripos() if you want case-insensitive searching.
	}

} // end class
