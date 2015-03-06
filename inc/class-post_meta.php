<?php
/**
 * Return all meta data to a post
 *
 * @package     Debug Objects
 * @subpackage  About plugin
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Post_Meta' ) ) {
	class Debug_Objects_Post_Meta extends Debug_Objects {
		
		protected static $classobj = NULL;
		
		public static $builtin = array();
	 
		public static $args = array();

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return Debug_Objects_Post_Meta|null $classobj
		 */
		public static function init() {
			
			NULL === self::$classobj and self::$classobj = new self();
			
			return self::$classobj;
		}
	
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			// get arguments of CPTs
			add_action( 'registered_post_type', array( $this, 'get_args' ), 10, 2 );
			// add tab to the plugin tabs list
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
				'tab' => __( 'Post Type & Meta', parent::get_plugin_data() ),
				'function' => array( $this, 'get_post_meta_data' )
			);
			
			return $tabs;
		}

		/**
		 * Get arguments of CPTs, write in var
		 *
		 * @param $post_type
		 * @param $args
		 */
		public function get_args( $post_type, $args ) {
			
			! $args->_builtin
				&& self::$args[ $post_type ] = $args;
		}

		/**
		 * Get data and create output
		 *
		 * @param  bool $echo
		 *
		 * @return String $output
		 */
		public function get_post_meta_data( $echo = TRUE ) {
			
			$output = '';
			
			// Post Meta data
			if ( ! isset( $GLOBALS['post']->ID ) ) {
				$output = __( 'No Post ID' );
				if ( $echo )
					echo $output;
				else
					return $output;
				
				return NULL;
			}
			
			$meta = get_post_custom( get_the_ID() );
			if ( ! isset( $meta ) )
				$output .= __( 'No meta data' );
			
			$output .= '<h4 class="alternate">' . __( 'Post ID:' ) . ' ' . get_the_ID() . '</h4>';
			$output .= '<ul>';
			$output .= '<li>' . __( 'Meta Keys:' ) . ' ' . count( $meta ) . '';
			$output .= '<table><tr><td>' . __( 'Key' ) . '</td><td>' . __( 'Value' ) . '</td></tr>';
			foreach ( $meta as $key => $value ) {
				$valuecount = count( $value );
				if ( 1 == $valuecount )
					$valuecount = $valuecount . ' ' . __( 'value' );
				else
					$valuecount = $valuecount . ' ' . __( 'values' );
				
				$export = var_export( get_post_meta( get_the_ID(), $key, FALSE ), TRUE );
				$escape = htmlspecialchars( $export, ENT_QUOTES, 'utf-8', FALSE );
				
				$output .= '<tr>';
				$output .= '<td><code>' . $key . '</code><br>' . $valuecount;
				$output .= '</td>';
				$output .= '<td><pre>' . $escape;
				$output .= '</pre></td>';
				$output .= '</tr>';
			}
			$output .= '';
			$output .= '</table>';
			$output .= '</li>';
			$output .= '<li class="alternate">' . __( 'Approximate Disk Size:' ) 
				. ' ' . $this->get_string_disk_size( serialize( $meta ) );
			$output .= '</ul>';
			
			// post type arguments
			if ( isset( self::$args ) ) {
				foreach ( self::$args as $post_type => $var ) {
					$export = var_export( $var, TRUE );
					$escape = htmlspecialchars( $export, ENT_QUOTES, 'utf-8', FALSE );
					
					$output .= sprintf(
						'<br><h4 class="alternate">Post Type: %s</h4><pre>%s</pre>',
						$post_type,
						$escape
					);
				}
			} else {
				$output .= __( 'No custom post type arguments' );
			}
			
			if ( $echo )
				echo $output;

			return $output;
		}
		
		public function get_string_disk_size( $string ) {
			
			$size = mb_strlen( $string, DB_CHARSET );
			if ( $size >= 1024 )
				$size = round( $size / 1024, 2 ) . ' KB';
			else
				$size = $size.' bytes';
			
			return $size;
		}
		
	} // end class
}// end if class exists