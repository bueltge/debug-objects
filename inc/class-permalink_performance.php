<?php
/**
 * Get value for the permalink rules for performance checks
 *
 * Kudos to Eric Mann and his post about this since WP 4.0
 *
 * @see         https://eamann.com/tech/permalink-performance/#note-6783-1
 *
 * @package     Debug Objects
 * @subpackage  Permalink Performace
 * @author      Frank Bültge <frank@bueltge.de>
 * @since       2014-12-01
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Permalink_Performance' ) ) {
	return NULL;
}

/**
 * Class Debug_Objects_Permalink_Performance
 */
class Debug_Objects_Permalink_Performance {

	/**
	 * The class object
	 *
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * Save time values.
	 *
	 * @var integer
	 */
	protected $query_timer = 0;

	/**
	 * Load the object and get the current state
	 *
	 * @since   2014-12-01
	 * @return String $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since   2014-12-01
	 * @return \Debug_Objects_Permalink_Performance
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return NULL;
		}

		$this->query_timer = 0;

		// Start timer
		add_filter( 'do_parse_request', array( $this, 'set_timer' ) );
		// Stop and get value
		add_action( 'parse_request', array( $this, 'get_timer' ) );

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Create tab for this data
	 *
	 * @param  Array $tabs
	 *
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[ ] = array(
			'tab'      => __( 'Permalink Performance' ),
			'function' => array( $this, 'get_formated_result' )
		);

		return $tabs;
	}

	/**
	 * Set timestamp to var
	 *
	 * @param $parse
	 *
	 * @return mixed
	 */
	public function set_timer( $parse ) {

		$this->query_timer = microtime( TRUE );

		return $parse;
	}

	/**
	 * Get time line
	 *
	 * @return mixed
	 */
	public function get_timer() {

		$time = microtime( TRUE ) - $this->query_timer;

		return $time;
	}

	/**
	 * Format the value and add background information
	 *
	 * @param bool $echo
	 *
	 * @return integer $time
	 */
	public function get_formated_result( $echo = FALSE ) {

		$time = $this->get_timer();

		if ( is_admin() )
			$time = 'Not usable in Admin area.';

		if ( $echo ) {
			return $time;
		}

		$view = '';
		$view .= '<p>WordPress does all of the conversion from URLs to parameters for WP_Query in the <code>parse_request()</code> method of the global <code>wp</code> object. This is a simple timer at the beginning of the function’s execution and check the value of that timer against the current system time when the function is complete and get it on the next line.</p>';
		if ( is_float( $time ) ) {
			$view .= '<p><strong>' . 'Time line (extracted): ' . substr( $time, 0, 5 ) . 's</strong> / ' . substr(
					$time * 1000, 0, 5
				) . 'ms' . '</p>';;
			$view .= '<p><strong>' . 'Time line: ' . $time . 's</strong> / ' . $time * 1000 . 'ms' . '</p>';
		} else {
			$view .= '<p><strong>' . $time . '</p>';
		}

		echo $view;
	}

} // end class