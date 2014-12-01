<?php
/**
 * Return different information about translation files
 *
 * Kudos to Toscho <info@toscho.de> for the idea and code resource
 *
 * @package     Debug Objects
 * @subpackage  Translation Information
 * @author      Frank Bültge
 * @since       09/24/2013
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Screen_Info' ) ) {
	return NULL;
}

class Debug_Objects_Translation {

	/**
	 * The class object
	 *
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * List of log entries.
	 *
	 * @type array
	 */
	protected $log = array();

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
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
	 * @since   09/25/2013
	 * @return \Debug_Objects_Translation
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return NULL;
		}

		add_filter( 'override_load_textdomain', array( $this, 'log_file_load' ), 10, 3 );

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
			'tab'      => __( 'Translation' ),
			'function' => array( $this, 'show_log' )
		);

		return $tabs;
	}

	/**
	 * Store log data.
	 *
	 * Kudos to
	 * @author  Toscho <info@toscho.de>
	 * @wp-hook override_load_textdomain
	 * @since   09/25/2013
	 *
	 * @param   bool   $false  FALSE, passed though
	 * @param   string $domain Text domain
	 * @param   string $mofile Path to file.
	 *
	 * @return  bool
	 */
	public function log_file_load( $false, $domain, $mofile ) {

		// DEBUG_BACKTRACE_IGNORE_ARGS is available since 5.3.6
		if ( version_compare( PHP_VERSION, '5.3.6' ) >= 0 ) {
			$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		} else {
			$trace = debug_backtrace();
		}

		$this->log[ ] = array(
			'caller' => $trace[ 4 ], // entry 4 is the calling file
			'domain' => $domain,
			'mofile' => $mofile,
			'found'  => file_exists( $mofile ) ? round( filesize( $mofile ) / 1024, 2 ) : FALSE
		);

		return $false;
	}

	/**
	 * Create Array with data, content and markup
	 *
	 * @since   09/25/2013
	 * @return Array
	 */
	protected function get_log() {

		$logs = $this->log;

		if ( empty ( $logs ) ) {
			return array( 'No MO file loaded or logged.' );
		}

		$out = array();

		foreach ( $logs as $log ) {

			if ( $log[ 'found' ] ) {
				$found = $log[ 'found' ] . __( 'kb' );
			} else {
				$found = __( '<strong>Not found!</strong>' );
			}

			if ( isset( $log[ 'caller' ][ 'file' ] ) ) {
				$file = __( 'Called in:' ) . ' <code>' . $log[ 'caller' ][ 'file' ] . '</code> ';
			} else {
				$file = '';
			}
			if ( isset( $log[ 'caller' ][ 'line' ] ) ) {
				$line = __( 'line' ) . ' <em>' . $log[ 'caller' ][ 'line' ] . '</em> ';
			} else {
				$line = '';
			}
			if ( $log[ 'caller' ][ 'function' ] ) {
				$function = __( 'via Function' ) . ' <code>' . $log[ 'caller' ][ 'function' ] . '</code>';
			} else {
				$function = '';
			}

			$out[ $log[ 'domain' ] ] = __( 'Domain:' ) . ' <code>' . $log[ 'domain' ] . '</code><br>' .
			                           __( 'File:' ) . ' <code>' . $log[ 'mofile' ] . '</code> (' . $found . ')<br>' .
			                           $file . $line . $function;
		}

		return $out;
	}

	/**
	 * Output the data
	 *
	 * @since   09/25/2013
	 * @return  String
	 */
	public function show_log() {

		$class  = '';
		$output = '';

		foreach ( $this->get_log() as $key => $value ) {

			$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';

			$output .= '<li' . $class . '>' . $value . '</li>';
		}

		echo '<h4>' . __( 'Translations' ) . ' (' . count( $this->get_log() ) . ')</h4>';
		echo '<p>' . __( 'Locale:' ) . ' ' . esc_html( get_locale() ) . "</p>\n";
		echo '<ul>' . $output . '</ul>';
	}

}