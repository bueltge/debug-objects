<?php
/**
 * Add small screen with information about hooks on current page of WP
 *
 * @package     Debug Objects
 * @subpackage  Current Hooks
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-20
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Page_Hooks' ) ) {
	return;
}

class Debug_Objects_Page_Hooks extends Debug_Objects {

	protected static $classobj;

	public $filter_storage = array();

	// define strings for important hooks to easier identify
	public $my_important_hooks = array();
	/**
	 * Store options.
	 *
	 * @var array
	 */
	private $options;

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
	 * Constructor, init the methods.
	 *
	 * @since   2.1.11
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_action( 'all', array( $this, 'store_fired_filters' ) );
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Add content for tabs
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$this->get_options();

		$tabs[] = array(
			'tab'      => esc_attr__( 'Page Hooks', 'debug_objects' ),
			'function' => array( $this, 'get_hooks' )
		);

		return $tabs;
	}

	/**
	 * Store tag of filter hooks in var.
	 *
	 * @param  string $tag
	 */
	public function store_fired_filters( $tag ) {

		global $wp_filter;

		if ( ! isset( $wp_filter[ $tag ] ) ) {
			return;
		}

		$hooked = (array) $wp_filter[ $tag ];

		// Don't translatable the string, to heavy for the hook 'all'.
		$fired = 'FALSE';
		if ( doing_filter( $tag ) ) {
			$fired = 'TRUE';
		}

		ksort( $hooked );
		foreach ( $hooked as $priority => $function ) {

			// Prevent buffer overflows of PHP_INT_MAX on array keys.
			// So reset the array keys.
			$hooked   = array_values( $hooked );
			$hooked[] = $function;
		}

		$this->filter_storage[] = array(
			'tag'    => $tag,
			'hooked' => $wp_filter[ $tag ],
			'fired'  => $fired
		);
	}

	/**
	 * Print hooks for current page
	 */
	public function get_hooks() {

		global $wp_actions;

		// Use this hook for remove Action Hook, like custom action hooks
		$wp_actions = (array) apply_filters( 'debug_objects_wp_actions', $wp_actions );

		$callbacks        = array();
		$hooks            = array();
		$filter_hooks     = '';
		$filter_callbacks = '';

		// Use this hook for remove Filter Hook from the completely array, like custom filter hooks.
		$filters_storage = (array) apply_filters( 'debug_objects_wp_filters', $this->filter_storage );

		foreach ( $filters_storage as $index => $the_ ) {

			// Use this hook for remove Filter Hook, like custom filter hooks.
			$filter_hook = apply_filters( 'debug_objects_filter_tag', array() );
			// Filter the Filter Hooks.
			if ( in_array( $the_[ 'tag' ], $filter_hook, TRUE ) ) {
				break;
			}

			if ( ! in_array( $the_[ 'tag' ], $hooks, TRUE ) ) {
				$hooks[] = $the_[ 'tag' ];
				$filter_hooks .= "<tr><td><code>{$the_['tag']}</code></td></tr>";
			}

			foreach ( (array) $the_[ 'hooked' ] as $priority => $hooked ) {

				foreach ( (array) $hooked as $id => $functions ) {

					if ( is_array( $functions ) ) {
						foreach ( (array) $functions as $function ) {

							if ( is_string( $function[ 'function' ] ) ) {
								// as array
								$hook_callbacks[] = array(
									'name'     => $function[ 'function' ],
									'args'     => $function[ 'accepted_args' ],
									'priority' => $id
								);
								// readable
								$filter_callbacks = "Fired: <code>{$the_['fired']}</code>, Function: <code>{$function['function']}()</code>, Arguments: <code>{$function['accepted_args']}</code>, Priority: <code>{$id}</code>";
							}
						}
					}

				}

			}
			$callbacks[ $the_[ 'tag' ] ][] = $filter_callbacks;
		}

		$output = '';
		$output .= '<table>';

		$output .= '<tr class="nohover">';
		$output .= "\t" . '<th>Total Action Hooks: ' . count( $wp_actions ) . '</th>';
		$output .= "\t" . '<th>Total Filter Hooks & Callback: ' . count( $callbacks ) . '</th>';
		$output .= '</tr>';

		$output .= '<tr class="nohover">';

		$output .= "\t" . '<td><table class="tablesorter">';

		$count_fired = '<th>' . esc_attr__( 'Count Fired', 'debug_objects' ) . '</th>';
		$output .= "\t" . '<thead><tr><th>' . esc_attr__( 'Fired in order', 'debug_objects' )
		           . '</th><th>' . esc_attr__( 'Action Hook', 'debug_objects' ) . '</th>'
		           . $count_fired . '</tr></thead>';

		// Filter hooks from this plugin.
		$wp_actions = $this->filter_debug_objects_hooks( $wp_actions );

		$order = 1;
		foreach ( $wp_actions as $key => $val ) {

			$count_fired = (int) did_action( $key );
			$output .= '<tr><td>' . $order . '.</td><td><code>'
			           . $key . '</code></td><td>' . $count_fired . '</td></tr>';
			$order ++;
		}
		$output .= '</table>';
		$output .= '</td>';

		$output .= "\t" . '<td>';
		$output .= "\t\t" . '<table class="tablesorter">';
		$output .= "\t" . '<thead><tr><th>' . esc_attr__( 'Fired in order', 'debug_objects' ) . '</th><th>'
		           . esc_attr__( 'Filter Hook & Callback', 'debug_objects' ) . '</th></tr></thead>';

		// Filter hooks from this plugin.
		$callbacks = $this->filter_debug_objects_hooks( $callbacks );

		$order = 1;
		foreach ( $callbacks as $hook => $values ) {

			// remove duplicate items
			$values = array_unique( $values );
			foreach ( $values as $key => $value ) {

				if ( empty( $value ) ) {
					$value = esc_attr__( 'Empty', 'debug_objects' );
				}

				$output .= '<tr>';
				$output .= "\t" . '<td>' . $order . '.</td><td>' . esc_attr__( 'Hook:', 'debug_objects' )
				           . ' <code>' . $hook . '</code><br> ' . $value . '</td>';
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

	/**
	 * Store options of the plugin.
	 */
	private function get_options() {

		$this->options = Debug_Objects_Settings::return_options();
	}
	/**
	 * Filter hooks to remove the hooks from this plugin.
	 *
	 * @param  array
	 *
	 * @return array
	 */
	private function filter_debug_objects_hooks( array $data ) {

		if ( ! isset( $this->options[ 'filter' ] ) ) {
			return $data;
		}

		if ( 1 !== (int) $this->options[ 'filter' ] ) {
			return $data;
		}

		foreach ( $data as $hook => $class ) {
			if ( 0 === strpos( strtolower( $hook ), 'debug_objects' ) ) {
				unset( $data[ $hook ] );
			}
		}

		return $data;
	}
} // end class
