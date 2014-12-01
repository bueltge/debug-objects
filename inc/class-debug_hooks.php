<?php
/**
 * Add small screen with informations about hooks of WP
 *
 * @package	    Debug Objects
 * @subpackage  Debug Hooks
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Debug_Hooks' ) )
	return NULL;

	class Debug_Objects_Debug_Hooks extends Debug_Objects {
		
		static public $wp_func = 0;
		
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
			
			self::$wp_func = 0;
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		public function get_conditional_tab( $tabs ) {
			/*
			$tabs[] = array( 
				'tab' => __( 'Debug Live Hooks', parent :: get_plugin_data() ),
				'function' => array( $this, 'list_live_hooks' )
			);
			*/
			$tabs[] = array( 
				'tab' => __( 'Debug Hooks', parent :: get_plugin_data() ),
				'function' => array( $this, 'list_hooks' )
			);
			
			return $tabs;
		}
		
		public function list_hooks( $filter = FALSE ){
			global $wp_filter;
			
			$hooks = $wp_filter;
			ksort( $hooks );
			
			echo '<h2>Hooks Total: ' . count( $wp_filter ) . '</h2>';
			
			foreach( $hooks as $tag => $hook ) {
				if ( FALSE === $filter || FALSE !== strpos( $tag, $filter ) ) {
					self::dump_hook($tag, $hook);
				}
			}
			
			echo '<p class="alternate">Register filter/actions total: ' . self::$wp_func . '</p>';
		}
		
		public function list_live_hooks( $hook = FALSE ) {
			
			if ( FALSE === $hook )
				$hook = 'all';
		
			add_action( $hook, array( $this, 'list_hook_details' ), -1 );
		}
		
		public static function list_hook_details( $input = NULL ) {
			global $wp_filter;

			$tag = current_filter();
			if ( isset( $wp_filter[$tag] ) )
				self::dump_hook( $tag, $wp_filter[$tag] );

			return $input;
		}
		
		public function dump_hook( $tag, $hook, $echo = TRUE ) {
			
			ksort($hook);
			
			// Get settings
			$options = Debug_Objects_Settings::return_options();
			
			// Filter Debug Objects Hooks
			if ( 
				isset( $options[ 'filter' ] ) 
				&& '1' === $options[ 'filter' ] 
				&& Debug_Objects::array_find( 'Debug_Objects', $hook )
				) {
				return NULL;
			}
			
			// Filter hook, files from this plugin, not helpful
			if (
				isset( $options[ 'filter' ] ) 
				&& '1' === $options[ 'filter' ] 
				&& preg_match( '/debug_objects/', $tag )
				)
				return NULL;
			
			$tag = esc_html( $tag );
			
			$output = '<ul><li><strong>' . $tag . '</strong><ul>';

			foreach( $hook as $priority => $functions ) {
				
				$output .= '<li>Priority: ' . $priority . '<ul>';
				
				foreach( $functions as $function ) {
					
					if ( $function['function'] != 'list_hook_details' ) {
						
						$output .= "\t" . '<li>&gt;&gt;&gt;&gt;&gt; <code>';
						
						if ( is_string( $function['function'] ) )
							$output .= $function['function'];
						// @see https://github.com/jeremeamia/super_closure/blob/master/SuperClosure.class.php
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

			return $output;
		}
		
	} // end class
