<?php
/**
 * Add small screen with informations about enqueued scripts and style in WP
 *
 * @package     Debug Objects
 * @subpackage  Enqueued Scripts and Stylesheets
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Enqueue_Stuff' ) ) {
	class Debug_Objects_Enqueue_Stuff extends Debug_Objects {
		
		protected static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return Debug_Objects_Enqueue_Stuff|null $classobj
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
				'tab' => __( 'Scripts & Styles', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_enqueued_stuff' )
			);
			
			return $tabs;
		}

		/**
		 * Get all stuff on enqueued scripts and styles
		 *
		 */
		public function get_enqueued_stuff() {
			global $wp_scripts, $wp_styles;
			
			// Get settings
			$options = Debug_Objects_Settings::return_options();
			
			/**
			 * Get all enqueue scripts
			 * Current is do_items() not usable, echo all scripts
			 * 
			 * @see https://github.com/bueltge/Debug-Objects/issues/22#issuecomment-24728637
			 */
			//$loaded_scripts = $wp_scripts->do_items();
			$wp_scripts->all_deps( $wp_scripts->queue );
			$loaded_scripts = $wp_scripts->to_do;
			
			// Get all enqueue styles
			$loaded_styles  = $wp_styles->do_items();
			?>
			
			<h4><?php _e( 'Enqueued Scripts' ); ?></h4>
			<table class="tablesorter">
				<thead>
					<tr>
						<th>Order</th>
						<th>Loaded</th>
						<th>Dependencies</th>
						<th>Path</th>
						<th>Version</th>
					</tr>
				</thead>
			<?php
			$class = '';
			$i = 1;
			foreach ( $loaded_scripts as $loaded_script ) {
				
				// Filter scripts from this plugin, not helpful
				if (
					isset( $options[ 'filter' ] ) 
					&& '1' === $options[ 'filter' ] 
					&& 'debug_objects' === substr( $loaded_script, 0, 13 )
					)
					break;
				
				$class = ( $i % 2 === 0 ) ? '' : ' class="alternate"';
				$deps = $wp_scripts->registered[$loaded_script]->deps;
				$dependencies = ( count( $deps ) > 0 ) ? implode( __( ', ' ), $deps ) : '';
				echo '<tr' . $class . '>';
				echo '<td>' . $i . '</td>';
				echo '<td>' . esc_html( $loaded_script ) . '</td>';
				echo '<td>' . esc_html( $dependencies ) . '</td>';
				echo '<td>' . esc_html( $wp_scripts->registered[$loaded_script]->src ) . '</td>';
				echo '<td>' . esc_html( $wp_scripts->registered[$loaded_script]->ver ) . '</td>';
				echo '</tr>' . "\n";
				
				$i++;
			}
			?>
			</table>
			
			<h4><?php _e( 'Enqueued Styles' ); ?></h4>
			<table class="tablesorter">
				<thead>
					<tr>
						<th>Order</th>
						<th>Loaded</th>
						<th>Dependencies</th>
						<th>Path</th>
						<th>Version</th>
					</tr>
				</thead>
				
			<?php
			$class = '';
			$i = 1;
			foreach ( $loaded_styles as $loaded_style ) {
				
				// Filter scripts from this plugin, not helpful
				if (
					isset( $options[ 'filter' ] ) 
					&& '1' === $options[ 'filter' ] 
					&& 'debug_objects' === substr( $loaded_style, 0, 13 )
					)
					break;
				
				$class = ( $i % 2 === 0 ) ? '' : ' class="alternate"';
				$deps = $wp_styles->registered[$loaded_style]->deps;
				$dependencies = ( count( $deps ) > 0 ) ? implode( __( ', ' ), $deps ) : '';
				echo '<tr' . $class . '>';
				echo '<td>' . $i . '</td>';
				echo '<td>' . esc_html( $loaded_style ) . '</td>';
				echo '<td>' . esc_html( $dependencies ) . '</td>';
				echo '<td>' . esc_html( $wp_styles->registered[$loaded_style]->src ) . '</td>';
				echo '<td>' . esc_html( $wp_styles->registered[$loaded_style]->ver ) . '</td>';
				echo '</tr>' . "\n";
				
				$i++;
			}
			?>
			</table>
			
			<?php
		}
		
	} // end class
}// end if class exists