<?php
/**
 * Add informations about theme and templates
 *
 * @package     Debug Objects
 * @subpackage  theme and template informations
 * @author      Frank Bültge
 * @since       2.0.3
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Theme' ) ) {
	class Debug_Objects_Theme extends Debug_Objects {
		
		protected static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return Debug_Objects_Theme|null $classobj
		 */
		public static function init() {
			
			NULL === self::$classobj and self::$classobj = new self();
			
			return self::$classobj;
		}
		
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
			add_action( 'admin_footer', array( $this, 'get_list_ids' ), 9999 );
			add_action( 'wp_footer', array( $this, 'get_list_ids' ), 9999 );
		}
		
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Theme & Plugins', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_theme_data' )
			);
			
			return $tabs;
		}
		
		public function get_theme_data( $echo = TRUE ) {
			
			$output  = '';
			
			if ( isset( $GLOBALS['template'] ) ) {
				$template = $GLOBALS['template'];
				if ( is_trackback() ) {
					$temp = ABSPATH . 'wp-trackback.php';
				} else if ( is_404() && $template == get_404_template() ) {
					$temp = $template;
				} else if ( is_search() && $template == get_search_template() ) {
					$temp = $template;
				} else if ( is_tax() && $template == get_taxonomy_template()) {
					$temp = $template;
				} else if ( is_home() && $template == get_home_template() ) {
					$temp = $template;
				} else if ( is_attachment() && $template == get_attachment_template() ) {
					$temp = $template;
				} else if ( is_single() && $template == get_single_template() ) {
					$temp = $template;
				} else if ( is_page() && $template == get_page_template() ) {
					$temp = $template;
				} else if ( is_category() && $template == get_category_template()) {
					$temp = $template;
				} else if ( is_tag() && $template == get_tag_template()) {
					$temp = $template;
				} else if ( is_author() && $template == get_author_template() ) {
					$temp = $template;
				} else if ( is_date() && $template == get_date_template() ) {
					$temp = $template;
				} else if ( is_archive() && $template == get_archive_template() ) {
					$temp = $template;
				} else if ( is_comments_popup() && $template == get_comments_popup_template() ) {
					$temp = $template;
				} else if ( is_paged() && $template == get_paged_template() ) {
					$temp = $template;
				} else if ( is_tag() && $template == get_tag_template() ) {
					$temp = $template;
				} else if ( is_tax() && $template == get_taxonomy_template() ) {
					$temp = $template;
				} else if ( file_exists( TEMPLATEPATH . '/index.php' ) ) {
					$temp = TEMPLATEPATH . '/index.php' ;
				}
			}
			
			$theme_data = array();
			$theme_data = wp_get_theme( get_stylesheet_directory() . '/style.css' );
			
			$output .=  "\n" . '<h4>' . __( 'Theme Values', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
				$output .= '<li class="alternate">' . __( 'Current theme name:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['Name'] != '' )
					$output .= $theme_data['Name'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li>' . __( 'Current theme title:', parent :: get_plugin_data() ) . ' ' . $theme_data['Title'] . '</li>';
				
				$output .= '<li class="alternate">' . __( 'Current theme uri:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['URI'] != '' )
					$output .= $theme_data['URI'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li>' . __( 'Current theme description:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['Description'] != '' )
					$output .= $theme_data['Description'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li class="alternate">' . __( 'Current theme author:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['Author'] != '' )
					$output .= $theme_data['Author'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li>' . __( 'Current theme version:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['Version'] != '' )
					$output .= $theme_data['Version'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li class="alternate">' . __( 'Current theme template:', parent :: get_plugin_data() ) . ' ';
				if ( $theme_data['Template'] != '' )
					$output .= $theme_data['Template'];
				else
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				$output .= '</li>';
				
				$output .= '<li>' . __( 'Current theme status:', parent :: get_plugin_data() ) . ' ' . $theme_data['Status'] . '</li>';
				$output .= '<li class="alternate">' . __( 'Current theme tags:', parent :: get_plugin_data() ) . ' ';
				if ( isset($theme_data['Tags'][0]) && $theme_data['Tags'][0] != '' ) {
					$output .= join( ', ', $theme_data['Tags']);
				} else {
					$output .= __( 'Undefined', parent :: get_plugin_data() );
				}
				$output .= '</li>';
				
				$output .= '<li>' . __( 'Current theme:', parent :: get_plugin_data() ) . ' ' . get_template() . '</li>';
				$output .= '<li class="alternate">' . __( 'Current theme directory:', parent :: get_plugin_data() ) . ' ' . get_template_directory() . '</li>';
				$output .= '<li>' . __( 'Current stylesheet:', parent :: get_plugin_data() ) . ' ' . get_stylesheet() . '</li>';
				$output .= '<li class="alternate">' . __( 'Current stylesheet directory:', parent :: get_plugin_data() ) . ' ' . get_stylesheet_directory() . '</li>';
			$output .= '</ul>' . "\n";
			
			$output .=  "\n" . '<h4>' . __( 'Current template', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
				$output .= '<li class="alternate">' . __( 'Current template path, constant', parent :: get_plugin_data() ) . ' <code>TEMPLATEPATH</code>: ' . TEMPLATEPATH . '</li>';
				$output .= '<li>' . __( 'Current stylesheet path, constant', parent :: get_plugin_data() ) . ' <code>STYLESHEETPATH</code>: ' . STYLESHEETPATH . '</li>';
				if ( isset($template) && $template )
					$output .= '<li class="alternate">' . __( 'Current template file path' ) . $template . '</li>';
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'Active Plugins' ) . '</h4>' . "\n";
			$output .= $this->get_active_plugins( FALSE );
			
			$output .=  "\n" . '<h4>' . __( 'Registered IDs, like Sidebar, Admin Bar etc.' ) . '</h4>' . "\n";
			$output .= '<div id="register_ids"></div>' . "\n";
			
			if ( $echo )
				echo $output;

			return $output;
		}
		
		public function get_active_plugins( $echo = TRUE ) {
			
			$all_plugins    = get_plugins();
			$active_plugins = get_option( 'active_plugins', array() );
			$output         = '';
			
			$class = '';
			foreach ( $all_plugins as $plugin_path => $plugin ) {
				
				// Only show active plugins
				if ( in_array( $plugin_path, $active_plugins ) ) {
					
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
					
					$output .= '<li' . $class . '>' . $plugin['Name'] . ', Version: ' . $plugin['Version'] . "\n";
			
					if ( isset( $plugin['PluginURI'] ) )
						$output .= ', PluginURI: ' . $plugin['PluginURI'] . "\n";
					
					$output .= "</li>\n";
				}
			}
			
			$output = '<ul>' . $output . '</ul>';
			
			if ( $echo )
				echo $output;

			return $output;
		}
		
		public function get_list_ids() {
			?>
			<script>
				var els = [];
				jQuery( '[id]' ).each( function () {
					els.push( this.id );
				} );
				els.sort();
				var ids = '#' + els.join( '<br />#' );
				jQuery( '#register_ids' ).html( ids );
			</script>
			<?php
		}
		
	} // end class
}// end if class exists
