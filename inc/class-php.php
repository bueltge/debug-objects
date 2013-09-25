<?php
/**
 * Add small screen with informations for different stuff from php, globals and WP
 *
 * @package     Debug Objects
 * @subpackage  Different Stuff
 * @author      Frank Bültge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Php' ) ) {
	class Debug_Objects_Php extends Debug_Objects {
		
		protected static $classobj = NULL;
		
		var $warnings           = array();
		var $notices            = array();
		var $messages           = array();
		var $real_error_handler = array();
	
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
		
		/**
		 * Constructor
		 * 
		 * @return  void
		 */
		public function __construct() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
			
			// @see http://php.net/manual/de/function.set-error-handler.php
			$this->real_error_handler = set_error_handler( array( $this, 'error_handler' ) );
			
			// set classes for admin bar item
			add_filter( 'debug_objects_classes', array( $this, 'get_debug_objects_classes' ) );
		}
		
		/**
		 * Get data for the generated tabs
		 * 
		 * @param  Array $tabs
		 * @return Array $tabs
		 */
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'System' ),
				'function' => array( $this, 'get_different_stuff' )
			);
			
			return $tabs;
		}
		
		/**
		 * Get different classes for admin bar item to format to see easier a problem on php
		 * 
		 * @param  Array $classes
		 * @return Array $classes
		 */
		public function get_debug_objects_classes( $classes ) {
			
			if ( 0 < count( $this->warnings ) )
				$classes[] = ' debug_objects_php_warning';
			
			if ( 0 < count( $this->notices ) )
				$classes[] = ' debug_objects_php_notice';
			
			if ( 0 < count( $this->messages ) )
				$classes[] = ' debug_objects_php_message';
			
			return $classes;
		}
		
		public function error_handler( $type, $message, $file, $line ) {
			
			$_key = md5( $file . ':' . $line . ':' . $message );
			
			switch ( $type ) {
				case E_WARNING :
				case E_USER_WARNING :
					$this->warnings[$_key] = array( $file.':'.$line, $message );
					break;
				case E_NOTICE :
				case E_USER_NOTICE :
					$this->notices[$_key] = array( $file.':'.$line, $message );
					break;
				case E_STRICT :
					// @TODO
					break;
				case E_DEPRECATED :
				case E_USER_DEPRECATED :
					// @TODO
					break;
				default:
					$this->messages[$_key] = array( $type, $message );
					break;
			}
	
			if ( null != $this->real_error_handler )
				return call_user_func( $this->real_error_handler, $type, $message, $file, $line );
			else
				return false;
		}
		
		public function get_different_stuff( $echo = TRUE ) {
			global $wpdb, $locale;
			
			$class     = '';
			$important = '';
			$output    = '';
			
			// php warnings
			if ( 0 < count( $this->warnings ) )
				$important = ' class="important"';
			else
				$important = '';
			$output .= "<h2$important>Total PHP Warnings: " . number_format( count( $this->warnings ) ) . "</h2>\n";
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
			
			// php notices
			if ( 0 < count( $this->notices ) )
				$important = ' class="important"';
			else
				$important = '';
			$output .= "<h2$important>Total PHP Notices: " . number_format( count( $this->notices ) ) . "</h2>\n";
			if ( count( $this->notices ) ) {
				echo '<ol>';
				foreach ( $this->notices as $location_message ) {
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
					list( $location, $message ) = $location_message;
					$output .= "<li$class>NOTICE: " . str_replace( ABSPATH, '', $location ) . 
						' - ' . strip_tags( $message ). "</li>";
				}
				$output .= '</ol>';
			}
			
			// default messages, unknown error type
			if ( 0 < count( $this->messages ) )
				$important = ' class="important"';
			else
				$important = '';
			$output .= "<h2$important>Total unknown Error Messages: " . number_format( count( $this->messages ) ) . "</h2>\n";
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
			
			if ( defined( 'WPLANG' ) )
				$locale = WPLANG;
			if ( empty($locale) )
				$locale = 'en_US';
			
			$class = '';
			$memory_usage = function_exists( 'memory_get_usage' ) ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
			$memory_limit = (int) ini_get( 'memory_limit' ) ;
			
			$memory_percent = '';
			if ( ! empty($memory_usage) && ! empty($memory_limit) )
				$memory_percent = round( $memory_usage / $memory_limit * 100, 0 );
			
			$os = __( 'undefined' );
			$oskey = $_SERVER['HTTP_USER_AGENT'];
			//Operating-System scan start
			if ( preg_match( '=WIN=i', $oskey) ) { //Windows
				if (preg_match( '=NT=i', $oskey) ) {
					if (preg_match( '=6.1=', $oskey) ) {
						$os = __( 'Windows 7' );
					} elseif (preg_match( '=6.0=', $oskey) ) {
						$os = __( 'Windows Vista' );
					} elseif (preg_match( '=5.1=', $oskey) ) {
						$os = __( 'Windows XP' );
					} elseif(preg_match( '=5.0=', $oskey) ) {//Windows 2000
						$os = __( 'Windows 2000' );
					}
				} else {
					if (preg_match( '=ME=', $oskey) ) { //Windows ME
						$os = __( 'Windows ME' );
					} elseif(preg_match( '=98=', $oskey) ) { //Windows 98
						$os = __( 'Windows 98' );
					} elseif(preg_match( '=95=', $oskey) ) { //Windows 95
						$os = __( 'Windows 95' );}
				}
			} elseif (preg_match( '=MAC=i', $oskey) ) { //Macintosh
				$os = __( 'Macintosh' );
			} elseif (preg_match( '=LINUX=i', $oskey) ) { //Linux
				$os = __( 'Linux' );
			} //Operating-System scan end
			
			if ( ! defined( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'Undefined' );
			elseif ( ! constant( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'OFF' );
			else
				$autosave_interval = AUTOSAVE_INTERVAL . __( 's' );
			
			if ( ! defined( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'Undefined' );
			elseif ( ! constant( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'OFF' );
			else
				$post_revisions = WP_POST_REVISIONS;
			
			if ( ! defined( 'SAVEQUERIES' ) )
				$savequeries = __( 'Undefined' );
			elseif ( ! constant( 'SAVEQUERIES' ) )
				$savequeries = __( 'OFF' );
			elseif ( SAVEQUERIES == 1 )
				$savequeries = __( 'ON' );
			
			if ( ! defined( 'WP_DEBUG' ) )
				$debug = __( 'Undefined' );
			elseif ( ! constant( 'WP_DEBUG' ) )
				$debug = __( 'OFF' );
			elseif ( WP_DEBUG == 1 )
				$debug = __( 'ON' );
				
			if ( ! defined( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'Undefined' );
			elseif ( ! constant( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'OFF' );
			elseif ( FORCE_SSL_LOGIN == 1 )
				$ssl_login = __( 'ON' );
			
			if ( ! defined( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'Undefined' );
			elseif ( ! constant( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'OFF' );
			elseif ( CONCATENATE_SCRIPTS == 1 )
					$concatenate_scripts = __( 'ON' );
			
			if ( ! defined( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'Undefined' );
			elseif ( ! constant( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'OFF' );
			elseif ( COMPRESS_SCRIPTS == 1 )
				$compress_scripts = __( 'ON' );
			
			if ( ! defined( 'COMPRESS_CSS' ) )
				$compress_css = __( 'Undefined' );
			elseif ( ! constant( 'COMPRESS_CSS' ) )
				$compress_css = __( 'OFF' );
			elseif ( COMPRESS_CSS == 1 )
				$compress_css = __( 'ON' );
			
			if ( ! defined( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'Undefined' );
			elseif ( ! constant( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'OFF' );
			elseif ( ENFORCE_GZIP == 1 )
				$enforce_gzip = __( 'ON' );
			
			if ( ini_get('safe_mode') )
				$safe_mode = __( 'On' );
			else
				$safe_mode = __( 'Off' );
			if ( ini_get('allow_url_fopen') )
				$allow_url_fopen = __( 'On');
			else
				$allow_url_fopen = __( 'Off' );
			if ( ini_get('upload_max_filesize') )
				$upload_max = ini_get('upload_max_filesize');
			else
				$upload_max = __( 'Undefined' );
			if ( ini_get('post_max_size') )
				$post_max = ini_get('post_max_size');
			else
				$post_max = __( 'Undefined' );
			if ( ini_get('max_execution_time') )
				$max_execute = ini_get('max_execution_time');
			else
				$max_execute = __( 'Undefined' );
			if ( is_callable('exif_read_data') )
				$exif = __( 'Yes' ) . ' ( Version ' . substr( phpversion('exif'), 0, 4 ) . ')';
			else
				$exif = __( 'No' );
			if ( is_callable('iptcparse') ) 
				$iptc = __('Yes');
			else
				$iptc = __( 'No' );
			if ( is_callable('xml_parser_create') )
				$xml = __('Yes');
			else
				$xml = __( 'No' );
			
			if ( function_exists( 'gd_info' ) )
				$gd = __('Yes');
			else
				$gd = __('No');
			
			if ( function_exists( 'curl_init' ) )
				$curl = __('Yes');
			else
				$curl = __('No');
			
			$output .= "\n" . '<h4>' . __( 'PHP Version &amp; System' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$php_info = array(
				__( 'PHP version:' )                              => PHP_VERSION,
				__( 'Server:' )                                   => substr( esc_attr( $_SERVER['SERVER_SOFTWARE'] ), 0, 14 ),
				__( 'Server SW:' )                                => esc_attr( $_SERVER['SERVER_SOFTWARE'] ),
				__( 'OS version:' )                               => $os,
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
				__( 'PHP GD Support' )                            => $gd,
				__( 'PHP cURL Support:' )                         => $curl,
				
			);
			// hook for more php informations
			$php_infos = apply_filters( 'debug_onjects_php_infos', $php_info );
			
			foreach ( $php_info as $name => $value ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$output .= '<li' . $class . '>' . $name . ' ' . $value . '</li>' . "\n";
			}
			$output .= '</ul>' . "\n";
			
			/**
			 * SQL informations
			 */
			$sqlversion = $wpdb->get_var( "SELECT VERSION() AS version" );
			$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
			if ( is_array($mysqlinfo) )
				$sql_mode = $mysqlinfo[0]->Value;
			if ( empty($sql_mode) )
				$sql_mode = __( 'Undefined' );
			
			$output .= "\n" . '<h4>' . __( 'MySQL' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'MySQL version:' ) . ' ' . $sqlversion . '</li>' . "\n";
			$output .= '<li>' . __( 'SQL Mode:' ) . ' ' . $sql_mode . '</li>' . "\n";
			
			/**
			 * WordPress informations
			 */
			if ( function_exists( 'is_multisite' ) ) {
				if ( is_multisite() ) {
					$ms = __( 'Yes' );
				} else {
					$ms = __( 'No' );
				}
				 
			} else $ms = __( 'Undefined' );
			
			$output .= "\n" . '<h4>' . __( 'WordPress Informations' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'Version:' ) . ' ' . get_bloginfo( 'version' ) . '</li>' . "\n";
			$output .= '<li>' . __( 'Multisite:' ) . ' ' . $ms . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Language, constant' ) . ' <code>WPLANG</code>: ' . $locale . '</li>' . "\n";
			$output .= '<li>' . __( 'Language folder, constant' ) . ' <code>WP_LANG_DIR</code>: ' . WP_LANG_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Content URL, constant' ) . ' <code>WP_CONTENT_URL</code>: ' . WP_CONTENT_URL . '</li>' . "\n";
			$output .= '<li>' . __( 'Content folder, constant' ) . ' <code>WP_CONTENT_DIR</code>: ' . WP_CONTENT_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Memory limit, constant' ) . ' <code>WP_MEMORY_LIMIT</code>: ' . WP_MEMORY_LIMIT . ' Byte</li>' . "\n";
			$output .= '<li>' . __( 'Post revision, constant' ) . ' <code>WP_POST_REVISIONS</code>: ' . $post_revisions . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Save queries, constant' ) . ' <code>SAVEQUERIES</code>: ' . $savequeries . '</li>' . "\n";
			$output .= '<li>' . __( 'Debug option, constant' ) . ' <code>WP_DEBUG</code>: ' . $debug . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'SSL Login, constant' ) . ' <code>FORCE_SSL_LOGIN</code>: ' . $ssl_login . '</li>' . "\n";
			$output .= '<li>' . __( 'Concatenate scripts, constant' ) . ' <code>CONCATENATE_SCRIPTS</code>: ' . $concatenate_scripts . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Compress scripts, constant' ) . ' <code>COMPRESS_SCRIPTS</code>: ' . $compress_scripts . '</li>' . "\n";
			$output .= '<li>' . __( 'Compress stylesheet, constant' ) . ' <code>COMPRESS_CSS</code>: ' . $compress_css . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Enforce GZIP, constant' ) . ' <code>ENFORCE_GZIP</code>: ' . $enforce_gzip . '</li>' . "\n";
			$output .= '<li>' . __( 'Autosave interval, constant' ) . ' <code>AUTOSAVE_INTERVAL</code>: ' . $autosave_interval . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'COOKIE_DOMAIN' ) )
				$cookie_domain = __( 'Undefined' );
			else
				$cookie_domain = COOKIE_DOMAIN;
				
			if ( ! defined( 'COOKIEPATH' ) )
				$cookiepath = __( 'Undefined' );
			else
				$cookiepath = COOKIEPATH;
				
			if ( ! defined( 'SITECOOKIEPATH' ) )
				$sitecookiepath = __( 'Undefined' );
			else
				$sitecookiepath = SITECOOKIEPATH;
				
			if ( ! defined( 'PLUGINS_COOKIE_PATH' ) )
				$plugins_cookie_path = __( 'Undefined' );
			else
				$plugins_cookie_path = PLUGINS_COOKIE_PATH;
				
			if ( ! defined( 'ADMIN_COOKIE_PATH' ) )
				$admin_cookie_path = __( 'Undefined' );
			else
				$admin_cookie_path = ADMIN_COOKIE_PATH;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Cookie Informations' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Cookie domain, constant' ) . ' <code>COOKIE_DOMAIN</code>: ' . $cookie_domain . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant' ) . ' <code>COOKIEPATH</code>: ' . $cookiepath . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Site cookie path, constant' ) . ' <code>SITECOOKIEPATH</code>: ' . $sitecookiepath . '</li>' . "\n";
			$output .= '<li>' . __( 'Plugin cookie path, constant' ) . ' <code>PLUGINS_COOKIE_PATH</code>: ' . $plugins_cookie_path . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Admin cookie path, constant' ) . ' <code>ADMIN_COOKIE_PATH</code>: ' . $admin_cookie_path . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_CHMOD_FILE' ) )
				$fs_chmod_file = __( 'Undefined' );
			else
				$fs_chmod_file = FS_CHMOD_FILE;
				
			if ( ! defined( 'FS_CHMOD_DIR' ) )
				$fs_chmod_dir = __( 'Undefined' );
			else
				$fs_chmod_dir = FS_CHMOD_DIR;
			
			$output .= "\n" . '<h4>' . __( 'WordPress File Permissions Informations' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'File Permissions, constant' ) . ' <code>FS_CHMOD_FILE</code>: ' . $fs_chmod_file . '</li>' . "\n";
			$output .= '<li>' . __( 'DIR Permissions, constant' ) . ' <code>FS_CHMOD_DIR</code>: ' . $fs_chmod_dir . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'CUSTOM_USER_TABLE' ) )
				$custom_user_table = __( 'Undefined' );
			else
				$custom_user_table = CUSTOM_USER_TABLE;
				
			if ( ! defined( 'CUSTOM_USER_META_TABLE' ) )
				$custom_user_meta_table = __( 'Undefined' );
			else
				$custom_user_meta_table = CUSTOM_USER_META_TABLE;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Custom User &amp; Usermeta Tables' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Custom User Table, constant' ) . ' <code>CUSTOM_USER_TABLE</code>: ' . $custom_user_table . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant' ) . ' <code>CUSTOM_USER_META_TABLE</code>: ' . $custom_user_meta_table . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_METHOD' ) )
				$fs_method = __( 'Undefined' );
			else
				$fs_method = FS_METHOD;
				
			if ( ! defined( 'FTP_BASE' ) )
				$ftp_base = __( 'Undefined' );
			else
				$ftp_base = FTP_BASE;
			
			if ( ! defined( 'FTP_CONTENT_DIR' ) )
				$ftp_content_dir = __( 'Undefined' );
			else
				$ftp_content_dir = FTP_CONTENT_DIR;
				
			if ( ! defined( 'FTP_PLUGIN_DIR' ) )
				$ftp_plugin_dir = __( 'Undefined' );
			else
				$ftp_plugin_dir = FTP_PLUGIN_DIR;
			
			if ( ! defined( 'FTP_PUBKEY' ) )
				$ftp_pubkey = __( 'Undefined' );
			else
				$ftp_pubkey = FTP_PUBKEY;
				
			if ( ! defined( 'FTP_PRIVKEY' ) )
				$ftp_privkey = __( 'Undefined' );
			else
				$ftp_privkey = FTP_PRIVKEY;
			
			if ( ! defined( 'FTP_USER' ) )
				$ftp_user = __( 'Undefined' );
			else
				$ftp_user = FTP_USER;
				
			if ( ! defined( 'FTP_PASS' ) )
				$ftp_pass = __( 'Undefined' );
			else
				$ftp_pass = FTP_PASS;
			
			if ( ! defined( 'FTP_HOST' ) )
				$ftp_host = __( 'Undefined' );
			else
				$ftp_host = FTP_HOST;
			
			$output .= "\n" . '<h4>' . __( 'WordPress FTP/SSH Informations' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Forces the filesystem method, constant' ) . ' <code>FS_METHOD</code> (<code>direct</code>, <code>ssh</code>, <code>ftpext</code> or <code>ftpsockets</code>): ' . $fs_method . '</li>' . "\n";
			$output .= '<li>' . __( 'Path to root install directory, constant' ) . ' <code>FTP_BASE</code>: ' . $ftp_base . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to wp-content directory, constant' ) . ' <code>FTP_CONTENT_DIR</code>: ' . $ftp_content_dir . '</li>' . "\n";
			$output .= '<li>' . __( 'Absolute path to plugin directory, constant' ) . ' <code>FTP_PLUGIN_DIR</code>: ' . $ftp_plugin_dir . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to SSH public key, constant' ) . ' <code>FTP_PUBKEY</code>: ' . $ftp_pubkey . '</li>' . "\n";
			$output .= '<li>' . __( 'dorector path to SSH private key, constant' ) . ' <code>FTP_PRIVKEY</code>: ' . $ftp_privkey . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'FTP or SSH username, constant' ) . ' <code>FTP_USER</code>: ' . $ftp_user . '</li>' . "\n";
			$output .= '<li>' . __( 'FTP or SSH password, constant' ) . ' <code>FTP_PASS</code>: ' . $ftp_pass . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Hostname, constant' ) . ' <code>FTP_HOST</code>: ' . $ftp_host . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'WordPress Query Informations' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Queries:' ) . ' ' . get_num_queries() . 'q';
			$output .= '</li>' . "\n";
			$output .= '<li>' . __( 'Timer stop:' ) . ' ' . timer_stop() . 's</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			// PHP_SELF
			if ( ! isset( $_SERVER['PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = __( 'Undefined' );
			if ( ! isset( $_SERVER['QUERY_STRING'] ) )
				$_SERVER['QUERY_STRING'] = __( 'Undefined' );
			if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) )
				$_SERVER['SCRIPT_FILENAME'] = __( 'Undefined' );
			if ( ! isset( $_SERVER['PHP_SELF'] ) )
				$_SERVER['PHP_SELF'] = __( 'Undefined' );
			
			$output .= "\n" . '<h4>' . __( 'Selected server and execution environment information' ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'PATH_INFO:' ) . ' ' . $_SERVER['PATH_INFO'] . '</li>';
			$output .= '<li class="alternate">' . __( 'REQUEST_URI:' ) . ' ' . $_SERVER['REQUEST_URI'] . '</li>';
			$output .= '<li>' . __( 'QUERY_STRING:' ) . ' ' . $_SERVER['QUERY_STRING'] . '</li>';
			$output .= '<li class="alternate">' . __( 'SCRIPT_NAME:' ) . ' ' . $_SERVER['SCRIPT_NAME'] . '</li>';
			$output .= '<li>' . __( 'SCRIPT_FILENAME:' ) . ' ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
			$output .= '<li class="alternate">' . __( 'PHP_SELF:' ) . ' ' . $_SERVER['PHP_SELF'] . '</li>';
			$output .= '<li>' . __( 'FILE:' ) . ' ' . __FILE__ . '</li>';
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'HTTP $_SERVER variables' ) . '</h4>' . "\n";
			if ( ! isset( $_SERVER ) || empty( $_SERVER ) )
				$output .= __( 'Undefined or empty' );
			else 
				$output .= '<li class="alternate">' . var_export( $_SERVER, TRUE ) . '</li>';
			$output .= '</ul>' . "\n";
			
			// error
			$output .= "\n" . '<h4>' . __( 'HTTP $_GET Error' ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_GET['error'] ) || empty( $_GET['error'] ) )
				$output .= __( 'Undefined or empty' );
			else
				$output .= '<li class="alternate">' . var_export( $_GET['error'], TRUE ) . '</li>';
			$output .= '</li></ul>' . "\n";
			
			// Globals 
			$output .= "\n" . '<h4>' . __( 'HTTP $_GET variables' ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_GET ) || empty( $_GET ) )
				$output .= __( 'Undefined or empty' );
			else 
				$output .= var_export( $_GET, TRUE );
			$output .= '</li></ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'HTTP $_POST variables' ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_POST ) || empty( $_POST ) )
				$output .= __( 'Undefined or empty' );
			else 
				$output .= var_export( $_POST, TRUE );
			$output .= '</li></ul>' . "\n";
			
			// cookies
			$output .= "\n" . '<h4>' . __( '$_COOKIE variables' ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_COOKIE ) || empty( $_COOKIE ) )
				$output .= __( 'Undefined or empty' );
			else 
				$output .= var_export( $_COOKIE, TRUE );
			$output .= '</li></ul>' . "\n";
			
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
} // end if class exists