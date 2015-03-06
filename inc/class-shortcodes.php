<?php
/**
 * Will autofill and retain the list of Shortcode References
 *
 * @package     Debug Objects
 * @subpackage  List Shortcodes
 * @author      Frank Bültge
 * @since       02/25/2013
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Shortcodes' ) )
	return NULL;

class Debug_Objects_Shortcodes {
	
	protected static $classobj = NULL;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Shortcodes|null $classobj
	 */
	public static function init() {
		
		NULL === self::$classobj and self::$classobj = new self();
		
		return self::$classobj;
	}

	/**
	 * Constructor, init the methods
	 *
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
			'tab'      => __( 'Shortcodes' ),
			'function' => array( $this, 'get_shortcodes' )
		);
		
		return $tabs;
	}
	
	/**
	 * Get all shortocdes and his data
	 * 
	 * @return String
	 */
	public function get_shortcodes() {
		global $shortcode_tags;
		
		$style   = '';
		$output  = '<h4>Total Shortcodes: ' . count( $shortcode_tags ) . '</h4>';
		$output .= '<ol>';
		foreach( $shortcode_tags as $tag => $function ) {
			
			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			
			if ( is_string( $function ) ) {
				
				$function = '<code>' . $function . '</code>';
				
			} else if ( is_array( $function ) ) {
				
				$object = '';
				$parameters = '';
				if ( is_string( $function[0] ) ) {
					
					$object = $function[0];
					
				} else if ( is_object( $function[0] ) ) {
					
					$object = get_class( $function[0] );
					foreach ( $function[0] as $parameter => $value ) {
						
						// if the array is empty
						if ( empty( $value ) )
							$value = __( 'The Array is empty' );
						
						$parameters .=  '<li><code>' . $parameter . '</code> => <code>' . $value . '</code></li>';
					}
					
				}
				
				if ( ! empty( $parameters ) )
					$parameters = '<br><strong>Parameters of class:</strong><ul>' . $parameters . '</ul>';
				$function = '<code>' . $object . '::' . $function[1] . '</code>' . $parameters;
			}
			else {
				$function = 'empty';
			}
			
			
			$output .= "<li$style><strong>Shortcode: </strong><code>$tag</code> <strong>Function: </strong>$function</li>";
		}
		
		$output .= '</ol>';
		
		echo $output;
	}
	
} // end class
