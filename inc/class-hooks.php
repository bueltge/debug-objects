<?php
/**
 * Add small screen with informations about hooks of WP
 *
 * @package     Debug Objects
 * @subpackage  Hooks
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Hooks' ) ) {
	class Debug_Objects_Hooks extends Debug_Objects {
		
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
				'tab' => __( 'Hooks', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_hooks' )
			);
			
			return $tabs;
		}
		
		public function get_hooks( $echo = TRUE, $sort_hooks = TRUE ) {
			global $wp_filter;
			
			if ( empty( $wp_filter ) )
				return NULL;
			
			if ( $sort_hooks )
				ksort( $wp_filter );
			
			$class = '';
			$output  = '';
			
			//hooks
			$output .= "\n" . '<h4>' . __( 'Simple WordPress Hooks &amp; Filters Insight', parent :: get_plugin_data()) . '</h4>' . "\n";
			$output .= "\n\n". '<ol>' . "\n";
			$wp_hook = 0;
			$wp_func = 0;
			
			foreach( $wp_filter as $hook => $arrays ) {
				
				if ( $sort_hooks )
					ksort($arrays);
				
				$wp_hook ++;
				
				$hook = esc_html( $hook );
				
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$output .= '<li' . $class . ' id="hook_' . $wp_hook. '" title="Hook: ' . $hook. '">' . $hook . "\n";
				$output .= '<ul id="li' . $wp_hook . '">' . "\n";
				
				foreach( $arrays as $priority => $subarray ) {
					$output .= '<li>' . __( 'Priority', parent :: get_plugin_data() ) . ' <strong>' . $priority . '</strong>: ' . "\n";
					$output .= '<ol>' . "\n";
					foreach($subarray as $sub) {
						$wp_func ++;
						
						$output .= '<li>';
						$func = $sub['function'];
						$args = $sub['accepted_args'];
						if ( is_array( $func ) ) {
							
							if ( is_object($func[0]) ) {
								$name  = get_class($func[0]) . '::' . $func[1];
								if ( empty($func[0]) ) {
									$output .= "\n". '<ul>' . "\n";
									$x = 0;
									foreach ( $func[0] as $k => $v ) {
										$x ++;
										if ( ! is_string($v) ) {
											$v  = htmlentities( serialize($v) );
											$v  = '<a href="javascript:debug_objects_toggle(\'serialize_' . $wp_func. $x. '\' );">' . __( 'View data', parent :: get_plugin_data() ) . '</a><textarea style="display:none;" class="large-text code" id="serialize_' . $wp_func. $x. '"name="v" cols="50" rows="10">' . $v. '</textarea>';
										}
										$output .= '<li>' . $k. ' : ' . $v. '</li>' . "\n";
									}
									$output .= '</ul>' . "\n";
								}
							} else {
								$output .= '<code>' . $func[0] . '()</code>';
							} // end if is_object()
						
						} else {
							$name  = $func;
						} // end if is_array()
						
						// echo params
					$output .= sprintf (
						"\t<code>%s()</code> (%s)",
						esc_html( $name ),
						sprintf(
							_n(
								__( '1 accepted argument', parent :: get_plugin_data() ),
								__( '%s accepted argument', parent :: get_plugin_data() ),
								$args
							),
							$args
						)
					);
				
						$output .= '</li>' . "\n";
					}
					$output .= '</ol>' . "\n";
					$output .= '</li>' . "\n";
				}
				
				$output .= '</ul>' . "\n";
				$output .= '</li>' . "\n";
			}
			
			$output .= '</ol>' . "\n";
			
			$output .= '<p class="alternate">' . __( 'Hooks total:', parent :: get_plugin_data() )
				. ' ' . $wp_hook . '<br />'
				. __( 'Register filter/actions total:', parent :: get_plugin_data() )
				. ' ' . $wp_func . '</p>';
			
			if ( $echo )
				echo $output;

			return $output;
		}
		
	} // end class
}// end if class exists
