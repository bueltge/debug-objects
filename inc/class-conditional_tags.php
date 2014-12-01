<?php
/**
 * Add small screen with information for conditional tags of WP
 *
 * @package     Debug Objects
 * @subpackage  Conditional Tags
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     11/23/2013
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
		 * @return \Debug_Objects_Conditional_Tags|null $classobj
		 */
		public static function init() {
			
			NULL === self::$classobj && self::$classobj = new self();
			
			return self::$classobj;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'template_include', array( $this, 'get_include_template' ) );
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}

		/**
		 * Create new tab for Conditionals on the default tab list
		 *
		 * @param   $tabs
		 * @return  array
		 */
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

		/**
		 * Get the status of each conditional tag
		 *
		 * @param   bool $echo
		 * @return  bool
		 */
		public function get_conditional_tags( $echo = TRUE ) {
			global $post_type, $post_id;
			
			$_post = get_post($post_id);
			
			$is     = '';
			$is_not = '';
			
			$is .= '<h4>Current Template</h4>';
			if ( empty( $this->template_storage ) )
				$this->template_storage = 'Empty.';
			$is .= '<code>' . $this->template_storage . '</code>';
			$is .= '<h4><a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a></h4>' . "\n";
			$is .= '<p>' . __( 'The Conditional Tags can be used in your Template files to change what content is displayed and how that content is displayed on a particular page depending on what conditions that page matches. You see on this view the condition of all possible tags.' ) . '</p>' . "\n";
			$is .= '<ul>' . "\n";
			
			if ( is_network_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_network_admin" title="' . __( 'Documentation in Codex' ) . '">is_network_admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_network_admin</li>' . "\n";
			
			if ( is_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_admin" title="' . __( 'Documentation in Codex' ) . '">is_admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_admin</li>' . "\n";

			if ( is_blog_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_blog_admin" title="' . __( 'Documentation in Codex' ) . '">is_blog_admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_blog_admin</li>' . "\n";

			if ( is_main_network() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_main_network" title="' . __( 'Documentation in Codex' ) . '">is_main_network</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_main_network</li>' . "\n";

			if ( is_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_archive" title="' . __( 'Documentation in Codex' ) . '">is_archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_archive</li>' . "\n";
		
			if ( is_attachment() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_attachment" title="' . __( 'Documentation in Codex' ) . '">is_attachment</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_attachment</li>' . "\n";
		
			if ( is_author() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_author" title="' . __( 'Documentation in Codex' ) . '">is_author</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_author</li>' . "\n";
		
			if ( is_category() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_category" title="' . __( 'Documentation in Codex' ) . '">is_category</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_category</li>' . "\n";
		
			if ( is_tag() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_tag" title="' . __( 'Documentation in Codex' ) . '">is_tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_tag</li>' . "\n";
			
			if ( is_tax() ) $is .= "\t" . '<li><a href="http://codex.wordpress.org/Function_Reference/is_tax" title="' . __( 'Documentation in Codex' ) . '">is_tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_tax</li>' . "\n";
			
			if ( is_comments_popup() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_comments_popup" title="' . __( 'Documentation in Codex' ) . '">is_comments_popup</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_comments_popup</li>' . "\n";
		
			if ( is_date() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_date" title="' . __( 'Documentation in Codex' ) . '">is_date</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_date</li>' . "\n";
			
			if ( is_day() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_day" title="' . __( 'Documentation in Codex' ) . '">is_day</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_day</li>' . "\n";
		
			if ( is_feed() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_feed" title="' . __( 'Documentation in Codex' ) . '">is_feed</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_feed</li>' . "\n";
			
			if ( is_front_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_front_page" title="' . __( 'Documentation in Codex' ) . '">is_front_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_front_page</li>' . "\n";
			
			if ( is_home() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_home" title="' . __( 'Documentation in Codex' ) . '">is_home</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_home</li>' . "\n";

			if ( is_main_site() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_main_site" title="' . __( 'Documentation in Codex' ) . '">is_main_site</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_main_site</li>' . "\n";

			if ( is_month() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_month" title="' . __( 'Documentation in Codex' ) . '">is_month</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_month</li>' . "\n";
		
			if ( is_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page" title="' . __( 'Documentation in Codex' ) . '">is_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_page</li>' . "\n";

			if ( is_page_template() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page_template" title="' . __( 'Documentation in Codex' ) . '">is_page_template</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_page_template</li>' . "\n";

			if ( is_paged() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_paged" title="' . __( 'Documentation in Codex' ) . '">is_paged</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_paged</li>' . "\n";
			
			/* Deprecated in WP 3.1
			if ( is_plugin_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_plugin_page" title="' . __( 'Documentation in Codex' ) . '">is_plugin_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_plugin_page</li>' . "\n";
			*/
			if ( is_preview() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_preview" title="' . __( 'Documentation in Codex' ) . '">is_preview</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_preview</li>' . "\n";

			if ( is_robots() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_robots" title="' . __( 'Documentation in Codex' ) . '">is_robots</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_robots</li>' . "\n";

			if ( is_rtl() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_rtl" title="' . __( 'Documentation in Codex' ) . '">is_rtl</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_rtl</li>' . "\n";

			if ( is_search() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_search" title="' . __( 'Documentation in Codex' ) . '">is_search</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_search</li>' . "\n";
		
			if ( is_single() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_single" title="' . __( 'Documentation in Codex' ) . '">is_single</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_single</li>' . "\n";
		
			if ( is_singular() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_singular" title="' . __( 'Documentation in Codex' ) . '">is_singular</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_singular</li>' . "\n";

			if ( is_ssl() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_ssl" title="' . __( 'Documentation in Codex' ) . '">is_ssl</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_ssl</li>' . "\n";

			if ( is_post_type_hierarchical( $post_type ) ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_post_type_hierarchical" title="' . __( 'Documentation in Codex' ) . '">is_post_type_hierarchical</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_post_type_hierarchical</li>' . "\n";
			
			if ( is_post_type_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_post_type_archive" title="' . __( 'Documentation in Codex' ) . '">is_is_post_type_archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_is_post_type_archive</li>' . "\n";
			
			if ( isset( $_post ) && comments_open() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/comments_open" title="' . __( 'Documentation in Codex' ) . '">is_comments_open</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_comments_open</li>' . "\n";
			
			if ( isset( $_post ) && pings_open() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/pings_open" title="' . __( 'Documentation in Codex' ) . '">is_pings_open</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_pings_open</li>' . "\n";
			
			if ( ! is_admin() && is_sticky() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_sticky" title="' . __( 'Documentation in Codex' ) . '">is_sticky</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_sticky</li>' . "\n";
		
			if ( is_time() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_time" title="' . __( 'Documentation in Codex' ) . '">is_time</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_time</li>' . "\n";
		
			if ( is_trackback() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_trackback" title="' . __( 'Documentation in Codex' ) . '">is_trackback</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_trackback</li>' . "\n";
		
			if ( is_year() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_year" title="' . __( 'Documentation in Codex' ) . '">is_year</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_year</li>' . "\n";
		
			if ( is_404() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_404" title="' . __( 'Documentation in Codex' ) . '">is_404</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no' ) . ' </i>is_404</li>' . "\n";
		
			$is .= $is_not;
			
			$is .= '</ul>' . "\n";
			
			if ( $echo )
				echo $is;

			return $echo;
		}
		
	} // end class

}// end if class exists