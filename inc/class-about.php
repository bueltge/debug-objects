<?php
/**
 * Add small screen with information about the plugin
 *
 * @package     Debug Objects
 * @subpackage  About plugin
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-20
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_About extends Debug_Objects {

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_About|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj && self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		Debug_Objects::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ), 99 );
	}

	/**
	 * Create new tab for information about the plugin in the plugin tab list
	 *
	 * @param   array $tabs
	 *
	 * @return  array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'About', 'debug_objects' ),
			'function' => array( $this, 'get_plugin_content' )
		);

		return $tabs;
	}

	/**
	 * Get information from plugin to easy red on the tab content
	 *
	 * @param   bool   $echo
	 *
	 * @return  string $output
	 */
	public function get_plugin_content( $echo = TRUE ) {

		$output = '';
		$output .= '<h3>' . parent::get_plugin_data( 'Title' ) . '</h3>';
		$output .= '<p>';
		$output .= '<strong>' . esc_attr__( 'Description:', 'debug_objects' ) . '</strong> ';
		$output .= parent::get_plugin_data( 'Description' ) . '</p>';
		$output .= '<p>';
		$output .= '<strong>' . esc_attr__( 'Version:', 'debug_objects' ) . '</strong> ';
		$output .= parent::get_plugin_data( 'Version' ) . '</p>';

		$output .= '<p><strong>' . esc_attr__( 'Here\'s how you can give back:', 'debug_objects' ) . '</strong></p>';
		$output .= '<ul>';
		$output .= '<li><a href="http://wordpress.org/extend/plugins/debug-objects/" title="'
		           . esc_attr__( 'The Plugin on the WordPress plugin repository', 'debug_objects' )
		           . '">' . esc_attr__( 'Give the plugin a good rating.', 'debug_objects' ) . '</a></li>';
		$output .= '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6069955" title="'
		           . esc_attr__( 'Donate via PayPal', 'debug_objects' )
		           . '">' . esc_attr__( 'Donate a few euros.', 'debug_objects' ) . '</a></li>';
		$output .= '<li><a href="http://www.amazon.de/gp/registry/3NTOGEK181L23/ref=wl_s_3" title="'
		           . esc_attr__( 'Frank Bültge\'s Amazon Wish List', 'debug_objects' )
		           . '">' . esc_attr__( 'Get me something from my wish list.', 'debug_objects' ) . '</a></li>';
		$output .= '<li><a href="https://github.com/bueltge/Debug-Objects" title="'
		           . esc_attr__( 'Please give me feedback, contribute and file technical bugs on this GitHub Repo, use Issues.',
		                         'debug_objects' ) . '">'
		           . esc_attr__( 'Github Repo for Contribute, Issues & Bugs',
		                         'debug_objects' ) . '</a></li>';
		$output .= '</ul>';

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

} // end class
