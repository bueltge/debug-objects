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
	
	class Debug_Objects_Conditional_Tags {
		
		protected static $classobj = NULL;
		
		public $template_storage = '';
		
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
			
			add_filter( 'template_include', array( $this, 'get_include_template' ) );
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Conditional Tags' ),
				'function' => array( $this, 'get_conditional_tags' )
			);
			
			return $tabs;
		}
		
		/**
		 * Return the current use template
		 * 
		 * @param  $template
		 * @return $template
		 */
		public function get_include_template( $template ) {
			
			$this->template_storage = $template;
			
			return $template;
		}
		
		public function get_conditional_tags( $echo = TRUE ) {
			global $post_type, $post_id;
			
			$_post = get_post($post_id);
			
			$is     = '';
			$is_not = '';
			
			$is .= '<h4>Current Template</h4>';
			$is .= '<code>' . $this->template_storage . '</code>';
			$is .= '<h4><a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a></h4>' . "\n";
			$is .= '<p>' . __( 'The Conditional Tags can be used in your Template files to change what content is displayed and how that content is displayed on a particular page depending on what conditions that page matches. You see on this view the condition of all possible tags.' ) . '</p>' . "\n";
			$is .= '<ul>' . "\n";
			
			if ( is_network_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_network_admin" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> network_admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> network_admin</li>' . "\n";
			
			if ( is_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_admin" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> admin</li>' . "\n";
		
			if ( is_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_archive" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> archive</li>' . "\n";
		
			if ( is_attachment() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_attachment" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> attachment</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> attachment</li>' . "\n";
		
			if ( is_author() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_author" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> author</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> author</li>' . "\n";
		
			if ( is_category() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_category" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> category</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> category</li>' . "\n";
		
			if ( is_tag() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_tag" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> tag</li>' . "\n";
			
			if ( is_tax() ) $is .= "\t" . '<li><a href="http://codex.wordpress.org/Function_Reference/is_tax" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> tax</li>' . "\n";
			
			if ( is_comments_popup() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_comments_popup" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> comments_popup</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> comments_popup</li>' . "\n";
		
			if ( is_date() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_date" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> date</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> date</li>' . "\n";
			
			if ( is_day() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_day" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> day</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> day</li>' . "\n";
		
			if ( is_feed() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_feed" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> feed</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> feed</li>' . "\n";
			
			if ( is_front_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_front_page" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> front_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> front_page</li>' . "\n";
			
			if ( is_home() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_home" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> home</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> home</li>' . "\n";
			
			if ( is_month() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_month" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> month</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> month</li>' . "\n";
		
			if ( is_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> page</li>' . "\n";
		
			if ( is_paged() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_paged" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> paged</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> paged</li>' . "\n";
			
			/* Deprecated in WP 3.1
			if ( is_plugin_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_plugin_page" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> plugin_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> plugin_page</li>' . "\n";
			*/
			if ( is_preview() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_preview" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> preview</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> preview</li>' . "\n";
		
			if ( is_robots() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_robots" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> robots</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> robots</li>' . "\n";
		
			if ( is_search() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_search" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> search</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> search</li>' . "\n";
		
			if ( is_single() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_single" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> single</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> single</li>' . "\n";
		
			if ( is_singular() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_singular" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> singular</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> singular</li>' . "\n";
			
			if ( is_post_type_hierarchical( $post_type ) ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_post_type_hierarchical" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> post_type_hierarchical</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> post_type_hierarchical</li>' . "\n";
			
			if ( is_post_type_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_post_type_archive" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> is_post_type_archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> is_post_type_archive</li>' . "\n";
			
			if ( isset( $_post ) && comments_open() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/comments_open" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> comments_open</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> comments_open</li>' . "\n";
			
			if ( isset( $_post ) && pings_open() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/pings_open" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> pings_open</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> pings_open</li>' . "\n";
			
			if ( ! is_admin() && is_sticky() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_sticky" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> sticky</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> sticky</li>' . "\n";
		
			if ( is_time() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_time" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> time</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> time</li>' . "\n";
		
			if ( is_trackback() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_trackback" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> trackback</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> trackback</li>' . "\n";
		
			if ( is_year() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_year" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> year</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> year</li>' . "\n";
		
			if ( is_404() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_404" title="' . __( 'Documentation in Codex' ) . '"><b>' . __( 'is' ) . '</b> 404</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . '</i> 404</li>' . "\n";
		
			$is .= $is_not;
			
			$is .= '</ul>' . "\n";
			
			if ( $echo )
				echo $is;
			else
				return $echo;
		}
		
	} // end class
}// end if class exists