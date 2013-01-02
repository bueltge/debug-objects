<?php
/**
 * Add small screen with informations for different stuff from php, globals and WP
 *
 * @package     Debug Objects
 * @subpackage  Different Stuff
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Php' ) ) {
	class Debug_Objects_Php extends Debug_Objects {
		
		protected static $classobj = NULL;
		
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
			
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}
		
		public function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'PHP, Globals &amp; WP', parent :: get_plugin_data() ),
				'function' => array( $this, 'get_different_stuff' )
			);
			
			return $tabs;
		}
		
		public function get_different_stuff( $echo = TRUE ) {
			global $wpdb, $locale;
			
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
			
			$os = __( 'undefined', parent :: get_plugin_data() );
			$oskey = $_SERVER['HTTP_USER_AGENT'];
			//Operating-System scan start
			if ( preg_match( '=WIN=i', $oskey) ) { //Windows
				if (preg_match( '=NT=i', $oskey) ) {
					if (preg_match( '=6.1=', $oskey) ) {
						$os = __( 'Windows 7', parent :: get_plugin_data() );
					} elseif (preg_match( '=6.0=', $oskey) ) {
						$os = __( 'Windows Vista', parent :: get_plugin_data() );
					} elseif (preg_match( '=5.1=', $oskey) ) {
						$os = __( 'Windows XP', parent :: get_plugin_data() );
					} elseif(preg_match( '=5.0=', $oskey) ) {//Windows 2000
						$os = __( 'Windows 2000', parent :: get_plugin_data() );
					}
				} else {
					if (preg_match( '=ME=', $oskey) ) { //Windows ME
						$os = __( 'Windows ME', parent :: get_plugin_data() );
					} elseif(preg_match( '=98=', $oskey) ) { //Windows 98
						$os = __( 'Windows 98', parent :: get_plugin_data() );
					} elseif(preg_match( '=95=', $oskey) ) { //Windows 95
						$os = __( 'Windows 95', parent :: get_plugin_data() );}
				}
			} elseif (preg_match( '=MAC=i', $oskey) ) { //Macintosh
				$os = __( 'Macintosh', parent :: get_plugin_data() );
			} elseif (preg_match( '=LINUX=i', $oskey) ) { //Linux
				$os = __( 'Linux', parent :: get_plugin_data() );
			} //Operating-System scan end
			
			if ( ! defined( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'OFF', parent :: get_plugin_data() );
			else
				$autosave_interval = AUTOSAVE_INTERVAL . __( 's', parent :: get_plugin_data() );
			
			if ( ! defined( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'OFF', parent :: get_plugin_data() );
			else
				$post_revisions = WP_POST_REVISIONS;
			
			if ( ! defined( 'SAVEQUERIES' ) )
				$savequeries = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'SAVEQUERIES' ) )
				$savequeries = __( 'OFF', parent :: get_plugin_data() );
			elseif ( SAVEQUERIES == 1 )
				$savequeries = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'WP_DEBUG' ) )
				$debug = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'WP_DEBUG' ) )
				$debug = __( 'OFF', parent :: get_plugin_data() );
			elseif ( WP_DEBUG == 1 )
				$debug = __( 'ON', parent :: get_plugin_data() );
				
			if ( ! defined( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'OFF', parent :: get_plugin_data() );
			elseif ( FORCE_SSL_LOGIN == 1 )
				$ssl_login = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'OFF', parent :: get_plugin_data() );
			elseif ( CONCATENATE_SCRIPTS == 1 )
					$concatenate_scripts = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'OFF', parent :: get_plugin_data() );
			elseif ( COMPRESS_SCRIPTS == 1 )
				$compress_scripts = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'COMPRESS_CSS' ) )
				$compress_css = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'COMPRESS_CSS' ) )
				$compress_css = __( 'OFF', parent :: get_plugin_data() );
			elseif ( COMPRESS_CSS == 1 )
				$compress_css = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'OFF', parent :: get_plugin_data() );
			elseif ( ENFORCE_GZIP == 1 )
				$enforce_gzip = __( 'ON', parent :: get_plugin_data() );
			
			if ( ini_get('safe_mode') )
				$safe_mode = __( 'On', parent :: get_plugin_data() );
			else
				$safe_mode = __( 'Off', parent :: get_plugin_data() );
			if ( ini_get('allow_url_fopen') )
				$allow_url_fopen = __( 'On', parent :: get_plugin_data());
			else
				$allow_url_fopen = __( 'Off', parent :: get_plugin_data() );
			if ( ini_get('upload_max_filesize') )
				$upload_max = ini_get('upload_max_filesize');
			else
				$upload_max = __( 'Undefined', parent :: get_plugin_data() );
			if ( ini_get('post_max_size') )
				$post_max = ini_get('post_max_size');
			else
				$post_max = __( 'Undefined', parent :: get_plugin_data() );
			if ( ini_get('max_execution_time') )
				$max_execute = ini_get('max_execution_time');
			else
				$max_execute = __( 'Undefined', parent :: get_plugin_data() );
			if ( is_callable('exif_read_data') )
				$exif = __( 'Yes', parent :: get_plugin_data() ) . ' ( Version ' . substr( phpversion('exif'), 0, 4 ) . ')';
			else
				$exif = __( 'No', parent :: get_plugin_data() );
			if ( is_callable('iptcparse') ) 
				$iptc = __('Yes');
			else
				$iptc = __( 'No', parent :: get_plugin_data() );
			if ( is_callable('xml_parser_create') )
				$xml = __('Yes');
			else
				$xml = __( 'No', parent :: get_plugin_data() );
			
			$output  = '';
			$output .= "\n" . '<h4>' . __( 'PHP Version &amp; System', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$php_info = array(
				__( 'PHP version:', parent :: get_plugin_data() )                             => PHP_VERSION,
				__( 'Server:', parent :: get_plugin_data() )                                  => substr( esc_attr( $_SERVER['SERVER_SOFTWARE'] ), 0, 14 ),
				__( 'Server SW:', parent :: get_plugin_data() )                               => esc_attr( $_SERVER['SERVER_SOFTWARE'] ),
				__( 'OS version:', parent :: get_plugin_data() )                              => $os,
				__( 'Memory usage in MByte:', parent :: get_plugin_data() )                   => $memory_usage,
				__( 'Memory limit, PHP Configuration in MByte:', parent :: get_plugin_data() ) => $memory_limit,
				__( 'Memory percent (in % of 100%):', parent :: get_plugin_data() )           => $memory_percent,
				__( 'PHP Safe Mode:', parent :: get_plugin_data() )                            => $safe_mode,
				__( 'PHP Allow URL fopen:', parent :: get_plugin_data() )                      => $allow_url_fopen,
				__( 'PHP Max Upload Size:', parent :: get_plugin_data() )                      => $upload_max,
				__( 'PHP Max Post Size:', parent :: get_plugin_data() )                        => $post_max,
				__( 'PHP Max Script Execute Time:', parent :: get_plugin_data() )              => $max_execute,
				__( 'PHP Exif support:', parent :: get_plugin_data() )                         => $exif,
				__( 'PHP IPTC support:', parent :: get_plugin_data() )                         => $iptc,
				__( 'PHP XML support:', parent :: get_plugin_data() )                          => $xml,
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
				$sql_mode = __( 'Undefined', parent :: get_plugin_data() );
			
			$output .= "\n" . '<h4>' . __( 'MySQL', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'MySQL version:', parent :: get_plugin_data() ) . ' ' . $sqlversion . '</li>' . "\n";
			$output .= '<li>' . __( 'SQL Mode:', parent :: get_plugin_data() ) . ' ' . $sql_mode . '</li>' . "\n";
			
			/**
			 * WordPress informations
			 */
			if ( function_exists( 'is_multisite' ) ) {
				if ( is_multisite() ) {
					$ms = __( 'Yes', parent :: get_plugin_data() );
				} else {
					$ms = __( 'No', parent :: get_plugin_data() );
				}
				 
			} else $ms = __( 'Undefined', parent :: get_plugin_data() );
			
			$output .= "\n" . '<h4>' . __( 'WordPress Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'Version:', parent :: get_plugin_data() ) . ' ' . get_bloginfo( 'version' ) . '</li>' . "\n";
			$output .= '<li>' . __( 'Multisite:', parent :: get_plugin_data() ) . ' ' . $ms . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Language, constant', parent :: get_plugin_data() ) . ' <code>WPLANG</code>: ' . $locale . '</li>' . "\n";
			$output .= '<li>' . __( 'Language folder, constant', parent :: get_plugin_data() ) . ' <code>WP_LANG_DIR</code>: ' . WP_LANG_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Content URL, constant', parent :: get_plugin_data() ) . ' <code>WP_CONTENT_URL</code>: ' . WP_CONTENT_URL . '</li>' . "\n";
			$output .= '<li>' . __( 'Content folder, constant', parent :: get_plugin_data() ) . ' <code>WP_CONTENT_DIR</code>: ' . WP_CONTENT_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Memory limit, constant', parent :: get_plugin_data() ) . ' <code>WP_MEMORY_LIMIT</code>: ' . WP_MEMORY_LIMIT . ' Byte</li>' . "\n";
			$output .= '<li>' . __( 'Post revision, constant', parent :: get_plugin_data() ) . ' <code>WP_POST_REVISIONS</code>: ' . $post_revisions . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Save queries, constant', parent :: get_plugin_data() ) . ' <code>SAVEQUERIES</code>: ' . $savequeries . '</li>' . "\n";
			$output .= '<li>' . __( 'Debug option, constant', parent :: get_plugin_data() ) . ' <code>WP_DEBUG</code>: ' . $debug . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'SSL Login, constant', parent :: get_plugin_data() ) . ' <code>FORCE_SSL_LOGIN</code>: ' . $ssl_login . '</li>' . "\n";
			$output .= '<li>' . __( 'Concatenate scripts, constant', parent :: get_plugin_data() ) . ' <code>CONCATENATE_SCRIPTS</code>: ' . $concatenate_scripts . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Compress scripts, constant', parent :: get_plugin_data() ) . ' <code>COMPRESS_SCRIPTS</code>: ' . $compress_scripts . '</li>' . "\n";
			$output .= '<li>' . __( 'Compress stylesheet, constant', parent :: get_plugin_data() ) . ' <code>COMPRESS_CSS</code>: ' . $compress_css . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Enforce GZIP, constant', parent :: get_plugin_data() ) . ' <code>ENFORCE_GZIP</code>: ' . $enforce_gzip . '</li>' . "\n";
			$output .= '<li>' . __( 'Autosave interval, constant', parent :: get_plugin_data() ) . ' <code>AUTOSAVE_INTERVAL</code>: ' . $autosave_interval . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'COOKIE_DOMAIN' ) )
				$cookie_domain = __( 'Undefined', parent :: get_plugin_data() );
			else
				$cookie_domain = COOKIE_DOMAIN;
				
			if ( ! defined( 'COOKIEPATH' ) )
				$cookiepath = __( 'Undefined', parent :: get_plugin_data() );
			else
				$cookiepath = COOKIEPATH;
				
			if ( ! defined( 'SITECOOKIEPATH' ) )
				$sitecookiepath = __( 'Undefined', parent :: get_plugin_data() );
			else
				$sitecookiepath = SITECOOKIEPATH;
				
			if ( ! defined( 'PLUGINS_COOKIE_PATH' ) )
				$plugins_cookie_path = __( 'Undefined', parent :: get_plugin_data() );
			else
				$plugins_cookie_path = PLUGINS_COOKIE_PATH;
				
			if ( ! defined( 'ADMIN_COOKIE_PATH' ) )
				$admin_cookie_path = __( 'Undefined', parent :: get_plugin_data() );
			else
				$admin_cookie_path = ADMIN_COOKIE_PATH;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Cookie Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Cookie domain, constant', parent :: get_plugin_data() ) . ' <code>COOKIE_DOMAIN</code>: ' . $cookie_domain . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant', parent :: get_plugin_data() ) . ' <code>COOKIEPATH</code>: ' . $cookiepath . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Site cookie path, constant', parent :: get_plugin_data() ) . ' <code>SITECOOKIEPATH</code>: ' . $sitecookiepath . '</li>' . "\n";
			$output .= '<li>' . __( 'Plugin cookie path, constant', parent :: get_plugin_data() ) . ' <code>PLUGINS_COOKIE_PATH</code>: ' . $plugins_cookie_path . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Admin cookie path, constant', parent :: get_plugin_data() ) . ' <code>ADMIN_COOKIE_PATH</code>: ' . $admin_cookie_path . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_CHMOD_FILE' ) )
				$fs_chmod_file = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_chmod_file = FS_CHMOD_FILE;
				
			if ( ! defined( 'FS_CHMOD_DIR' ) )
				$fs_chmod_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_chmod_dir = FS_CHMOD_DIR;
			
			$output .= "\n" . '<h4>' . __( 'WordPress File Permissions Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'File Permissions, constant', parent :: get_plugin_data() ) . ' <code>FS_CHMOD_FILE</code>: ' . $fs_chmod_file . '</li>' . "\n";
			$output .= '<li>' . __( 'DIR Permissions, constant', parent :: get_plugin_data() ) . ' <code>FS_CHMOD_DIR</code>: ' . $fs_chmod_dir . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'CUSTOM_USER_TABLE' ) )
				$custom_user_table = __( 'Undefined', parent :: get_plugin_data() );
			else
				$custom_user_table = CUSTOM_USER_TABLE;
				
			if ( ! defined( 'CUSTOM_USER_META_TABLE' ) )
				$custom_user_meta_table = __( 'Undefined', parent :: get_plugin_data() );
			else
				$custom_user_meta_table = CUSTOM_USER_META_TABLE;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Custom User &amp; Usermeta Tables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Custom User Table, constant', parent :: get_plugin_data() ) . ' <code>CUSTOM_USER_TABLE</code>: ' . $custom_user_table . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant', parent :: get_plugin_data() ) . ' <code>CUSTOM_USER_META_TABLE</code>: ' . $custom_user_meta_table . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_METHOD' ) )
				$fs_method = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_method = FS_METHOD;
				
			if ( ! defined( 'FTP_BASE' ) )
				$ftp_base = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_base = FTP_BASE;
			
			if ( ! defined( 'FTP_CONTENT_DIR' ) )
				$ftp_content_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_content_dir = FTP_CONTENT_DIR;
				
			if ( ! defined( 'FTP_PLUGIN_DIR' ) )
				$ftp_plugin_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_plugin_dir = FTP_PLUGIN_DIR;
			
			if ( ! defined( 'FTP_PUBKEY' ) )
				$ftp_pubkey = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_pubkey = FTP_PUBKEY;
				
			if ( ! defined( 'FTP_PRIVKEY' ) )
				$ftp_privkey = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_privkey = FTP_PRIVKEY;
			
			if ( ! defined( 'FTP_USER' ) )
				$ftp_user = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_user = FTP_USER;
				
			if ( ! defined( 'FTP_PASS' ) )
				$ftp_pass = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_pass = FTP_PASS;
			
			if ( ! defined( 'FTP_HOST' ) )
				$ftp_host = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_host = FTP_HOST;
			
			$output .= "\n" . '<h4>' . __( 'WordPress FTP/SSH Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Forces the filesystem method, constant', parent :: get_plugin_data() ) . ' <code>FS_METHOD</code> (<code>direct</code>, <code>ssh</code>, <code>ftpext</code> or <code>ftpsockets</code>): ' . $fs_method . '</li>' . "\n";
			$output .= '<li>' . __( 'Path to root install directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_BASE</code>: ' . $ftp_base . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to wp-content directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_CONTENT_DIR</code>: ' . $ftp_content_dir . '</li>' . "\n";
			$output .= '<li>' . __( 'Absolute path to plugin directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_PLUGIN_DIR</code>: ' . $ftp_plugin_dir . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to SSH public key, constant', parent :: get_plugin_data() ) . ' <code>FTP_PUBKEY</code>: ' . $ftp_pubkey . '</li>' . "\n";
			$output .= '<li>' . __( 'dorector path to SSH private key, constant', parent :: get_plugin_data() ) . ' <code>FTP_PRIVKEY</code>: ' . $ftp_privkey . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'FTP or SSH username, constant', parent :: get_plugin_data() ) . ' <code>FTP_USER</code>: ' . $ftp_user . '</li>' . "\n";
			$output .= '<li>' . __( 'FTP or SSH password, constant', parent :: get_plugin_data() ) . ' <code>FTP_PASS</code>: ' . $ftp_pass . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Hostname, constant', parent :: get_plugin_data() ) . ' <code>FTP_HOST</code>: ' . $ftp_host . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'WordPress Query Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Queries:', parent :: get_plugin_data() ) . ' ' . get_num_queries() . 'q';
			$output .= '</li>' . "\n";
			$output .= '<li>' . __( 'Timer stop:', parent :: get_plugin_data() ) . ' ' . timer_stop() . 's</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			// PHP_SELF
			if ( ! isset( $_SERVER['PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['QUERY_STRING'] ) )
				$_SERVER['QUERY_STRING'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) )
				$_SERVER['SCRIPT_FILENAME'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['PHP_SELF'] ) )
				$_SERVER['PHP_SELF'] = __( 'Undefined', parent :: get_plugin_data() );
			
			$output .= "\n" . '<h4>' . __( 'Selected server and execution environment information', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'PATH_INFO:', parent :: get_plugin_data() ) . ' ' . $_SERVER['PATH_INFO'] . '</li>';
			$output .= '<li class="alternate">' . __( 'REQUEST_URI:', parent :: get_plugin_data() ) . ' ' . $_SERVER['REQUEST_URI'] . '</li>';
			$output .= '<li>' . __( 'QUERY_STRING:', parent :: get_plugin_data() ) . ' ' . $_SERVER['QUERY_STRING'] . '</li>';
			$output .= '<li class="alternate">' . __( 'SCRIPT_NAME:', parent :: get_plugin_data() ) . ' ' . $_SERVER['SCRIPT_NAME'] . '</li>';
			$output .= '<li>' . __( 'SCRIPT_FILENAME:', parent :: get_plugin_data() ) . ' ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
			$output .= '<li class="alternate">' . __( 'PHP_SELF:', parent :: get_plugin_data() ) . ' ' . $_SERVER['PHP_SELF'] . '</li>';
			$output .= '<li>' . __( 'FILE:', parent :: get_plugin_data() ) . ' ' . __FILE__ . '</li>';
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'HTTP $_SERVER variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			if ( ! isset( $_SERVER ) || empty( $_SERVER ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else 
				$output .= '<li class="alternate">' . var_export( $_SERVER, TRUE ) . '</li>';
			$output .= '</ul>' . "\n";
			
			// error
			$output .= "\n" . '<h4>' . __( 'HTTP $_GET Error', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_GET['error'] ) || empty( $_GET['error'] ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else
				$output .= '<li class="alternate">' . var_export( $_GET['error'], TRUE ) . '</li>';
			$output .= '</li></ul>' . "\n";
			
			// Globals 
			$output .= "\n" . '<h4>' . __( 'HTTP $_GET variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_GET ) || empty( $_GET ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else 
				$output .= var_export( $_GET, TRUE );
			$output .= '</li></ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'HTTP $_POST variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_POST ) || empty( $_POST ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else 
				$output .= var_export( $_POST, TRUE );
			$output .= '</li></ul>' . "\n";
			
			// cookies
			$output .= "\n" . '<h4>' . __( '$_COOKIE variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_COOKIE ) || empty( $_COOKIE ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
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