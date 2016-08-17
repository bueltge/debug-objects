<?php
/**
 * Add settings page
 *
 * @package     Debug Objects
 * @subpackage  Settings
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2016-02-15
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Settings' ) ) {

	class Debug_Objects_Settings extends Debug_Objects {

		protected static $classobj;

		// string for translation
		public static $textdomain;

		// string for options in table options
		public static $option_string;

		// string for nonce fields
		public static $nonce_string;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @return \Debug_Objects_Settings|null $classobj
		 */
		public static function get_object() {

			NULL === self::$classobj && self::$classobj = new self();

			return self::$classobj;
		}

		/**
		 * Constructor, init on defined hooks of WP and include second class
		 *
		 * @access  public
		 * @since   0.0.2
		 * @uses    register_activation_hook, register_uninstall_hook, add_action
		 * @return \Debug_Objects_Settings
		 */
		public function __construct() {

			// textdomain from parent class
			self::$textdomain    = parent:: get_plugin_data();
			self::$option_string = parent:: $option_string;
			self::$nonce_string  = parent:: get_plugin_data() . '_nonce';

			//register_uninstall_hook( __FILE__,       array( 'Debug_Objects_Settings', 'unregister_settings' ) );
			// settings for an active multisite
			if ( is_multisite() && is_plugin_active_for_network( parent:: $plugin ) ) {
				add_action( 'network_admin_menu', array( $this, 'add_settings_page' ) );
				// add settings link
				add_filter(
					'network_admin_plugin_action_links', array( $this, 'network_admin_plugin_action_links' ), 10, 2
				);
				// save settings on network
				add_action(
					'network_admin_edit_' . self::$option_string, array( $this, 'save_network_settings_page' )
				);
				// return message for update settings
				add_action( 'network_admin_notices', array( $this, 'get_network_admin_notices' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
				// add settings link
				add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
				// use settings API
				add_action( 'admin_init', array( $this, 'register_settings' ) );
			}

			// add item on admin bar for go faster to the settings
			add_action( 'admin_bar_menu', array( $this, 'add_wp_admin_bar_item' ), 9999 );
			// content for settings page
			add_action( 'debug_objects_settings_page', array( $this, 'get_inside_form' ) );
			// add meta boxes on settings pages
			add_action( 'debug_objects_settings_page_sidebar', array( $this, 'get_plugin_infos' ) );
			add_action( 'debug_objects_settings_page_sidebar', array( $this, 'get_about_plugin' ) );
		}

		/**
		 * Return Textdomain string
		 *
		 * @access  public
		 * @since   2.0.0
		 * @return  string
		 */
		public function get_textdomain() {

			return self::$textdomain;
		}

		/**
		 * Add settings link on plugins.php in backend
		 *
		 * @uses
		 * @access public
		 *
		 * @param  array $links , string $file
		 * @param        $file
		 *
		 * @since  2.0.0
		 * @return string $links
		 */
		public function plugin_action_links( $links, $file ) {

			if ( parent:: get_plugin_string() === $file ) {
				$links[] = '<a href="tools.php?page=' . plugin_basename( __FILE__ ) . '">' . __( 'Settings' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Add settings link on plugins.php on network admin in backend
		 *
		 * @uses
		 * @access public
		 *
		 * @param  array $links , string $file
		 *
		 * @since  2.0.0
		 * @return string $links
		 */
		public function network_admin_plugin_action_links( $links, $file ) {

			if ( parent::get_plugin_string() === $file ) {
				$links[] = '<a href="' . network_admin_url(
						'settings.php?page=' . plugin_basename( __FILE__ )
					) . '">' . __( 'Settings' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Add item to admin bar.
		 *
		 * @since   07/24/2012
		 *
		 * @param   array $wp_admin_bar
		 *
		 * @return  void
		 */
		public function add_wp_admin_bar_item( $wp_admin_bar ) {

			if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
				return NULL;
			}

			// Multisite install, but active only in side of Network
			if ( is_multisite() && ! is_plugin_active_for_network( parent:: $plugin ) ) {
				return NULL;
			}

			$classes = apply_filters( 'debug_objects_css_classes', array() );

			$classes = implode( ' ', $classes );

			/** @var $wp_admin_bar WP_Admin_Bar */
			$wp_admin_bar->add_menu(
				array(
					'parent'    => 'network-admin',
					'secondary' => FALSE,
					'id'        => 'network-' . self::get_textdomain(),
					'title'     => self::get_plugin_data( 'Name' ),
					'meta'      => array( 'class' => $classes ),
					'href'      => network_admin_url( 'settings.php?page=' . plugin_basename( __FILE__ ) ),
				)
			);

			$scheme = ( is_ssl() ? 'https' : 'http' );
			$url    = $scheme . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
			$str    = array( '?debug', '&debug', '#debugobjects' );
			$url    = esc_url( str_replace( $str, '', $url ) );
			$get    = '?';
			if ( FALSE !== strpos( $url, '?' ) ) {
				$get = '&';
			}
			$href = $url . $get . 'debug#debugobjects';
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'debug_objects',
					'parent' => 'top-secondary',
					'title'  => '<img style="float:left;height:28px;" src="'
						. plugins_url( '/img/bug-32.png', parent::$plugin )
						. '" alt="The Bug" />'
						. __( ' Objects', self::get_textdomain() ),
					'meta'   => array( 'class' => $classes ),
					'href'   => $href
				)
			);
		}

		public function get_debug_objects_css_classes( $classes ) {

			return $classes;
		}

		/**
		 * Add settings page in WP backend
		 *
		 * @uses   add_options_page
		 * @access public
		 * @since  2.0.0
		 * @return void
		 */
		public function add_settings_page() {

			if ( is_multisite() && is_plugin_active_for_network( parent:: $plugin ) ) {
				add_submenu_page(
					'settings.php',
					parent:: get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ),
					parent:: get_plugin_data( 'Name' ),
					'manage_options',
					plugin_basename( __FILE__ ),
					array( $this, 'get_settings_page' )
				);
			} else {
				add_submenu_page(
					'tools.php',
					parent:: get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ),
					parent:: get_plugin_data( 'Name' ),
					'manage_options',
					plugin_basename( __FILE__ ),
					array( $this, 'get_settings_page' )
				);
				add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );
			}
		}

		/**
		 * Return options as array, observed install in MU or single install.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @return  array
		 */
		public static function return_options() {

			if ( is_multisite() && is_plugin_active_for_network( parent::$plugin ) ) {
				wp_nonce_field( self::$nonce_string );
				$options = (array) get_site_option( self::$option_string );
			} else {
				settings_fields( self::$option_string . '_group' );
				$options = (array) get_option( self::$option_string );
			}

			return $options;
		}

		/**
		 * Return form and markup on settings page
		 *
		 * @uses    settings_fields, normalize_whitespace, is_plugin_active_for_network, get_site_option, get_option
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public function get_settings_page() {

			?>
			<div class="wrap">
				<h2><?php echo parent:: get_plugin_data( 'Name' ); ?><?php _e( 'Settings' ); ?></h2>

				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<?php
						// settings.php?page=Debug-Objects/inc/class-settings.php // plugin_basename( __FILE__ );
						// $action = 'edit.php?action=' . self::$option_string;
						if ( is_multisite() && is_plugin_active_for_network( parent:: $plugin ) ) {
							$action = 'edit.php?action=' . self::$option_string;
						} else {
							$action = 'options.php';
						}
						?>
						<form method="post" action="<?php echo $action; ?>">
							<?php
							$options = self::return_options();
							?>
							<!-- main content -->
							<div id="post-body-content">
								<div class="meta-box-sortables ui-sortable">

									<?php do_action( 'debug_objects_settings_page', $options ); ?>

								</div>
								<!-- .meta-box-sortables .ui-sortable -->
							</div>
							<!-- post-body-content -->

							<!-- sidebar -->
							<div id="postbox-container-1" class="postbox-container">
								<div class="meta-box-sortables">

									<?php do_action( 'debug_objects_settings_page_sidebar', $options ); ?>

								</div>
								<!-- .meta-box-sortables -->
							</div>
							<!-- #postbox-container-1 .postbox-container -->

							<br class="clear" />
							<?php submit_button( __( 'Save Changes' ), 'button-primary', 'submit', TRUE ); ?>
						</form>

					</div>
					<!-- #post-body .metabox-holder .columns-2 -->
				</div>
				<!-- #poststuff -->

			</div> <!-- .wrap -->
			<?php
		}

		/**
		 * Echo content in form-area of backend settings page
		 *
		 * @access  public
		 * @since   2.0.0
		 *
		 * @param   $options array
		 *
		 * @return  void
		 */
		public function get_inside_form( $options ) {

			?>
			<table class="form-table">
				<?php
				$defaults = array(
					'Backend'               => __(
						'Output in WordPress Admin Footer. <br />Alternatively use url param "<code>debug</code>" or set a cookie via url param "<code>debugcookie</code>" in days. <br />Example: <code>example.com/?debug</code>'
					),
					'Frontend'              => __(
						'Output in Footer of Frontend. <br />Alternatively use url param "<code>debug</code>" or set a cookie via url param "<code>debugcookie</code>" in days <br />Example: <code>example.com/?debugcookie=5</code>'
					),
					'Php'                   => __( 'PHP, WordPress and global Stuff' ),
					// php, WordPress, globals and more
					'Classes'               => __( 'List all declared classes and his subclasses' ),
					'Functions'             => __( 'List all defined functions' ),
					'Constants'             => __( 'All Constants' ),
					// All active Constants
					'Rewrite_Backtrace'     => __(
						'Filter to temporarily get a "debug object" prior to redirecting with a backtrace'
					),
					'Conditional_Tags'      => __( 'Conditional Tags' ),
					'Roles'                 => __( 'Role and his capabilities'),
					'Options'               => __( 'This tab shows a list of all saved options' ),
					'Shortcodes'            => __( 'List all shortcodes' ),
					'Post_Meta'             => __(
						'Get a list of arguments to custom post types and a list of post meta for the current post type'
					),
					'Theme'                 => __( 'Theme and Template informations' ),
					'Html_Inspector'        => __(
						'HTML Inspector is a code quality tool to check markup. Any errors will be reported to the console of the browser. This works only on front end. <a href="https://github.com/philipwalton/html-inspector" title="GitHub Repository for the tool.">More information</a> about the solutions.'
					),
					'Translation'           => __( 'Get translation data: language, files, possible problems.' ),
					'Enqueue_Stuff'         => __( 'Introduced scripts and stylesheets' ),
					// Scripts and styles
					'Debug_Hooks'           => __(
						'List existing Hooks and assigned functions and count of accepted args'
					),
					// Hooks, faster
					//'Hooks'            => __( 'List existing Hooks and assigned functions' ), // Hooks
					'All_Hooks'             => __( 'List all hooks, very slow and use many RAM' ),
					'Page_Hooks'            => __( 'Hooks of current page' ),
					// Hook Instrument for active page
					'Screen_Info'           => __(
						'Shows all the screen info for the current page from the admin backend'
					),
					'Db_Query'              => __(
						'Three Tabs: Only the database queries from plugins and wp-content in each tab with runtime and a tab with content of all queries and his runtime in order of runtime'
					),
					// WP Queries
					'Stack_Trace'           => __(
						'Stack Trace, all files and functions on each query. The Database-Query options is prerequisite.<br />A stack trace is a report of the active stack frames at a certain point in time during the execution of a program.'
					),
					'Cache'                 => __( 'Contents of Cache' ),
					// WP Cache
					'Rewrites'              => __( 'A list of all cached rewrites.' ),
					'Permalink_Performance' => __( 'Analyze the performance for the permalink rule settings.' ),
					'Cron'                  => __( 'Crons' ),
					'Transient'             => __( 'List all transients.' ),
					'Memory'                => __(
						'Memory Used, Load Time and included Files, but without all file in the folder <code>wp-admin</code>, <code>wp-includes</code>'
					),
					'Inspector'             => __( 'Provide information about a given domain' ),
					//'Super_Var_Dump'    => __( 'A customized var_dump walker for viewing complex PHP variable data with an easy, javascript-backed nested-exploring view. Use the function <code>super_var_dump( $example_object );</code> for your debugging. More hints on <a href="https://github.com/ericandrewlewis/super-var-dump">this project</a>.' ),
					'Chromephp'             => __(
						'Logging PHP variables to Google Chrome console. You need to install the <a href="http://chromelogger.com/">Chrome Logger</a> extension. Start logging: <code>ChromePhp::log( $_SERVER );</code> More information can be found here: <a href="https://github.com/ccampbell/chromephp">github.com/ccampbell/chromephp</a>. This option is always active to load very early, before it possible to check the options.'
					),
					//'Debug'            => __( '' ),
					'Php_Error'             => __(
						'A alternative PHP Error reporting; works only with PHP 5.3. Set the url param <code>php_error</code> for all strict messages.'
					),
					'Default_Mode'          => __(
						'Add the url-param \'<code>default</code>\', like \'<code>?debug&default</code>\', for run WordPress in a safe mode. Plugins are not loaded and set the default theme as active theme, is it available.'
					),
					'Filter'                => __(
						'Filter class, hooks, scripts and styles from this plugin Debug Objects.'
					),
					'Fields_API'            => __(
						'WordPress Fields API (Currently is this core proposal for a new wide-reaching API for WordPress core)'
					),
					'About'                 => __( 'About the plugin' ),
					// about plugin
				);

				$classes          = apply_filters( 'debug_objects_classes', $defaults );
				$disabled_options = apply_filters( 'debug_objects_disabled_options', array( 'Chromephp' ) );

				foreach ( $classes as $class => $hint ) {
					$key = strtolower( $class );
					if ( in_array( $class, $disabled_options, FALSE ) ) {
						$disabled        = ' disabled="disabled"';
						$options[ $key ] = '1';
					} else {
						$disabled = '';
					} ?>
					<tr valign="top">
						<td scope="row" style="width: 20%;">
							<label for="<?php echo self::$option_string . '_' . $key; ?>"><?php echo str_replace(
									'_', ' ', $class
								); ?></label>
						</td>
						<td>
							<input type="checkbox" id="<?php echo self::$option_string . '_' . $key; ?>" <?php echo $disabled; ?> name="<?php echo self::$option_string . '[' . $key . ']'; ?>" value="1"
								<?php if ( isset( $options[ $key ] ) ) {
									checked( '1', $options[ $key ] );
								} ?> />
							<span class="description"><?php _e( $hint ); ?></span>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		/*
		 * Return informations to donate
		 * 
		 * @uses    _e,esc_attr_e
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public function get_plugin_infos() {

			?>
			<div class="postbox">

				<h3><span><?php _e( 'Like this plugin?' ); ?></span></h3>

				<div class="inside">
					<p>
						<img style="float:right;" src="<?php echo plugins_url(
							'/img/bug-32.png', parent::$plugin
						); ?>" alt="The Bug" />
						<?php _e( 'Here\'s how you can give back:' ); ?></p>
					<ul>
						<li><a href="http://wordpress.org/extend/plugins/debug-objects/" title="<?php esc_attr_e(
								'The Plugin on the WordPress plugin repository'
							); ?>"><?php _e( 'Give the plugin a good rating.' ); ?></a></li>
						<li>
							<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6069955" title="<?php esc_attr_e(
								'Donate via PayPal'
							); ?>"><?php _e( 'Donate a few euros.' ); ?></a></li>
						<li><a href="http://www.amazon.de/gp/registry/3NTOGEK181L23/ref=wl_s_3" title="<?php esc_attr_e(
								'Frank Bültge\'s Amazon Wish List'
							); ?>"><?php _e( 'Get me something from my wish list.' ); ?></a></li>
						<li><a href="https://github.com/bueltge/Debug-Objects" title="<?php esc_attr_e(
								'I waiting for your pull requests!'
							); ?>"><?php _e( 'Fork it or improve it; open issues on github.' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		/*
		 * Return informations about the plugin
		 * 
		 * @uses    _e,esc_attr_e
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public function get_about_plugin() {

			?>
			<div class="postbox">

				<h3><span><?php _e( 'About this plugin' ); ?></span></h3>

				<div class="inside">
					<p>
						<strong><?php _e( 'Version:' ); ?></strong>
						<?php echo parent:: get_plugin_data( 'Version' ); ?>
					</p>

					<p>
						<strong><?php _e( 'Description:' ); ?></strong>
						<?php echo parent:: get_plugin_data( 'Description' ); ?>
					</p>

					<p>
						<strong><?php _e( 'Hints:' ) ?></strong><br>
						<?php
						_e( '&middot; <em>Comfort on debug output:<br> <code>pre_print( $var );</code></em><br>' );
						_e(
							'You can use the function <code>pre_print( $var );</code> for little bid comfort on debug output, like <code>var_dump()</code>, but more readable. More features or helpers you can activate in the settings.'
						); ?>
						<br>
						<?php _e(
							'&middot; <em>Simple Debug in Browser Console:<br> <code>debug_to_console( $data );</code></em><br>'
						);
						_e(
							'You can use the function <code>debug_to_console( $data );</code> for debug the content of a variable to your console inside the browser, simple and easy, but useful. More comfort for debug on console is the possibilities with ChromePhp, active and documented in the settings.'
						); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/*
		 * Save network settings
		 * 
		 * @uses    update_site_option, wp_redirect, add_query_arg, network_admin_url
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public function save_network_settings_page() {

			// validate options
			$value = self::validate_settings( $_POST[ self::$option_string ] );
			// update options
			update_site_option( self::$option_string, $value );
			// redirect to settings page in network
			wp_redirect(
				add_query_arg(
					array( 'page' => plugin_basename( __FILE__ ), 'updated' => 'true' ),
					network_admin_url( 'settings.php' )
				)
			);
			exit();
		}

		/*
		 * Retrun string before update message
		 * 
		 * @uses   
		 * @access  public
		 * @since   2.0.0
		 * @return  string $notice
		 */
		public function get_network_admin_notices() {

			// if updated and the right page
			if ( isset( $_GET[ 'updated' ] ) && 'settings_page_debug-objects/inc/class-settings-network' === $GLOBALS[ 'current_screen' ]->id
			) {
				$message = __( 'Options saved.' );
				$notice  = '<div id="message" class="updated"><p>' . $message . '</p></div>';
				echo $notice;
			}
		}

		/**
		 * Validate settings for options
		 *
		 * @uses     normalize_whitespace
		 * @access   public
		 *
		 * @param $values
		 *
		 * @internal param array $value
		 * @since    2.0.0
		 * @return  string $value
		 */
		public function validate_settings( $values ) {

			foreach ( (array) $values as $key => $value ) {

				if ( isset( $value[ $key ] ) ) {

					if ( 1 === $value[ $key ] ) {
						$value[ $key ] = 1;
					} else {
						$value[ $key ] = 0;
					}
				}
			}

			return $values;
		}

		/**
		 * Register settings for options
		 *
		 * @uses    register_setting
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public function register_settings() {

			register_setting(
				self::$option_string . '_group', self::$option_string, array( $this, 'validate_settings' )
			);
			add_option( self::$option_string, array( 'php' => '1', 'debug_hooks' => '1', 'about' => '1' ) );
		}

		/**
		 * Unregister and delete settings; clean database
		 *
		 * @uses    unregister_setting, delete_option
		 * @access  public
		 * @since   0.0.2
		 * @return  void
		 */
		public function unregister_settings() {

			unregister_setting( self::$option_string . '_group', self::$option_string );
			delete_option( self::$option_string );
		}

		/**
		 * Add help text
		 *
		 * @uses    normalize_whitespace
		 *
		 * @param   string $contextual_help
		 * @param   string $screen_id
		 * @param   string $screen
		 *
		 * @since   2.0.0
		 * @return  string $contextual_help
		 */
		public function contextual_help( $contextual_help, $screen_id, $screen ) {

			if ( 'settings_page_' . self::$option_string . '_group' !== $screen_id ) {
				return $contextual_help;
			}

			$contextual_help =
				'<p>' . __( '' ) . '</p>';

			return normalize_whitespace( $contextual_help );
		}

	}

	$Debug_Objects_Settings = Debug_Objects_Settings::get_object();

} // end if class exists
