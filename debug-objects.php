<?php
/**
 * Plugin Name: Debug Objects
 * Plugin URI:  http://bueltge.de/debug-objects-wordpress-plugin/966/
 * Text Domain: debug_objects
 * Domain Path: /languages
 * Description: List filter and action-hooks, cache data, defined constants, queries, included scripts and styles, php
 * and memory information and return of conditional tags only for admins; for debug, information or learning purposes.
 * Setting output in the settings of the plugin and use output via link in Admin Bar, via setting, via url-param
 * '<code>debug</code>' or set a cookie via url param '<code>debugcookie</code>' in days.
 * Version:     2.3.1
 * License:     GPL-3+
 * Author:      Frank Bültge
 * Author URI:  http://bueltge.de/
 *
 * @version 2016-03-31
 */

// avoid direct calls to this file, because now WP core and framework has been used.
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects' ) ) {

	// include plugin on hook
	add_action( 'plugins_loaded', array( 'Debug_Objects', 'get_object' ) );
	register_activation_hook( __FILE__, array( 'Debug_Objects', 'on_activation' ) );

	// include the ChromePHP very early
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc/class-chromephp.php';
	$debug_objects_chromephp = Debug_Objects_Chromephp::init();

	/**
	 * Class Debug_Objects
	 */
	class Debug_Objects {

		/**
		 * The class object
		 *
		 * @since  0.0.1
		 * @var    String
		 */
		protected static $classobj = NULL;

		/**
		 * Define folder, there have inside the autoload files
		 *
		 * @since  09/16/2013
		 * @var    String
		 */
		static protected $file_base = '';

		// table for page hooks
		public static $table = 'hook_list';

		// var for tab array
		public static $tabs = array();

		// string vor save in DB
		public static $option_string = 'debug_objects';

		// plugin basename
		public static $plugin;

		// included classes on default; without user settings
		public static $by_settings = array( 'Wrap' );

		// exclude class for central include
		public static $exclude_class = array( 'Backend', 'Frontend', 'Stack_Trace' );

		// store classes from settings
		public $store_classes = array();

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @return \Debug_Objects|String $classobj
		 */
		public static function get_object() {

			NULL === self::$classobj && self::$classobj = new self();

			return self::$classobj;
		}

		/**
		 * Init other methods via hook; install settings and capabilities
		 *
		 * @since   2.0.0
		 * @return \Debug_Objects
		 */
		public function __construct() {

			ini_set( 'max_execution_time', 60 );

			// define table
			self::$table  = $GLOBALS[ 'wpdb' ]->base_prefix . self::$table;
			self::$plugin = plugin_basename( __FILE__ );

			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			// add and remove settings, the table for the plugin
			register_deactivation_hook( __FILE__, array( $this, 'on_deactivation' ) );
			register_uninstall_hook( __FILE__, array( 'Debug_Objects', 'on_uninstall' ) );

			// define folder for autoload, settings was load via settings and init_classes()
			self::$file_base = dirname( __FILE__ ) . '/inc/autoload';

			// Load 5.4 improvements 
			if ( version_compare( phpversion(), '5.4.0', '>=' ) ) {
				require_once( dirname( __FILE__ ) . '/inc/class-php-54-improvements.php' );
			}

			// load all files form autoload folder
			self::load();

			// add custom capability
			add_action( 'admin_init', array( $this, 'add_capabilities' ) );

			self::init_classes();
		}

		/**
		 * Load all files from a folder, no check
		 *
		 * @since   09/16/2013
		 * @return  void
		 */
		public static function load() {

			$file_base = self::$file_base;

			$autoload_files = glob( "$file_base/*.php" );

			// load files
			foreach ( $autoload_files as $path ) {
				require_once $path;
			}
		}

		/**
		 * Add custom capability to check always with custom object
		 *
		 * @since   0.0.1
		 * @return  void
		 */
		public function add_capabilities() {

			/** @var $wp_roles WP_Role */
			global $wp_roles;
			$wp_roles->add_cap( 'administrator', '_debug_objects' );
		}

		/**
		 * Include classes
		 * Use filter string 'debug_objects_classes' for include custom classes
		 *
		 * @since   2.0.0
		 * @return  void
		 */
		public function init_classes() {

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$options = (array) get_site_option( self::$option_string );
			} else {
				$options = (array) get_option( self::$option_string );
			}

			if ( ( isset( $options[ 'frontend' ] ) && '1' === $options[ 'frontend' ] ) || ( isset( $options[ 'backend' ] ) && '1' === $options[ 'backend' ] ) ) {
				$view = TRUE;
			} else {
				$view = FALSE;
			}

			if ( isset( $options[ 'stack_trace' ] ) && '1' === $options[ 'stack_trace' ] ) {
				define( 'STACKTRACE', TRUE );
			}

			// exclude options from include classes
			foreach ( self:: $exclude_class as $exclude_class ) {

				if ( isset( $options[ strtolower( $exclude_class ) ] ) ) {
					unset( $options[ strtolower( $exclude_class ) ] );
				}
			}

			if ( ! empty( $options ) ) {
				foreach ( $options as $class => $check ) {
					if ( '1' === $check ) {
						self:: $by_settings[ ] = ucwords( $class );
					}
				}
			}
			$classes = $this->store_classes = apply_filters( 'debug_objects_classes', self::$by_settings );

			self::set_cookie_control();

			// Load class backtrace without output, if option is active
			if ( in_array( 'Rewrite_backtrace', $classes, FALSE ) ) {

				$file = dirname( __FILE__ ) . DIRECTORY_SEPARATOR
					. 'inc/class-rewrite_backtrace.php';
				require_once( $file );
				add_action( 'init', array( 'Debug_Objects_Rewrite_Backtrace', 'init' ) );
			}

			if ( $view || self::debug_control() ) {
				foreach ( $classes as $key => $require ) {
					if ( ! class_exists( 'Debug_Objects_' . $require ) ) {
						$file = dirname( __FILE__ ) . DIRECTORY_SEPARATOR
							. 'inc/class-' . strtolower( $require ) . '.php';

						if ( file_exists( $file ) ) {
							/* @noinspection */
							require_once $file;
						}

						add_action( 'plugins_loaded', array( 'Debug_Objects_' . $require, 'init' ), - 1 );
					}
				}
			}

		}

		public function get_classes() {

			return $this->store_classes;
		}

		/**
		 * Check for url param to view output
		 *
		 * @access  public
		 * @since   2.0.1
		 * @return bool $debug
		 */
		public function debug_control() {

			// Debug via _GET Param on URL
			if ( ! isset( $_GET[ 'debug' ] ) ) {
				$debug = FALSE;
			} else {
				$debug = TRUE;
			}

			if ( ! $debug ) {
				$debug = self::get_cookie_control( $debug );
			}

			return (bool) $debug;
		}

		/**
		 * Check for cookie to view output
		 *
		 * @access  public
		 * @since   2.0.1
		 *
		 * @param   $debug
		 *
		 * @return  bool $debug
		 */
		public function get_cookie_control( $debug ) {


			if ( ! isset( $_COOKIE[ self::get_plugin_data() . '_cookie' ] ) ) {
				return FALSE;
			}

			$cookie = $_COOKIE[ self::get_plugin_data() . '_cookie' ];
			if ( 'Debug_Objects_True' === $cookie ) {
				$debug = TRUE;
			}

			return (bool) $debug;
		}

		/**
		 * Init cookie and control the live time
		 *
		 * @access  public
		 * @since   2.0.1
		 * @return  void
		 */
		public function set_cookie_control() {

			if ( ! isset( $_GET[ 'debugcookie' ] ) ) {
				return;
			}

			if ( $_GET[ 'debugcookie' ] ) {
				//$cookie_live = time() + 60 * 60 * 24 * (int) $_GET[ 'debugcookie' ]; // days
				$cookie_live = new DateTime( 'now' );
				$user_value = (int) $_GET[ 'debugcookie' ];
				$cookie_live->add( new DateInterval( 'P' . $user_value . 'D' ) );
				setcookie(
					$this->get_plugin_data() . '_cookie', 'Debug_Objects_True', $cookie_live, COOKIEPATH, COOKIE_DOMAIN
				);
			}

			if ( 0 === (int) $_GET[ 'debugcookie' ] ) {
				setcookie( $this->get_plugin_data() . '_cookie', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
			}
		}

		/**
		 * Return plugin comment data
		 *
		 * @since   2.0.0
		 * @access  public
		 *
		 * @param   string $value default = 'TextDomain'
		 *                        Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
		 * @param   bool   $echo
		 *
		 * @return  string
		 */
		public function get_plugin_data( $value = 'TextDomain', $echo = FALSE ) {

			static $plugin_data = array();

			// fetch the data just once.
			if ( isset( $plugin_data[ $value ] ) ) {
				return $plugin_data[ $value ];
			}

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			$plugin_data  = get_plugin_data( __FILE__ );
			$plugin_value = empty ( $plugin_data[ $value ] ) ? '' : $plugin_data[ $value ];

			if ( $echo ) {
				echo $plugin_value;
			}

			return $plugin_value;
		}

		/**
		 * Return plugin basename of plugin
		 *
		 * @since   2.0.0
		 * @return  string
		 */
		public function get_plugin_string() {

			return self::$plugin;
		}

		/**
		 * Add user rights and the db-table
		 *
		 * @since   2.0.0
		 * @return  void
		 */
		public static function on_activation() {

			// Check for PHP Version
			if ( ! version_compare( PHP_VERSION, '5.2.4', '>=' ) ) {
				deactivate_plugins( __FILE__ );
				wp_die(
					wp_sprintf(
						'<strong>%s:</strong> ' . __( 'Sorry, This plugin requires PHP 5.2.4' ),
						self:: get_plugin_data( 'Name' )
					)
				);
			}

			// add capability
			global $wp_roles;
			$wp_roles->add_cap( 'administrator', '_debug_objects' );

			// add table
			$table = $GLOBALS[ 'wpdb' ]->base_prefix . self::$table;

			global $wpdb;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS $table (
				called_by varchar(96) NOT NULL,
				hook_name varchar(96) NOT NULL,
				hook_type varchar(15) NOT NULL,
				first_call int(11) NOT NULL,
				arg_count tinyint(4) NOT NULL,
				file_name varchar(128) NOT NULL,
				line_num smallint NOT NULL,
				PRIMARY KEY (first_call,hook_name) )"
			);
		}

		/**
		 * Flush capabilities when plugin deactivated
		 *
		 * @since   2.0.0
		 * @return  void
		 */
		public function on_deactivation() {

			// remove retired administrator capability
			global $wp_roles;
			$wp_roles->remove_cap( 'administrator', '_debug_objects' );
		}

		/**
		 * Delete user rights and the db-table on uninstall
		 *
		 * @since   2.1.16
		 * @return  void
		 */
		public function on_uninstall() {

			unregister_setting( self::$option_string . '_group', self::$option_string );
			delete_option( self::$option_string );

			// remove retired administrator capability
			global $wp_roles;
			$wp_roles->remove_cap( 'administrator', '_debug_objects' );

			// remove hook table
			global $wpdb;
			$wpdb->query( 'DROP TABLE IF EXISTS ' . self::$table );
		}

		/**
		 * Recursive search in array for string
		 *
		 * @param  String $needle
		 * @param  Array  $haystack
		 *
		 * @return Boolean
		 */
		public function recursive_in_array( $needle, $haystack ) {

			if ( '' !== $haystack ) {
				foreach ( $haystack as $stalk ) {
					if ( $needle === $stalk || ( is_array( $stalk ) && $this->recursive_in_array( $needle, $stalk ) ) ) {
						return TRUE;
					}
				}

				return FALSE;
			}

			return FALSE;
		}

		/**
		 *  Find the position of the first occurrence of a case-insensitive substring in a array
		 *
		 * @param  String $needle
		 * @param  array  $haystack
		 *
		 * @return Boolean
		 */
		public function array_find( $needle, $haystack ) {

			foreach ( $haystack as $key => $value ) {

				if ( is_object( $value ) ) {
					$value = get_object_vars( $value );
				}

				if ( is_array( $value ) ) {
					return $this->array_find( $needle, $value );

				} else if ( FALSE !== stripos( $needle, $value ) ) {
					return TRUE;
				}
			}

			return FALSE;
		}

		/**
		 * Return undefined list as tree
		 *
		 * @since        Version 2.0.0
		 * @param        $arr
		 * @param string $root_name
		 * @param bool   $unserialized_string
		 *
		 * @return string
		 */
		public function get_as_ul_tree( $arr, $root_name = '', $unserialized_string = FALSE ) {

			global $wp_object;

			$wp_object = 0;
			$output    = '';
			$wp_object ++;

			if ( ! is_object( $arr ) && ! is_array( $arr ) ) {
				return $output;
			}

			if ( $root_name ) {
				$output .= '<ul class="root' . ( $unserialized_string ? ' unserialized' : '' ) . '">' . "\n";
				if ( is_object( $arr ) ) {
					$output .= '<li class="vt-object"><span class="' . ( $unserialized_string ? 'unserialized'
							: 'key' ) . '">' . $root_name . '</span>';
					if ( ! $unserialized_string ) {
						$output .= '<br />' . "\n";
					}
					$output .= '<small><em>type</em>: object ( ' . get_class(
							$arr
						) . ' )</small><br/><small><em>count</em>: ' . count(
							get_object_vars( $arr )
						) . '</small><ul>';
				} else {
					$output .= '<li class="vt-array"><span class="' . ( $unserialized_string ? 'unserialized'
							: 'key' ) . '">' . $root_name . '</span>';
					if ( ! $unserialized_string ) {
						$output .= '<br />' . "\n";
					}
					$output .= '<small><em>type</em>: array</small><br/><small><em>count</em>: ' . count(
							$arr
						) . '</small><ul>';
				}
			}

			foreach ( $arr as $key => $val ) {
				$wp_object ++;

				if ( is_numeric( $key ) ) {
					$key = '[' . $key . ']';
				}
				$vt = gettype( $val );
				switch ( $vt ) {
					case 'object':
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars( $key ) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt (" . get_class(
								$val
							) . ') | <em>count</em>: ' . count( $val ) . '</small>';
						if ( $val ) {
							$output .= '<ul>';
							$output .= Debug_Objects:: get_as_ul_tree( $val );
							$output .= '</ul>';
						}
						$output .= '</li>';
						break;
					case 'array':
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars( $key ) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt | <em>count</em>: " . count( $val ) . '</small>';
						if ( $val ) {
							$output .= '<ul>';
							$output .= Debug_Objects:: get_as_ul_tree( $val );
							$output .= '</ul>';
						}
						$output .= '</li>';
						break;
					case 'boolean':
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars( $key ) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">" . ( $val
								? 'TRUE' : 'FALSE' ) . '</span></li>';
						break;
					case 'integer':
					case 'double':
					case 'float':
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars( $key ) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">$val</span></li>";
						break;
					case 'string':
						$val = trim( $val );
						$val = preg_replace( '/;n;/', ';N;', $val );
						$val = str_replace( "\n", '', $val );
						$val = normalize_whitespace( $val );

						if ( is_serialized_string( $val ) ) {
							$obj = unserialize( $val );
						} else {
							$obj = normalize_whitespace( $val );
						}

						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars( $key ) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt | <em>size</em>: " . strlen(
								$val
							) . " | <em>serialized</em>: " . ( is_serialized( $val ) !== FALSE ? 'TRUE'
								: 'FALSE' ) . '</small><br/>';

						if ( is_serialized( $val ) ) {
							$output .= Debug_Objects:: get_as_ul_tree(
								$obj, '<small><em>value</em>:</small> <span class="value">[unserialized]</span>', TRUE
							);
						} else {
							if ( $val ) {
								$output .= '<small><em>value</em>: </small><span class="value">' . htmlspecialchars(
										$val
									) . '</span>';
							} else {
								$output .= '';
							}
						}

						$output .= '</li>';
						break;
					default: //what the hell is this ?
						$output .= '<li id="hook_' . $wp_object . '_' . $vt . '" class="vt-' . $vt . '"><span class="key">' . htmlspecialchars(
								$key
							) . '</span>';
						$output .= '<br/><small><em>type</em>: ' . $vt
							. '</small><br/><small><em>value</em>:</small><span class="value">'
							. htmlspecialchars(
								$val
							) . '</span></li>';
						break;
				}
			}

			if ( $root_name ) {
				$output .= "\t" . '</ul>' . "\n\t" . '</li>' . "\n" . '</ul>' . "\n";
			}

			return $output;
		}

		/**
		 * Print debug output
		 *
		 * @since     03/11/2012
		 *
		 * @param     mixed  $var
		 * @param     string $before
		 * @param     bool   $return
		 *
		 * @internal  param $mixed
		 * @return    string
		 */
		public static function pre_print( $var, $before = '', $return = FALSE ) {

			$export = var_export( $var, TRUE );
			$escape = htmlspecialchars( $export, ENT_QUOTES, 'utf-8', FALSE );

			if ( ! $return ) {
				print $before . '<pre>' . $escape . '</pre>';
			}

			return $before . '<pre>' . $escape . '</pre>';
		}

	} // end class

} // end if class exists

if ( ! function_exists( 'pre_print' ) ) {

	/**
	 * Print debug output
	 *
	 * @since     03/11/2012
	 *
	 * @param     mixed  $var
	 * @param     string $before
	 * @param     bool   $return
	 *
	 * @return    string
	 */
	function pre_print( $var, $before = '', $return = FALSE ) {

		Debug_Objects::pre_print( $var, $before, $return );
	}
}

if ( ! function_exists( 'debug_to_console' ) ) {
	/**
	 * Simple helper to debug to the console
	 *
	 * @param  object , array, string $data
	 *
	 * @return string
	 */
	function debug_to_console( $data ) {

		$output = 'console.info( \'Debug in Console via Debug Objects Plugin:\' );';
		$output .= 'console.log(' . json_encode( $data ) . ');';
		$output = sprintf( '<script>%s</script>', $output );

		echo $output;
	}
}
