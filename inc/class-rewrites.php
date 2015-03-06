<?php
 /**
 * A list of all cached rewrites.
 * 
 * @package     Debug Objects
 * @subpackage  rewrites
 * @author      Frank Bültge
 * @since       03/17/2014
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Rewrites' ) )
	return NULL;

class Debug_Objects_Rewrites {
	
	/**
	 * The class object
	 * 
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 * @return Debug_Objects_Rewrites|String $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object )
			self::$class_object = new self;
		
		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since   09/25/2013
	 */
	public function __construct() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
		
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}
	
	/**
	 * Create tab for this data
	 * 
	 * @param  Array $tabs
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {
		
		$tabs[] = array(
			'tab' => __( 'Rewrites' ),
			'function' => array( $this, 'show_rewrites' )
		);
		
		return $tabs;
	}
	
	public function show_rewrites( $echo = TRUE ) {
		global $wp_rewrite, $wp;
		
		$permalink_structure = get_option( 'permalink_structure' );
		
		$output  = '<h4>' . __( 'Permalink Structure' ) . '</h4>';
		$output .= '<ul><li><code>' . esc_attr( $permalink_structure ) . '</code></li></ul>';
		
		$output .= '<h4>' . __( 'Current Rewrite' ) . '</h4>';
		$output .= '<ul>';
		if ( empty( $wp->matched_rule ) )
			$wp->matched_rule = __( 'Empty' );
		$output .= '<li>' . sprintf( __( 'Matched Rule: %s' ), $wp->matched_rule ) . '</li>';
		if ( empty( $wp->matched_query ) )
			$wp->matched_query = __( 'Empty' );
		$output .= '<li>' . sprintf( __( 'Matched Query: %s' ), $wp->matched_query ) . '</li>';
		if ( empty( $wp->query_string ) )
			$wp->query_string = __( 'Empty' );
		$output .= '<li>' . sprintf( __( 'Query String: %s' ), $wp->query_string ) . '</li>';
		$output .= '</ul>';
		
		$output .= '<h4>' . __('Rewrite Rules' ) . '</h4>';
		if ( ! empty( $wp_rewrite->rules ) ) {
			$output .= '<table class="tablesorter"><thead><tr>';
			$output .= '<th>' . __( 'Rule') . '</th>';
			$output .= '<th><strong>' . __( 'Rewrite' ) . '</th>';
			$output .= '</tr></thead><tbody>';
			
			foreach( $wp_rewrite->rules as $rule => $rewrite ) {
				$class = $wp->matched_rule === $rule ? ' class="current"' : '';
				$output .= '<tr>';
				$output .= '<td' . $class . '>' . $rule .'</td>';
				$output .= '<td' . $class . '>' . $rewrite . '</td>';
				$output .= '</tr>';
			}
			
			$output .= '</tbody></table>';
		} else {
			$output .= __( 'No rules defined.' );
		}
		
		if ( $echo )
			echo $output;

		return $output;
	}
}