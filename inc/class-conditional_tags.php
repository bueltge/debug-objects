<?php
/**
 * Add small screen with informations for conditional tags of WP
 *
 * @package     Debug Objects
 * @subpackage  Conditional Tags
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Conditional_Tags' ) ) {
	
	class Debug_Objects_Conditional_Tags extends Debug_Objects {
		
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
				'tab' => __( 'Conditional Tags', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_conditional_tags' )
			);
			
			return $tabs;
		}
		
		public function get_conditional_tags( $echo = TRUE ) {
			
			$is = '';
			$is_not = '';
			
			$is .=  "\n" . '<h4><a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a></h4>' . "\n";
			$is .= '<p>' . __( 'The Conditional Tags can be used in your Template files to change what content is displayed and how that content is displayed on a particular page depending on what conditions that page matches. You see on this view the condition of all possible tags.', parent :: get_plugin_data() ) . '</p>' . "\n";
			$is .= '<ul>' . "\n";
			
			if ( is_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_admin" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> admin</li>' . "\n";
		
			if ( is_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_archive" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> archive</li>' . "\n";
		
			if ( is_attachment() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_attachment" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> attachment</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> attachment</li>' . "\n";
		
			if ( is_author() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_author" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> author</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> author</li>' . "\n";
		
			if ( is_category() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_category" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> category</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> category</li>' . "\n";
		
			if ( is_tag() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_tag" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> tag</li>' . "\n";
			
			if ( is_tax() ) $is .= "\t" . '<li><a href="http://codex.wordpress.org/Function_Reference/is_tax" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> tax</li>' . "\n";
			
			if ( is_comments_popup() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_comments_popup" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> comments_popup</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> comments_popup</li>' . "\n";
		
			if ( is_date() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_date" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> date</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> date</li>' . "\n";
			
			if ( is_day() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_day" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> day</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> day</li>' . "\n";
		
			if ( is_feed() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_feed" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> feed</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> feed</li>' . "\n";
			
			if ( is_front_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_front_page" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> front_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> front_page</li>' . "\n";
			
			if ( is_home() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_home" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> home</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> home</li>' . "\n";
			
			if ( is_month() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_month" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> month</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> month</li>' . "\n";
		
			if ( is_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> page</li>' . "\n";
		
			if ( is_paged() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_paged" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> paged</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> paged</li>' . "\n";
			
			/* Deprecated in WP 3.1
			if ( is_plugin_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_plugin_page" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> plugin_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> plugin_page</li>' . "\n";
			*/
			if ( is_preview() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_preview" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> preview</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> preview</li>' . "\n";
		
			if ( is_robots() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_robots" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> robots</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> robots</li>' . "\n";
		
			if ( is_search() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_search" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> search</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> search</li>' . "\n";
		
			if ( is_single() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_single" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> single</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> single</li>' . "\n";
		
			if ( is_singular() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_singular" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> singular</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> singular</li>' . "\n";
		
			if ( ! is_admin() && is_sticky() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_sticky" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> sticky</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> sticky</li>' . "\n";
		
			if ( is_time() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_time" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> time</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> time</li>' . "\n";
		
			if ( is_trackback() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_trackback" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> trackback</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> trackback</li>' . "\n";
		
			if ( is_year() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_year" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> year</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> year</li>' . "\n";
		
			if ( is_404() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_404" title="' . __( 'Documentation in Codex', parent :: get_plugin_data() ) . '"><b>' . __( 'is', parent :: get_plugin_data() ) . '</b> 404</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', parent :: get_plugin_data() ) . '</i> 404</li>' . "\n";
		
			$is .= $is_not;
			
			$is .= '</ul>' . "\n";
			
			if ( $echo )
				echo $is;
			else
				return $echo;
		}
		
	} // end class
}// end if class exists