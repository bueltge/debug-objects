<?php
/**
 * Wrapper for HTML Inspector Tool
 * HTML Inspector is a code quality tool to help you and your team write better markup.
 * @see         https://github.com/philipwalton/html-inspector
 * 
 * @package     Debug Objects
 * @subpackage  HTML Inspector
 * @author      Frank B&uuml;ltge
 * @since       01/03/2014
 * @version     01/03/2014
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Html_Inspector' ) ) {
	class Debug_Objects_Html_Inspector extends Debug_Objects {
		
		protected static $classobj = NULL;
		
		private static $handle = 'html-inspector';
		
		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return \Debug_Objects_Html_Inspector|null $classobj
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
			
			if ( is_admin() )
				return;
			
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		/**
		 * Load inspector javascript
		 * 
		 * @since   01/03/2014
		 * @return  void
		 */
		public function enqueue_script() {
			
			wp_register_script( self::$handle,
				plugins_url( 'html-inspector/', __FILE__ ) . 'html-inspector.js'
			);
			wp_register_script( 'run-' . self::$handle,
				plugins_url( 'html-inspector/', __FILE__ ) . 'run-html-inspector.js',
				array( 'jquery', self::$handle )
			);
			wp_enqueue_script( 'run-' . self::$handle );
		}
		
		/**
		 * Create new tab for information about the plugin in the plugin tab list
		 * 
		 * @since   01/03/2014
		 * @param   array $tabs
		 * @return  array $tabs
		 */
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'HTML Inspector', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_html_errors' )
			);
			
			return $tabs;
		}
		
		/**
		 * Greate a output to easier to read without console
		 * 
		 * @since   01/03/2014
		 * @return  String $output
		 */
		public function get_html_errors() {
			
			$output = '<h4>HTML Inspector Error Feedback</h4>';
			
			echo $output;
		}
		
	} // end class
}