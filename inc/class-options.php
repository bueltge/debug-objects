<?php
 /**
 * Return all options items from options-table
 * 
 * @package     Debug Objects
 * @subpackage  options content
 * @author      Frank Bültge
 * @since       03/06/2014
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Options' ) )
	return NULL;

class Debug_Objects_Options {
	
	/**
	 * The class object
	 * 
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;
	
	protected $autoload_mu_options = array();
	
	/**
	 * List of options entries.
	 *
	 * @type array
	 */
	protected $autoload_options = array();
	
	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 * @return  $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object )
			self::$class_object = new self;
		
		return self::$class_object;
	}
	
	/**
	 * Init function to register all used hooks
	 * 
	 * @since   09/25/2013
	 * @return  void
	 */
	public function __construct() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
		
		$this->get_options();
		
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
			'tab' => __( 'Options' ),
			'function' => array( $this, 'show_options' )
		);
		
		return $tabs;
	}
	
	public function get_options() {
		global $wpdb;
		
		if ( is_multisite() )
			$this->autoload_mu_options = $wpdb->get_results(
				"SELECT option_id, option_name, option_value, autoload FROM " . $wpdb->base_prefix . "options WHERE autoload = 'yes'"
			);
		
		$this->autoload_options = $wpdb->get_results(
			"SELECT option_id, option_name, option_value, autoload FROM $wpdb->options WHERE autoload = 'yes'"
		);
		//$this->options = wp_load_alloptions();
		
	}
	
	public function show_options() {
		
		echo '<ul><li><a href="#multisite">Multisite Options</a></li><li><a href="#site">Site Options</a></li></ul>';
		
		echo '<hr />';
		
		if ( is_multisite() ) {
			echo '<h4 id="multisite">Multisite Options</h4>';
			pre_print( $this->autoload_mu_options );
		}
		
		echo '<h4 id="site">Site Options</h4>';
		pre_print( $this->autoload_options );
	}
}