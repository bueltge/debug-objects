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
				<tr><th colspan="3"><h4>Enqueued Scripts</h4></th></tr>
				<tr><th>Order</th><th>Loaded</th><th>Dependencies</th><th>Path</th></tr>
			<?php
			$i = 1;
			foreach ( $wp_scripts->do_items() as $loaded_scripts ) {
				echo '<tr',  ( $i % 2 === 0 ) ? '' : ' class="alternate"' , '><td>', $i, '<td>', $loaded_scripts, '</td><td>', ( count( $wp_scripts->registered[$loaded_scripts]->deps ) > 0 ) ? implode( " and ", $wp_scripts->registered[$loaded_scripts]->deps ) : '', '</td><td>', $wp_scripts->registered[$loaded_scripts]->src , '</td></tr>', "\n";
				$i++;
			}
			?>
				<tr><th colspan="3"><h4>Enqueued Styles</h4></th></tr>
				<tr><th>Order</th><th>Loaded</th><th>Dependencies</th><th>Path</th></tr>
				<?php
			
			$i = 1;
			foreach ( $wp_styles->do_items() as $loaded_styles ) {
				echo '<tr', ( $i % 2 === 0 ) ? '' : ' class="alternate"' , '"><td>', $i, '<td>', $loaded_styles, '</td><td>', ( count( $wp_styles->registered[$loaded_styles]->deps ) > 0 ) ? implode( " and ", $wp_styles->registered[$loaded_styles]->deps ) : '', '</td><td>', $wp_styles->registered[$loaded_styles]->src , '</td></tr>', "\n";
				$i++;
			}
			?>
			</table>
			<?php
		}
		
	} // end class
}// end if class exists