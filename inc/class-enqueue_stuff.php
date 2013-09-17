<?php
/**
 * Add small screen with informations about enqueued scripts and style in WP
 *
 * @package     Debug Objects
 * @subpackage  Enqueued Scripts and Stylesheets
 * @author      Frank B&uuml;ltge
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
				'tab' => __( 'Scripts & Styles', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_enqueued_stuff' )
			);
			
			return $tabs;
		}
		
		public function get_enqueued_stuff() {
			global $wp_scripts, $wp_styles;
			?>
			
			<table>
				<tr>
					<th colspan="4"><strong>Enqueued Scripts</strong></th>
				</tr>
				<tr>
					<th>Order</th>
					<th>Loaded</th>
					<th>Dependencies</th>
					<th>Path</th>
				</tr>
			<?php
			$class = '';
			$i = 1;
			foreach ( $wp_scripts->do_items() as $loaded_scripts ) {
					
				$class = ( $i % 2 === 0 ) ? '' : ' class="alternate"';
				echo '<tr' . $class . '>';
				echo '<td>' . $i . '</td>';
				echo '<td>' . $loaded_scripts . '</td>';
				echo '<td>';
				echo ( count( $wp_scripts->registered[$loaded_scripts]->deps ) > 0 ) ? implode( __( 'and' ), $wp_scripts->registered[$loaded_scripts]->deps ) : '';
				echo '</td>';
				echo '<td>' . $wp_scripts->registered[$loaded_scripts]->src . '</td>';
				echo '</tr>' . "\n";
				
				$i++;
			}
			?>
			</table>
			
			<table>
				<tr>
					<th colspan="4"><strong>Enqueued Styles</strong></th>
				</tr>
				<tr>
					<th>Order</th>
					<th>Loaded</th>
					<th>Dependencies</th>
					<th>Path</th>
				</tr>
			
			<?php
			$class = '';
			$i = 1;
			foreach ( $wp_styles->do_items() as $loaded_styles ) {
				
				$class = ( $i % 2 === 0 ) ? '' : ' class="alternate"';
				echo '<tr' . $class . '>';
				echo '<td>' . $i . '</td>';
				echo '<td>' . $loaded_styles . '</td>';
				echo '<td>';
				echo ( count( $wp_styles->registered[$loaded_styles]->deps ) > 0 ) ? implode( __( 'and'  ), $wp_styles->registered[$loaded_styles]->deps ) : '';
				echo '</td>';
				echo '<td>' . $wp_styles->registered[$loaded_styles]->src . '</td>';
				echo '</tr>' . "\n";
				
				$i++;
			}
			?>
			</table>
			
			<?php
		}
		
	} // end class
}// end if class exists