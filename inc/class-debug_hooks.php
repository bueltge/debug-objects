<?php
/**
 * Add small screen with informations about hooks of WP
 *
 * @package	    Debug Objects
 * @subpackage  Debug Hooks
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Debug_Hooks' ) ) {
	
	class Debug_Objects_Debug_Hooks extends Debug_Objects {
		
		static public $wp_func = 0;
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			self::$wp_func = 0;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			/*
			$tabs[] = array( 
				'tab' => __( 'Debug Live Hooks', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'list_live_hooks' )
			);
			*/
			$tabs[] = array( 
				'tab' => __( 'Debug Hooks', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'list_hooks' )
			);
			
			return $tabs;
		}
		
		public static function list_hooks( $filter = false ){
			global $wp_filter;
			
			$hooks = $wp_filter;
			ksort( $hooks );
			
			$wp_hook = 0;
			foreach( $hooks as $tag => $hook ) {
				if ( FALSE === $filter || FALSE !== strpos( $tag, $filter ) ) {
					self::dump_hook($tag, $hook);
				}
				$wp_hook ++;
			}
			
			echo '<p class="alternate">Hooks Total: ' . $wp_hook;
			echo '<br>Register filter/actions total: ' . self::$wp_func . '</p>';
		}
		
		public static function list_live_hooks( $hook = FALSE ) {
			
			if ( FALSE === $hook )
				$hook = 'all';
		
			add_action( $hook, array( __CLASS__, 'list_hook_details' ), -1 );
		}
		
		public static function list_hook_details( $input = NULL ) {
			global $wp_filter;
			
			$tag = current_filter();
			if ( isset( $wp_filter[$tag] ) )
				self::dump_hook( $tag, $wp_filter[$tag] );
		
			return $input;
		}
		
		public static function dump_hook( $tag, $hook, $echo = TRUE ) {
			ksort($hook);
			
			$tag = esc_html( $tag );
			
			$output = '<ul><li><strong>' . $tag . '</strong><ul>';
			
			foreach( $hook as $priority => $functions ) {
				
				$output .= '<li>Priority: ' . $priority . '<ul>';
			
				foreach( $functions as $function ) {
					if ( $function['function'] != 'list_hook_details' ) {
						
						$output .= "\t" . '<li>&gt;&gt;&gt;&gt;&gt; <code>';
						
						if ( is_string( $function['function'] ) )
							$output .= $function['function'];
						
						else if ( $function['function'] instanceOf Closure )
							$output .= '(object) <a href="http://php.net/manual/en/functions.anonymous.php" title="read more about Closures on php.net">Closure, Anonymous functions</a>';
						
						else if ( is_object( $function['function'][0] ) ) 
							$output .= '(object) ' . get_class( $function['function'][0] ) . ' :: ' . $function['function'][1];
						
						else if ( is_string( $function['function'][0] ) )
							$output .= $function['function'][0] . ' :: ' . $function['function'][1];
						
						else
							$output .= print_r( $function, TRUE );
						
						$output .= '</code> (Accepted args: ' . $function['accepted_args'] . ') </li>';
					}
					self::$wp_func ++;
				}
				$output .=  '</ul></li>';
			}
		
			$output .=  '</ul></li></ul>';
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
} // end if class exists
