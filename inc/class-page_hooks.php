<?php
/**
 * Add small screen with informations about hooks on current page of WP
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

if ( class_exists( 'Debug_Objects_Page_Hooks' ) )
	return NULL;

class Debug_Objects_Page_Hooks {
	
	protected static $classobj = NULL;
	
	public $filters_storage = array();
	// define strings for important hooks to easier identify
	public $my_important_hooks = array();
	
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
		
		add_action( 'all', array( $this, 'store_fired_filters' ) );
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
	
	public function store_fired_filters( $tag ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $tag ] ) )
			return;

		$hooked = $wp_filter[ $tag ];
		ksort( $hooked );

		foreach ( $hooked as $priority => $function )
			$hooked[] = $function;

		$this->filters_storage[] = array(
			'tag'    => $tag,
			'hooked' => $wp_filter[ $tag ],
		);
	}
	
	/**
	 * Get hooks for current page
	 * 
	 * @return String
	 */
	public function get_hooks() {
		global $wp_actions;
		
		$callbacks        = array();
		$hooks            = array();
		$filter_hooks     = '';
		$filter_callbacks = '';
		
		foreach ( $this->filters_storage as $index => $the_ ) {
			
			$hook_callbacks = array();
			
			if ( ! in_array( $the_['tag'], $hooks ) ) {
				$hooks[] = $the_['tag'];
				$filter_hooks .= "<tr><td><code>{$the_['tag']}</code></td></tr>";
			}
			
			foreach( $the_['hooked'] as $priority => $hooked ) {
				foreach( $hooked as $id => $function ) {
					if ( is_string( $function['function'] ) ) {
						// as array
						$hook_callbacks[] = array(
							'name'     => $function['function'],
							'args'     => $function['accepted_args'],
							'priority' => $priority
						);
						// readable
						$filter_callbacks = "Function: {$function['function']}(), Arguments: {$function['accepted_args']}, Priority: {$priority}";
					}
				}
			}
			$callbacks[$the_['tag']][] = $filter_callbacks; //$hook_callbacks;
		}
		
		// format important hooks, that you easier identifier this hooks 
		$this->my_important_hooks = apply_filters(
			'debug_objects_important_hooks',
			array( 'admin_print_' , 'admin_head-', 'admin_footer-', 'add_meta_boxes' )
		);
		
		$output  = '';
		
		$output .= '<table>';
		
		$output .= '<tr class="nohover">';
		$output .= "\t" . '<th>Total Action Hooks: ' . count( $wp_actions ) . '</th>';
		//$output .= '<th>Total Filter Hooks: ' . count( $hooks ) . '</th>';
		$output .= "\t" . '<th>Total Filter Hooks & Callback: ' . count( $callbacks ) . '</th>';
		$output .= '</tr>';
		
		$output .= '<tr class="nohover">';
		
		$output .= "\t" . '<td><table>';
		$output .= "\t" . '<tr><td>Fired in order</td><td>Hook</td></tr>';
		
		$order = 1;
		foreach ( $wp_actions as $key => $val ) {
			
			// format, if the key is inside the important list of hooks
			foreach( $this->my_important_hooks as $hook ) {
				
				if ( FALSE !== strpos( $key, $hook ) )
					$key = '<span>' . $key . ' </span>';
			}
			
			$output .= '<tr><td>' . $order . '.</td><td><code>' . $key . '</code></td></tr>';
			$order ++;
		}
		$output .= '</table>';
		$output .= '</td>';
		/*
		$output .= '<td><table>';
		$output .= $filter_hooks;
		$output .= '</table></td>';
		*/
		$output .= "\t" . '<td>';
		$output .= "\t\t" . '<table>';
		foreach ( $callbacks as $hook => $values ) {
			
			// remove dublicate items
			$values = array_unique( $values );
			foreach ( $values as $key => $value ) {
				$escape = htmlspecialchars( $value, ENT_QUOTES, 'utf-8', FALSE );
				
				if ( empty( $escape ) )
					$escape = __( 'Empty' );
				
				$output .= '<tr>';
				$output .= "\t" . '<td>' . __( 'Hook:' ) 
					. ' <code>' . $hook . '</code><br> ' . $escape . '</td>';
				$output .= '</tr>';
			}

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
