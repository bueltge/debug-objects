<?php
/**
 * Add small screen with information for different stuff from php, globals and WP
 *
 * @package     Debug_Objects
 * @subpackage  Debug_Objects_Php
 * @author      Frank Bültge <frank@bueltge.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since       Version 2.0.0
 *
 * Php Version 5.3
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Php
 */
class Debug_Objects_Php extends Debug_Objects {

	protected static $classobj = NULL;

	var $content = '';

	var $warnings = array();

	var $notices = array();

	var $messages = array();

	var $real_error_handler = array();

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_Php|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj && self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Constructor
	 *
	 * @return \Debug_Objects_Php
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return;
		}

		$this->content  = '';
		$this->warnings = array();
		$this->notices  = array();
		$this->messages = array();

		// @see http://php.net/manual/de/function.set-error-handler.php
		$this->real_error_handler = set_error_handler( array( $this, 'process_error_backtrace' ) );

		// set classes for admin bar item
		add_filter( 'debug_objects_css_classes', array( $this, 'get_css_classes' ) );

		add_filter( 'debug_objects_tab_css_classes', array( $this, 'set_tab_css_classes' ), 10, 2 );

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Get data for the generated tabs
	 *
	 * @param  Array $tabs
	 *
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		if ( empty( $tabs ) ) {
			$tabs = array();
		}

		$tabs[ ] = array(
			'tab'      => __( 'System' ),
			'class'    => '',
			'function' => array( $this, 'get_different_stuff' )
		);

		return $tabs;
	}

	/**
	 * Adds a backtrace to PHP errors
	 *
	 * @see http://stackoverflow.com/questions/1159216/how-can-i-get-php-to-produce-a-backtrace-upon-errors/1159235#1159235
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @return null
	 */
	public function process_error_backtrace( $errno, $errstr, $errfile, $errline ) {

		if ( ! ( error_reporting() && $errno ) ) {
			return NULL;
		}

		$type  = '';
		$fatal = FALSE;
		$_key  = md5( $errfile . ':' . $errline . ':' . $errstr );

		switch ( $errno ) {
			case E_WARNING      :
			case E_USER_WARNING :
				$this->warnings[ $_key ] = array( $errfile . ':' . $errline, $errstr );
				break;
			case E_STRICT      :
			case E_NOTICE      :
			case ( defined( 'E_DEPRECATED' ) ? E_DEPRECATED : 8192 ) :
			case E_USER_NOTICE  :
				$type                   = 'warning';
				$fatal                  = FALSE;
				$this->notices[ $_key ] = array( $errfile . ':' . $errline, $errstr );
				break;
			default       :
				$type                    = 'fatal error';
				$fatal                   = TRUE;
				$this->messages[ $_key ] = array( $type, $errstr );
				break;
		}

		$trace = debug_backtrace();
		array_shift( $trace );

		if ( 'cli' === php_sapi_name() && ini_get( 'display_errors' ) ) {
			$this->content .= 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			foreach ( $trace as $item ) {
				$this->content .= '  ' . ( isset( $item[ 'file' ] ) ? $item[ 'file' ]
						: '< unknown file >' ) . ' ' . ( isset( $item[ 'line' ] ) ? $item[ 'line' ]
						: '< unknown line >' ) . ' calling ' . $item[ 'function' ] . '()' . "\n";
			}

			flush();
		} else if ( ini_get( 'display_errors' ) ) {
			$this->content .= '<p class="error_backtrace">' . "\n";
			$this->content .= '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			$this->content .= '  <ol>' . "\n";
			foreach ( $trace as $item ) {
				$this->content .= '	<li>' . ( isset( $item[ 'file' ] ) ? $item[ 'file' ]
						: '< unknown file >' ) . ' ' . ( isset( $item[ 'line' ] ) ? $item[ 'line' ]
						: '< unknown line >' ) . ' calling ' . $item[ 'function' ] . '()</li>' . "\n";
			}
			$this->content .= '  </ol>' . "\n";
			$this->content .= '</p>' . "\n";

			flush();
		}

		if ( ini_get( 'log_errors' ) ) {
			$items = array();
			foreach ( $trace as $item ) {
				$items[ ] = ( isset( $item[ 'file' ] ) ? $item[ 'file' ]
						: '< unknown file >' ) . ' ' . ( isset( $item[ 'line' ] ) ? $item[ 'line' ]
						: '< unknown line >' ) . ' calling ' . $item[ 'function' ] . '()';
			}
			$message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(
					' | ', $items
				);
			error_log( $message );
		}

		if ( $fatal ) {
			return NULL;
		}
	}

	/**
	 * @param $type
	 * @param $message
	 * @param $file
	 * @param $line
	 *
	 * @return bool|mixed
	 */
	public function error_handler( $type, $message, $file, $line ) {

		$_key = md5( $file . ':' . $line . ':' . $message );

		switch ( $type ) {
			case E_WARNING :
			case E_USER_WARNING :
				$this->warnings[ $_key ] = array( $file . ':' . $line, $message );
				break;
			case E_NOTICE :
			case E_USER_NOTICE :
				$this->notices[ $_key ] = array( $file . ':' . $line, $message );
				break;
			case E_STRICT :
				// @TODO
				break;
			case E_DEPRECATED :
			case E_USER_DEPRECATED :
				// @TODO
				break;
			default:
				$this->messages[ $_key ] = array( $type, $message );
				break;
		}

		if ( NULL != $this->real_error_handler ) {
			return call_user_func( $this->real_error_handler, $type, $message, $file, $line );
		} else {
			return FALSE;
		}
	}

	/**
	 * Get different classes for admin bar item to format to see easier a problem on php
	 *
	 * @param Array|string $classes
	 *
	 * @return Array $classes
	 */
	public function get_css_classes( $classes = '' ) {

		if ( count( $this->warnings ) ) {
			$classes[ ] = ' debug_objects_php_warning';
		}

		if ( count( $this->notices ) ) {
			$classes[ ] = ' debug_objects_php_notice';
		}

		if ( count( $this->messages ) ) {
			$classes[ ] = ' debug_objects_php_message';
		}

		return $classes;
	}

	/**
	 * Set css class to each tab
	 *
	 * @param string $classes
	 * @param        $tab
	 *
	 * @return Array|string
	 */
	public function set_tab_css_classes( $classes = '', $tab ) {

		if ( 'System' === $tab ) {
			$classes = $this->get_css_classes();
		}

		return $classes;
	}

	/**
	 * Get all output
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function get_different_stuff( $echo = TRUE ) {

		global $wpdb, $locale;

		$class  = '';
		$output = '';

		$output .= '<div class="important"><h4>PHP Error Backtrace</h4>' . "\n";
		$output .= $this->content;
		$output .= '</div>' . "\n";

		// php warnings
		if ( 0 < count( $this->warnings ) ) {
			$important = ' class="important"';
		} else {
			$important = '';
		}
		$output .= "<div$important><h2>Total PHP Warnings: " . number_format( count( $this->warnings ) ) . "</h2>\n";
		if ( count( $this->warnings ) ) {
			$output .= '<ol>';
			foreach ( $this->warnings as $location_message ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				list( $location, $message ) = $location_message;
				$output .= "<li$class>WARNING: " . str_replace( ABSPATH, '', $location )
					. ' - ' . strip_tags( $message ) . '</li>';
			}
			$output .= '</ol>';
		}
		$output .= '</div>';

		// php notices
		if ( 0 < count( $this->notices ) ) {
			$important = ' class="important"';
		} else {
			$important = '';
		}
		$output .= "<div$important><h2>Total PHP Notices: " . number_format( count( $this->notices ) ) . "</h2>\n";
		if ( count( $this->notices ) ) {
			$output .= '<ol>';
			foreach ( $this->notices as $location_message ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				list( $location, $message ) = $location_message;
				$output .= "<li$class>NOTICE: " . str_replace( ABSPATH, '', $location ) .
					' - ' . strip_tags( $message ) . '</li>';
			}
			$output .= '</ol>';
		}
		$output .= '</div>';

		// default messages, unknown error type
		if ( 0 < count( $this->messages ) ) {
			$important = ' class="important"';
		} else {
			$important = '';
		}
		$output .= "<div$important><h2>Total unknown Error Messages: " . number_format(
				count( $this->messages )
			) . "</h2>\n";
		if ( count( $this->messages ) ) {
			$output .= '<ol>';
			foreach ( $this->messages as $location_message ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				list( $location, $message ) = $location_message;
				$output .= "<li$class>Error Message: " . str_replace( ABSPATH, '', $location )
					. ' - ' . strip_tags( $message ) . '</li>';
			}
			$output .= '</ol>';
		}
		$output .= '</div>';

		if ( defined( 'WPLANG' ) ) {
			$locale = WPLANG;
		} else if ( empty( $locale ) ) {
			$locale = 'en_US';
		}

		/**
		 * Get text direction
		 *
		 * @see class WP_Locale(), wp-inlcudes/locale.php
		 */
		if ( isset( $GLOBALS[ 'text_direction' ] ) ) {
			$text_direction = $GLOBALS[ 'text_direction' ];
		} else {
			$text_direction = 'ltr';
		}

		$class        = '';
		$memory_usage = function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;
		$memory_limit = (int) ini_get( 'memory_limit' );

		$memory_percent = '';
		if ( ! empty( $memory_usage ) && ! empty( $memory_limit ) ) {
			$memory_percent = round( $memory_usage / $memory_limit * 100, 0 );
		}

		if ( ! isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
			$_SERVER[ 'HTTP_USER_AGENT' ] = __( 'Undefinded' );
		}

		$operation_system = __( 'undefined' );
		$oskey            = esc_attr( $_SERVER[ 'HTTP_USER_AGENT' ] );
		//Operating-System scan start
		if ( preg_match( '=WIN=i', $oskey ) ) { //Windows
			if ( preg_match( '=NT=i', $oskey ) ) {
				if ( preg_match( '=6.1=', $oskey ) ) {
					$operation_system = __( 'Windows 7' );
				} elseif ( preg_match( '=6.0=', $oskey ) ) {
					$operation_system = __( 'Windows Vista' );
				} elseif ( preg_match( '=5.1=', $oskey ) ) {
					$operation_system = __( 'Windows XP' );
				} elseif ( preg_match( '=5.0=', $oskey ) ) { //Windows 2000
					$operation_system = __( 'Windows 2000' );
				}
			} else {
				if ( preg_match( '=ME=', $oskey ) ) { //Windows ME
					$operation_system = __( 'Windows ME' );
				} elseif ( preg_match( '=98=', $oskey ) ) { //Windows 98
					$operation_system = __( 'Windows 98' );
				} elseif ( preg_match( '=95=', $oskey ) ) { //Windows 95
					$operation_system = __( 'Windows 95' );
				}
			}
		} elseif ( preg_match( '=MAC=i', $oskey ) ) { //Macintosh
			$operation_system = __( 'Macintosh' );
		} elseif ( preg_match( '=LINUX=i', $oskey ) ) { //Linux
			$operation_system = __( 'Linux' );
		} //Operating-System scan end

		if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
			$autosave_interval = __( 'Undefined' );
		} elseif ( ! constant( 'AUTOSAVE_INTERVAL' ) ) {
			$autosave_interval = __( 'OFF' );
		} else {
			$autosave_interval = AUTOSAVE_INTERVAL . __( 's' );
		}

		if ( ! defined( 'WP_POST_REVISIONS' ) ) {
			$post_revisions = __( 'Undefined' );
		} elseif ( ! constant( 'WP_POST_REVISIONS' ) ) {
			$post_revisions = __( 'OFF' );
		} else {
			$post_revisions = WP_POST_REVISIONS;
		}

		$savequeries = '';
		if ( ! defined( 'SAVEQUERIES' ) ) {
			$savequeries = __( 'Undefined' );
		} elseif ( ! constant( 'SAVEQUERIES' ) ) {
			$savequeries = __( 'OFF' );
		} elseif ( 1 == SAVEQUERIES ) {
			$savequeries = __( 'ON' );
		}

		$debug = '';
		if ( ! defined( 'WP_DEBUG' ) ) {
			$debug = __( 'Undefined' );
		} elseif ( ! constant( 'WP_DEBUG' ) ) {
			$debug = __( 'OFF' );
		} elseif ( 1 == WP_DEBUG ) {
			$debug = __( 'ON' );
		}

		$ssl_login = '';
		if ( ! defined( 'FORCE_SSL_LOGIN' ) ) {
			$ssl_login = __( 'Undefined' );
		} elseif ( ! constant( 'FORCE_SSL_LOGIN' ) ) {
			$ssl_login = __( 'OFF' );
		} elseif ( 1 == FORCE_SSL_LOGIN ) {
			$ssl_login = __( 'ON' );
		}

		$concatenate_scripts = '';
		if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
			$concatenate_scripts = __( 'Undefined' );
		} elseif ( ! constant( 'CONCATENATE_SCRIPTS' ) ) {
			$concatenate_scripts = __( 'OFF' );
		} elseif ( 1 == CONCATENATE_SCRIPTS ) {
			$concatenate_scripts = __( 'ON' );
		}

		$compress_scripts = '';
		if ( ! defined( 'COMPRESS_SCRIPTS' ) ) {
			$compress_scripts = __( 'Undefined' );
		} elseif ( ! constant( 'COMPRESS_SCRIPTS' ) ) {
			$compress_scripts = __( 'OFF' );
		} elseif ( 1 == COMPRESS_SCRIPTS ) {
			$compress_scripts = __( 'ON' );
		}

		$compress_css = '';
		if ( ! defined( 'COMPRESS_CSS' ) ) {
			$compress_css = __( 'Undefined' );
		} elseif ( ! constant( 'COMPRESS_CSS' ) ) {
			$compress_css = __( 'OFF' );
		} elseif ( 1 == COMPRESS_CSS ) {
			$compress_css = __( 'ON' );
		}

		$enforce_gzip = '';
		if ( ! defined( 'ENFORCE_GZIP' ) ) {
			$enforce_gzip = __( 'Undefined' );
		} elseif ( ! constant( 'ENFORCE_GZIP' ) ) {
			$enforce_gzip = __( 'OFF' );
		} elseif ( 1 == ENFORCE_GZIP ) {
			$enforce_gzip = __( 'ON' );
		}

		if ( ini_get( 'safe_mode' ) ) {
			$safe_mode = __( 'On' );
		} else {
			$safe_mode = __( 'Off' );
		}
		if ( ini_get( 'allow_url_fopen' ) ) {
			$allow_url_fopen = __( 'On' );
		} else {
			$allow_url_fopen = __( 'Off' );
		}
		if ( ini_get( 'upload_max_filesize' ) ) {
			$upload_max = ini_get( 'upload_max_filesize' );
		} else {
			$upload_max = __( 'Undefined' );
		}
		if ( ini_get( 'post_max_size' ) ) {
			$post_max = ini_get( 'post_max_size' );
		} else {
			$post_max = __( 'Undefined' );
		}
		if ( ini_get( 'max_execution_time' ) ) {
			$max_execute = ini_get( 'max_execution_time' );
		} else {
			$max_execute = __( 'Undefined' );
		}
		if ( is_callable( 'exif_read_data' ) ) {
			$exif = __( 'Yes' ) . ' ( Version ' . esc_attr( substr( phpversion( 'exif' ), 0, 4 ) ) . ')';
		} else {
			$exif = __( 'No' );
		}
		if ( is_callable( 'iptcparse' ) ) {
			$iptc = __( 'Yes' );
		} else {
			$iptc = __( 'No' );
		}
		if ( is_callable( 'xml_parser_create' ) ) {
			$xml = __( 'Yes' );
		} else {
			$xml = __( 'No' );
		}

		if ( function_exists( 'gd_info' ) ) {
			$is_libgd = __( 'Yes' );
		} else {
			$is_libgd = __( 'No' );
		}

		if ( function_exists( 'curl_init' ) ) {
			$curl = __( 'Yes' );
		} else {
			$curl = __( 'No' );
		}

		$output .= "\n" . '<h4>' . __( 'PHP Version &amp; System' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";

		if ( ! isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
			$_SERVER[ 'SERVER_SOFTWARE' ] = __( 'Undefined' );
		}
		$php_info = array(
			__( 'PHP version:' )                              => PHP_VERSION,
			__( 'Server:' )                                   => substr(
				esc_attr( $_SERVER[ 'SERVER_SOFTWARE' ] ), 0, 14
			),
			__( 'Server SW:' )                                => esc_attr( $_SERVER[ 'SERVER_SOFTWARE' ] ),
			__( 'OS version:' )                               => $operation_system,
			__( 'Memory usage in MByte:' )                    => $memory_usage,
			__( 'PHP Memory limit, Configuration in MByte:' ) => $memory_limit,
			__( 'PHP Memory percent (in % of 100%):' )        => $memory_percent,
			__( 'PHP Safe Mode:' )                            => $safe_mode,
			__( 'PHP Allow URL fopen:' )                      => $allow_url_fopen,
			__( 'PHP Max Upload Size:' )                      => $upload_max,
			__( 'PHP Max Post Size:' )                        => $post_max,
			__( 'PHP Max Script Execute Time:' )              => $max_execute,
			__( 'PHP Exif support:' )                         => $exif,
			__( 'PHP IPTC support:' )                         => $iptc,
			__( 'PHP XML support:' )                          => $xml,
			__( 'PHP GD Support' )                            => $is_libgd,
			__( 'PHP cURL Support:' )                         => $curl,

		);
		// hook for more php information
		$php_info = apply_filters( 'debug_onjects_php_infos', $php_info );

		foreach ( $php_info as $name => $value ) {
			$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
			$output .= '<li' . $class . '>' . $name . ' ' . $value . '</li>' . "\n";
		}
		$output .= '</ul>' . "\n";

		/**
		 * SQL information
		 */
		$sqlversion = wp_cache_get( 'sql_version' );
		if ( FALSE === $sqlversion ) {
			$sqlversion = $wpdb->get_var( 'SELECT VERSION() AS version' );
			wp_cache_set( 'sql_version', $sqlversion );
		}
		$mysqlinfo = wp_cache_get( 'mysql_info' );
		if ( FALSE === $mysqlinfo ) {
			$mysqlinfo = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
			if ( is_array( $mysqlinfo ) ) {
				$sql_mode = $mysqlinfo[ 0 ]->Value;
			}
			if ( empty( $sql_mode ) ) {
				$sql_mode = __( 'Undefined' );
			}
			wp_cache_set( 'mysql_info', $sql_mode );
		}

		$output .= "\n" . '<h4>' . __( 'MySQL' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li>' . __( 'MySQL version:' ) . ' ' . esc_attr( $sqlversion ) . '</li>' . "\n";
		$output .= '<li>' . __( 'SQL Mode:' ) . ' ' . esc_attr( $sql_mode ) . '</li>' . "\n";

		/**
		 * WordPress information
		 */
		if ( function_exists( 'is_multisite' ) ) {
			if ( is_multisite() ) {
				$is_multisite = __( 'Yes' );
			} else {
				$is_multisite = __( 'No' );
			}
		} else {
			$is_multisite = __( 'Undefined' );
		}

		$output .= "\n" . '<h4>' . __( 'WordPress Informations' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li>' . __( 'Version:' ) . ' ' . esc_attr( get_bloginfo( 'version' ) ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __( 'Multisite:' ) . ' ' . $is_multisite . '</li>' . "\n";
		$output .= '<li>' . __( 'Language, constant' ) . ' <code>WPLANG</code>: ' . esc_attr(
				$locale
			) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Text direction, global'
			) . ' <code>$GLOBALS[\'text_direction\']</code>: ' . esc_attr( $text_direction ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Language folder, constant'
			) . ' <code>WP_LANG_DIR</code>: ' . esc_attr( WP_LANG_DIR ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Content URL, constant'
			) . ' <code>WP_CONTENT_URL</code>: ' . esc_attr( WP_CONTENT_URL ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Content folder, constant'
			) . ' <code>WP_CONTENT_DIR</code>: ' . esc_attr( WP_CONTENT_DIR ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Memory limit, constant'
			) . ' <code>WP_MEMORY_LIMIT</code>: ' . esc_attr( WP_MEMORY_LIMIT ) . ' Byte</li>' . "\n";
		$output .= '<li>' . __(
				'Post revision, constant'
			) . ' <code>WP_POST_REVISIONS</code>: ' . esc_attr( $post_revisions ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Save queries, constant'
			) . ' <code>SAVEQUERIES</code>: ' . esc_attr( $savequeries ) . '</li>' . "\n";
		$output .= '<li>' . __( 'Debug option, constant' ) . ' <code>WP_DEBUG</code>: ' . esc_attr(
				$debug
			) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'SSL Login, constant'
			) . ' <code>FORCE_SSL_LOGIN</code>: ' . esc_attr( $ssl_login ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Concatenate scripts, constant'
			) . ' <code>CONCATENATE_SCRIPTS</code>: ' . esc_attr( $concatenate_scripts ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Compress scripts, constant'
			) . ' <code>COMPRESS_SCRIPTS</code>: ' . esc_attr( $compress_scripts ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Compress stylesheet, constant'
			) . ' <code>COMPRESS_CSS</code>: ' . esc_attr( $compress_css ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Enforce GZIP, constant'
			) . ' <code>ENFORCE_GZIP</code>: ' . esc_attr( $enforce_gzip ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Autosave interval, constant'
			) . ' <code>AUTOSAVE_INTERVAL</code>: ' . esc_attr( $autosave_interval ) . '</li>' . "\n";
		$output .= '</ul>' . "\n";

		if ( ! defined( 'COOKIE_DOMAIN' ) ) {
			$cookie_domain = __( 'Undefined' );
		} else {
			$cookie_domain = COOKIE_DOMAIN;
		}

		if ( ! defined( 'COOKIEPATH' ) ) {
			$cookiepath = __( 'Undefined' );
		} else {
			$cookiepath = COOKIEPATH;
		}

		if ( ! defined( 'SITECOOKIEPATH' ) ) {
			$sitecookiepath = __( 'Undefined' );
		} else {
			$sitecookiepath = SITECOOKIEPATH;
		}

		if ( ! defined( 'PLUGINS_COOKIE_PATH' ) ) {
			$plugins_cookie_path = __( 'Undefined' );
		} else {
			$plugins_cookie_path = PLUGINS_COOKIE_PATH;
		}

		if ( ! defined( 'ADMIN_COOKIE_PATH' ) ) {
			$admin_cookie_path = __( 'Undefined' );
		} else {
			$admin_cookie_path = ADMIN_COOKIE_PATH;
		}

		$output .= "\n" . '<h4>' . __( 'WordPress Cookie Informations' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Cookie domain, constant'
			) . ' <code>COOKIE_DOMAIN</code>: ' . esc_attr( $cookie_domain ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Cookie path, constant'
			) . ' <code>COOKIEPATH</code>: ' . esc_attr( $cookiepath ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Site cookie path, constant'
			) . ' <code>SITECOOKIEPATH</code>: ' . esc_attr( $sitecookiepath ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Plugin cookie path, constant'
			) . ' <code>PLUGINS_COOKIE_PATH</code>: ' . esc_attr( $plugins_cookie_path ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Admin cookie path, constant'
			) . ' <code>ADMIN_COOKIE_PATH</code>: ' . esc_attr( $admin_cookie_path ) . '</li>' . "\n";
		$output .= '</ul>' . "\n";

		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			$fs_chmod_file = __( 'Undefined' );
		} else {
			$fs_chmod_file = FS_CHMOD_FILE;
		}

		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			$fs_chmod_dir = __( 'Undefined' );
		} else {
			$fs_chmod_dir = FS_CHMOD_DIR;
		}

		$output .= "\n" . '<h4>' . __( 'WordPress File Permissions Informations' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li class="alternate">' . __(
				'File Permissions, constant'
			) . ' <code>FS_CHMOD_FILE</code>: ' . esc_attr( $fs_chmod_file ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'DIR Permissions, constant'
			) . ' <code>FS_CHMOD_DIR</code>: ' . esc_attr( $fs_chmod_dir ) . '</li>' . "\n";
		$output .= '</ul>' . "\n";

		if ( ! defined( 'CUSTOM_USER_TABLE' ) ) {
			$if_custom_user_table = __( 'Undefined' );
		} else {
			$if_custom_user_table = CUSTOM_USER_TABLE;
		}

		if ( ! defined( 'CUSTOM_USER_META_TABLE' ) ) {
			$if_custom_usermeta_table = __( 'Undefined' );
		} else {
			$if_custom_usermeta_table = CUSTOM_USER_META_TABLE;
		}

		$output .= "\n" . '<h4>' . __( 'WordPress Custom User &amp; Usermeta Tables' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Custom User Table, constant'
			) . ' <code>CUSTOM_USER_TABLE</code>: ' . esc_attr( $if_custom_user_table ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Cookie path, constant'
			) . ' <code>CUSTOM_USER_META_TABLE</code>: ' . esc_attr( $if_custom_usermeta_table ) . '</li>' . "\n";
		$output .= '</ul>' . "\n";

		if ( ! defined( 'FS_METHOD' ) ) {
			$fs_method = __( 'Undefined' );
		} else {
			$fs_method = FS_METHOD;
		}

		if ( ! defined( 'FTP_BASE' ) ) {
			$ftp_base = __( 'Undefined' );
		} else {
			$ftp_base = FTP_BASE;
		}

		if ( ! defined( 'FTP_CONTENT_DIR' ) ) {
			$ftp_content_dir = __( 'Undefined' );
		} else {
			$ftp_content_dir = FTP_CONTENT_DIR;
		}

		if ( ! defined( 'FTP_PLUGIN_DIR' ) ) {
			$ftp_plugin_dir = __( 'Undefined' );
		} else {
			$ftp_plugin_dir = FTP_PLUGIN_DIR;
		}

		if ( ! defined( 'FTP_PUBKEY' ) ) {
			$ftp_pubkey = __( 'Undefined' );
		} else {
			$ftp_pubkey = FTP_PUBKEY;
		}

		if ( ! defined( 'FTP_PRIVKEY' ) ) {
			$ftp_privkey = __( 'Undefined' );
		} else {
			$ftp_privkey = FTP_PRIVKEY;
		}

		if ( ! defined( 'FTP_USER' ) ) {
			$ftp_user = __( 'Undefined' );
		} else {
			$ftp_user = FTP_USER;
		}

		if ( ! defined( 'FTP_PASS' ) ) {
			$ftp_pass = __( 'Undefined' );
		} else {
			$ftp_pass = FTP_PASS;
		}

		if ( ! defined( 'FTP_HOST' ) ) {
			$ftp_host = __( 'Undefined' );
		} else {
			$ftp_host = FTP_HOST;
		}

		$output .= "\n" . '<h4>' . __( 'WordPress FTP/SSH Informations' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Forces the filesystem method, constant'
			) . ' <code>FS_METHOD</code> (<code>direct</code>, <code>ssh</code>, <code>ftpext</code> or <code>ftpsockets</code>): ' . esc_attr(
				$fs_method
			) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Path to root install directory, constant'
			) . ' <code>FTP_BASE</code>: ' . esc_attr( $ftp_base ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Absolute path to wp-content directory, constant'
			) . ' <code>FTP_CONTENT_DIR</code>: ' . esc_attr( $ftp_content_dir ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'Absolute path to plugin directory, constant'
			) . ' <code>FTP_PLUGIN_DIR</code>: ' . esc_attr( $ftp_plugin_dir ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Absolute path to SSH public key, constant'
			) . ' <code>FTP_PUBKEY</code>: ' . esc_attr( $ftp_pubkey ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'dorector path to SSH private key, constant'
			) . ' <code>FTP_PRIVKEY</code>: ' . esc_attr( $ftp_privkey ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'FTP or SSH username, constant'
			) . ' <code>FTP_USER</code>: ' . esc_attr( $ftp_user ) . '</li>' . "\n";
		$output .= '<li>' . __(
				'FTP or SSH password, constant'
			) . ' <code>FTP_PASS</code>: ' . esc_attr( $ftp_pass ) . '</li>' . "\n";
		$output .= '<li class="alternate">' . __(
				'Hostname, constant'
			) . ' <code>FTP_HOST</code>: ' . esc_attr( $ftp_host ) . '</li>' . "\n";
		$output .= '</ul>' . "\n";

		$output .= "\n" . '<h4>' . __( 'WordPress Query Informations' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li class="alternate">' . __( 'Queries:' ) . ' ' . esc_attr( get_num_queries() ) . 'q';
		$output .= '</li>' . "\n";
		$output .= '<li>' . __( 'Timer stop:' ) . ' ' . esc_attr( timer_stop() ) . 's</li>' . "\n";
		$output .= '</ul>' . "\n";

		// PHP_SELF
		if ( ! isset( $_SERVER[ 'PATH_INFO' ] ) ) {
			$_SERVER[ 'PATH_INFO' ] = __( 'Undefined' );
		}
		if ( ! isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$_SERVER[ 'REQUEST_URI' ] = __( 'Undefined' );
		}
		if ( ! isset( $_SERVER[ 'SCRIPT_NAME' ] ) ) {
			$_SERVER[ 'SCRIPT_NAME' ] = __( 'Undefined' );
		}
		if ( ! isset( $_SERVER[ 'QUERY_STRING' ] ) ) {
			$_SERVER[ 'QUERY_STRING' ] = __( 'Undefined' );
		}
		if ( ! isset( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) {
			$_SERVER[ 'SCRIPT_FILENAME' ] = __( 'Undefined' );
		}
		if ( ! isset( $_SERVER[ 'PHP_SELF' ] ) ) {
			$_SERVER[ 'PHP_SELF' ] = __( 'Undefined' );
		}

		$output .= "\n" . '<h4>' . __( 'Selected server and execution environment information' ) . '</h4>' . "\n";
		$output .= '<ul>' . "\n";
		$output .= '<li>' . __( 'PATH_INFO:' ) . ' ' . esc_attr( $_SERVER[ 'PATH_INFO' ] ) . '</li>';
		$output .= '<li class="alternate">' . __( 'REQUEST_URI:' ) . ' ' . esc_attr(
				$_SERVER[ 'REQUEST_URI' ]
			) . '</li>';
		$output .= '<li>' . __( 'QUERY_STRING:' ) . ' ' . esc_attr( $_SERVER[ 'QUERY_STRING' ] ) . '</li>';
		$output .= '<li class="alternate">' . __( 'SCRIPT_NAME:' ) . ' ' . esc_attr(
				$_SERVER[ 'SCRIPT_NAME' ]
			) . '</li>';
		$output .= '<li>' . __( 'SCRIPT_FILENAME:' ) . ' ' . esc_attr( $_SERVER[ 'SCRIPT_FILENAME' ] ) . '</li>';
		$output .= '<li class="alternate">' . __( 'PHP_SELF:' ) . ' ' . esc_attr( $_SERVER[ 'PHP_SELF' ] ) . '</li>';
		$output .= '<li>' . __( 'FILE:' ) . ' ' . esc_attr( __FILE__ ) . '</li>';
		$output .= '</ul>' . "\n";

		$output .= "\n" . '<h4>' . __( 'HTTP $_SERVER variables' ) . '</h4>' . "\n";
		if ( ! isset( $_SERVER ) || empty( $_SERVER ) ) {
			$output .= __( 'Undefined or empty' );
		} else {
			/** @noinspection PhpInternalEntityUsedInspection */
			$output .= '<li class="alternate">' . Debug_Objects::pre_print( $_SERVER, '', TRUE ) . '</li>';
		}
		$output .= '</ul>' . "\n";

		// error
		$output .= "\n" . '<h4>' . __( 'HTTP $_GET Error' ) . '</h4>' . "\n";
		$output .= '<ul><li>' . "\n";
		if ( ! isset( $_GET[ 'error' ] ) || empty( $_GET[ 'error' ] ) ) {
			$output .= __( 'Undefined or empty' );
		} else {
			/** @noinspection PhpInternalEntityUsedInspection */
			$output .= '<li class="alternate">' . Debug_Objects::pre_print(  esc_attr( $_GET[ 'error' ] ), '', TRUE ) . '</li>';
		}
		$output .= '</li></ul>' . "\n";

		// Globals
		$output .= "\n" . '<h4>' . __( 'HTTP $_GET variables' ) . '</h4>' . "\n";
		$output .= '<ul><li>' . "\n";
		if ( ! isset( $_GET ) || empty( $_GET ) ) {
			$output .= __( 'Undefined or empty' );
		} else {
			/** @noinspection PhpInternalEntityUsedInspection */
			$output .= Debug_Objects::pre_print( $_GET, '', TRUE );
		}
		$output .= '</li></ul>' . "\n";

		$output .= "\n" . '<h4>' . __( 'HTTP $_POST variables' ) . '</h4>' . "\n";
		$output .= '<ul><li>' . "\n";
		if ( ! isset( $_POST ) || empty( $_POST ) ) {
			$output .= __( 'Undefined or empty' );
		} else {
			/** @noinspection PhpInternalEntityUsedInspection */
			$output .= Debug_Objects::pre_print( $_POST, '', TRUE );
		}
		$output .= '</li></ul>' . "\n";

		// cookies
		$output .= "\n" . '<h4>' . __( '$_COOKIE variables' ) . '</h4>' . "\n";
		$output .= '<ul><li>' . "\n";
		if ( ! isset( $_COOKIE ) || empty( $_COOKIE ) ) {
			$output .= __( 'Undefined or empty' );
		} else {
			/** @noinspection PhpInternalEntityUsedInspection */
			$output .= Debug_Objects::pre_print( $_COOKIE, '', TRUE );
		}
		$output .= '</li></ul>' . "\n";

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

} // end class