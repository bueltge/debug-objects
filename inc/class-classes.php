<?php
/**
 * Add list of all declared classes and sub-classes
 *
 * @package     Debug Objects
 * @subpackage  Helper for Debug
 * @author      Frank Bültge
 * @since       2.1.5
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Classes' ) ) {
	class Debug_Objects_Classes {
		
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
		
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Classes' ),
				'function' => array( $this, 'get_classes' )
			);
			
			return $tabs;
		}
		
		/**
		 * Return all declared classes
		 * 
		 * @since 2.1.5 03/27/2012
		 * @param bool $sort sort classes
		 * @param bool $echo return or echo
		 */
		public function get_classes( $sort = TRUE, $echo = TRUE ) {
			
			$classes = get_declared_classes();
			if ( $sort )
				sort( $classes );
			
			if ( ! $echo ) {
				return $classes;
			} else {
				$i         = 0;
				$style     = '';
				$substyle  = '';
				$output    = '';
				$suboutput = '';
				foreach ( $classes as $count => $class ) {
					
					$subclasses = '';
					$style      = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					$output    .= '<tr' . $style . '><td>' . $count . '</td><td>' . $class . '</td>';
					$subclasses = get_parent_class( $class );
					
					if ( ! empty( $subclasses ) ) {
						$output .= '<td><code>extend</code> ' . $subclasses . '</td>';
					} else {
						$output .= '<td> </td>';
					}
					
					$output .= '</tr>';
					$i ++;
				}
				echo '<h4>Total Classes: ' . count( $classes ) . '</h4>';
				echo '<table>' . $output . '</table>';
			}
			
		}
		
	} // end class
}// end if class exists