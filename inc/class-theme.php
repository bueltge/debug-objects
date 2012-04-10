<?php
/**
 * Add informations about theme and templates
 *
 * @package     Debug Objects
 * @subpackage  theme and template informations
 * @author      Frank B&uuml;ltge
 * @since       2.0.3
 */

if ( ! class_exists( 'Debug_Objects_Theme' ) ) {
	
	class Debug_Objects_Theme extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Theme', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_theme_data' )
			);
			
			return $tabs;
		}
		
		public static function get_theme_data( $echo = TRUE ) {
			
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
			if ( function_exists( 'wp_get_theme' ) )
				$theme_data = wp_get_theme( get_stylesheet_directory() . '/style.css' );
			else
				$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
			
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
					$output .= '<li class="alternate">' . $template . '</li>';
			$output .= '</ul>' . "\n";
			
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists
