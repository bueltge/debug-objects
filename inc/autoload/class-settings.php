<?php
/**
 * Add settings page
 *
 * @package     Debug Objects
 * @subpackage  Settings
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-04-10
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Settings
 */
class Debug_Objects_Settings extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @since  0.0.1
	 * @var    String
	 */
	protected static $classobj;

	// string for translation
	public static $textdomain;

	// string for options in table options
	public static $option_string;

	// string for nonce fields
	public static $nonce_string;

	/**
	 * Constructor, init on defined hooks of WP and include second class
	 *
	 * @since   0.0.2
	 */
	public function __construct() {

		Debug_Objects::__construct();

		// textdomain from parent class
		self::$textdomain    = parent::get_plugin_data();
		self::$option_string = parent::$option_string;
		self::$nonce_string  = parent::get_plugin_data() . '_nonce';

		register_uninstall_hook( __FILE__, array( 'Debug_Objects_Settings', 'unregister_settings' ) );
		// settings for an active multisite
		if ( is_multisite() && is_plugin_active_for_network( parent::$plugin ) ) {
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
	 * @param  array  $links
	 * @param  string $file
	 *
	 * @since  2.0.0
	 * @return string $links
	 */
	public function plugin_action_links( $links, $file ) {

		if ( parent::get_plugin_string() === $file ) {
			$links[] = '<a href="tools.php?page=' . plugin_basename( __FILE__ ) . '">' . __( 'Settings' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Add settings link on plugins.php on network admin in backend
	 *
	 * @param  array  $links
	 * @param  string $file
	 *
	 * @since  2.0.0
	 * @return string $links
	 */
	public function network_admin_plugin_action_links( $links, $file ) {

		if ( parent::get_plugin_string() === $file ) {
			$links[] = '<a href="' . network_admin_url(
					'settings.php?page=' . plugin_basename( __FILE__ )
				) . '">' . esc_html__( 'Settings' ) . '</a>';
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

		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$classes = apply_filters( 'debug_objects_css_classes', array() );

		$classes = implode( ' ', $classes );

		/** @var $wp_admin_bar WP_Admin_Bar */
		$wp_admin_bar->add_menu(
			array(
				'parent'    => 'network-admin',
				'secondary' => FALSE,
				'id'        => 'network-' . $this->get_textdomain(),
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
				            . __( ' Objects', 'debug_objects' ),
				'meta'   => array( 'class' => $classes ),
				'href'   => $href,
			)
		);
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

		if ( is_multisite() && is_plugin_active_for_network( parent::$plugin ) ) {
			add_submenu_page(
				'settings.php',
				parent::get_plugin_data( 'Name' ) . ' ' . esc_html__( 'Settings' ),
				parent::get_plugin_data( 'Name' ),
				'manage_options',
				plugin_basename( __FILE__ ),
				array( $this, 'get_settings_page' )
			);
		} else {
			add_submenu_page(
				'tools.php',
				parent::get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ),
				parent::get_plugin_data( 'Name' ),
				'manage_options',
				plugin_basename( __FILE__ ),
				array( $this, 'get_settings_page' )
			);
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
			$options = (array) get_site_option( parent::$option_string );
		} else {
			settings_fields( parent::$option_string . '_group' );
			$options = (array) get_option( parent::$option_string );
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
			<h2><?php echo esc_attr( parent::get_plugin_data( 'Name' ) ); ?> <?php esc_attr_e( 'Settings' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<?php
					$action = 'options.php';
					if ( is_multisite() && is_plugin_active_for_network( parent::$plugin ) ) {
						$action = 'edit.php?action=' . self::$option_string;
					}
					?>
					<form method="post" action="<?php echo esc_url( $action ); ?>">
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
	 * @since   2.0.0
	 *
	 * @param   array $options
	 *
	 * @return  void
	 */
	public function get_inside_form( $options ) {

		?>
		<table class="form-table">
			<?php
			$allowed_tags = array(
				'br'   => array(),
				'code' => array(),
				'a'    => array(
					'href'  => array(),
					'title' => array(),
				),
			);
			$defaults     = array(
				'Backend'               => wp_kses(
					__( 'Output in WordPress Admin Footer. <br>Alternatively use url param <code>debug</code> or set a cookie via url param <code>debugcookie</code> in days. <br>Example: <code>example.com/?debug</code>',
					    'debug_objects' ),
					$allowed_tags
				),
				'Frontend'              => wp_kses(
					__( 'Output in Footer of Frontend. <br>Alternatively use url param <code>debug</code> or set a cookie via url param <code>debugcookie</code> in days <br>Example: <code>example.com/?debugcookie=5</code>',
					    'debug_objects' ),
					$allowed_tags
				),
				'Php'                   => esc_attr__( 'PHP, WordPress and global Stuff', 'debug_objects' ),
				// php, WordPress, globals and more
				'Classes'               => esc_attr__( 'List all declared classes and his subclasses',
				                                       'debug_objects' ),
				'Functions'             => esc_attr__( 'List all defined functions', 'debug_objects' ),
				'Constants'             => esc_attr__( 'All Constants', 'debug_objects' ),
				// All active Constants
				'Rewrite_Backtrace'     => esc_attr__(
					'Filter to temporarily get a "debug object" prior to redirecting with a backtrace', 'debug_objects'
				),
				'Conditional_Tags'      => esc_attr__( 'Conditional Tags', 'debug_objects' ),
				'Roles'                 => esc_attr__( 'Role and his capabilities', 'debug_objects' ),
				'Options'               => esc_attr__( 'This tab shows a list of all saved options', 'debug_objects' ),
				'Shortcodes'            => esc_attr__( 'List all shortcodes', 'debug_objects' ),
				'Post_Meta'             => esc_attr__(
					'Get a list of arguments to custom post types and a list of post meta for the current post type',
					'debug_objects'
				),
				'Theme'                 => esc_attr__( 'Theme and Template informations', 'debug_objects' ),
				'Html_Inspector'        => wp_kses(
					__( 'HTML Inspector is a code quality tool to check markup. Any errors will be reported to the console of the browser. This works only on front end. <a href="https://github.com/philipwalton/html-inspector" title="GitHub Repository for the tool.">More information</a> about the solutions.',
					    'debug_objects' ),
					$allowed_tags
				),
				'Translation'           => esc_attr__(
					'Get translation data: language, files, possible problems.', 'debug_objects' ),
				'Enqueue_Stuff'         => esc_attr__( 'Introduced scripts and stylesheets', 'debug_objects' ),
				// Scripts and styles
				// Hooks, faster @ToDo check it is usable, better?
				//'Hooks'            => __( 'List existing Hooks and assigned functions' ), // Hooks
				'All_Hooks'             => esc_attr__( 'List all hooks, very slow and use many RAM', 'debug_objects' ),
				'Page_Hooks'            => esc_attr__( 'Hooks of current page', 'debug_objects' ),
				// Hook Instrument for active page
				'Screen_Info'           => esc_attr__(
					'Shows all the screen info for the current page from the admin backend', 'debug_objects' ),
				'Request'               => esc_attr__( 'Shows all WP queries performed on the current request.', 'debug_objects' ),
				'Db_Query'              => esc_attr__(
					'Three Tabs: Only the database queries from plugins and wp-content in each tab with runtime and a tab with content of all queries and his runtime in order of runtime on the current request.',
					'debug_objects'
				),
				// WP Queries
				'Stack_Trace'           => wp_kses(
					__( 'Stack Trace, all files and functions on each query. The Database-Query options is prerequisite. <br>A stack trace is a report of the active stack frames at a certain point in time during the execution of a program.',
					'debug_objects' ),
					$allowed_tags
				),
				'Cache'                 => esc_attr__( 'Contents of Cache', 'debug_objects' ),
				// WP Cache
				'Rewrites'              => esc_attr__( 'A list of all cached rewrites.', 'debug_objects' ),
				'Permalink_Performance' => esc_attr__( 'Analyze the performance for the permalink rule settings.',
				                                       'debug_objects' ),
				'Cron'                  => esc_attr__( 'Crons', 'debug_objects' ),
				'Transient'             => esc_attr__( 'List all transients.', 'debug_objects' ),
				'Memory'                => wp_kses(
					__( 'Memory Used, Load Time and included Files, but without all file in the folder <code>wp-admin</code>, <code>wp-includes</code>',
					'debug_objects' ),
					$allowed_tags
				),
				'Inspector'             => esc_attr__( 'Provide information about a given domain', 'debug_objects' ),
				'Default_Mode'          => wp_kses(
					__( 'Add the url-param <code>default</code>, like <code>?debug&default</code>, for run WordPress in a safe mode. Plugins are not loaded and set the default theme as active theme, is it available.',
					'debug_objects' ),
					$allowed_tags
				),
				'Filter'                => esc_attr__(
					'Filter class, hooks, scripts and styles from this plugin Debug Objects.', 'debug_objects'
				),
				'Fields_API'            => esc_attr__(
					'WordPress Fields API (Currently is this core proposal for a new wide-reaching API for WordPress core)',
					'debug_objects'
				),
				'WooCommerce'           => esc_attr__( 'A simple helper for develop and debug the WooCommerce data.', 'debug_objects' ),
				'About'                 => esc_attr__( 'About the plugin', 'debug_objects' ),
			);

			$classes          = (array) apply_filters( 'debug_objects_classes', $defaults );
			ksort( $classes, SORT_STRING );
			$disabled_options = (array) apply_filters( 'debug_objects_disabled_options', array() );

			foreach ( $classes as $class => $hint ) {
				$key = strtolower( $class );
				if ( in_array( $class, $disabled_options, TRUE ) ) {
					$disabled        = ' disabled="disabled"';
					$options[ $key ] = '1';
				} else {
					$disabled = '';
				} ?>
				<tr valign="top">
					<td scope="row" style="width: 20%;">
						<label for="<?php echo esc_attr( self::$option_string . '_' . $key ); ?>"><?php
							echo esc_attr( str_replace( '_', ' ', $class ) ); ?></label>
					</td>
					<td>
						<input type="checkbox"
							id="<?php echo esc_attr( self::$option_string . '_' . $key ); ?>" <?php
						echo esc_attr( $disabled ); ?>
							name="<?php echo esc_attr( self::$option_string . '[' . $key . ']' ); ?>" value="1"
							<?php
							if ( isset( $options[ $key ] ) ) {
								checked( '1', $options[ $key ] );
							}
							?> />
						<span class="description"><?php
							$args = array(
								'a'    => array(
									'href'  => array(),
									'title' => array(),
								),
								'code' => array(),
							);
							echo wp_kses( $hint, $args ); ?></span>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}

	/**
	 * Return information to donate
	 *
	 * @since   2.0.0
	 * @return  void
	 */
	public function get_plugin_infos() {

		?>
		<div class="postbox">

			<h3><span><?php esc_attr_e( 'Like this plugin?' ); ?></span></h3>

			<div class="inside">
				<p>
					<img style="float:right;" src="<?php echo esc_url( plugins_url(
						                                                   '/img/bug-32.png', parent::$plugin
					                                                   ) ); ?>" alt="The Bug" />
					<?php esc_attr_e( 'Here\'s how you can give back:' ); ?></p>
				<ul>
					<li><a href="http://wordpress.org/extend/plugins/debug-objects/" title="<?php esc_attr_e(
							'The Plugin on the WordPress plugin repository'
						); ?>"><?php esc_attr_e( 'Give the plugin a good rating.' ); ?></a></li>
					<li>
						<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6069955"
							title="<?php esc_attr_e(
								'Donate via PayPal'
							); ?>"><?php esc_attr_e( 'Donate a few euros.' ); ?></a></li>
					<li><a href="http://www.amazon.de/gp/registry/3NTOGEK181L23/ref=wl_s_3" title="<?php esc_attr_e(
							'Frank Bültge\'s Amazon Wish List'
						); ?>"><?php esc_attr_e( 'Get me something from my wish list.' ); ?></a></li>
					<li><a href="https://github.com/bueltge/Debug-Objects" title="<?php esc_attr_e(
							'I waiting for your pull requests!'
						); ?>"><?php esc_attr_e( 'Fork it or improve it; open issues on github.' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Return information about the plugin
	 *
	 * @uses    _e,esc_attr_e
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function get_about_plugin() {

		?>
		<div class="postbox">

			<h3><span><?php esc_attr_e( 'About this plugin' ); ?></span></h3>

			<div class="inside">
				<p>
					<strong><?php esc_attr_e( 'Version:' ); ?></strong>
					<?php echo esc_attr( parent::get_plugin_data( 'Version' ) ); ?>
				</p>

				<p>
					<strong><?php esc_attr_e( 'Description:' ); ?></strong>
					<?php
					$allowed_tags = array(
						'a' => array(
							'href' => array(),
							'title' => array(),
						),
					);
					echo wp_kses(
						parent::get_plugin_data( 'Description' ),
						$allowed_tags
					); ?>
				</p>

				<p>
					<strong><?php esc_attr_e( 'Hints:' ) ?></strong><br>
					<?php
					$allowed_tags = array(
						'em'   => array(),
						'br'   => array(),
						'code' => array(),
					);
					echo wp_kses( __( '&middot; <em>Comfort on debug output:<br> <code>pre_print( $var );</code></em><br>',
					                  'debug_objects' ), $allowed_tags );
					echo wp_kses( __( 'You can use the function <code>pre_print( $var );</code> for little bid comfort on debug output, like <code>var_dump()</code>, but more readable. More features or helpers you can activate in the settings.',
					                  'debug_objects' ), $allowed_tags ); ?>
					<br>
					<?php echo wp_kses( __( '&middot; <em>Simple Debug in Browser Console:<br> <code>debug_to_console( $data, $description = \'\' );</code></em><br>',
					                        'debug_objects' ), $allowed_tags );
					echo wp_kses( __( 'You can use the function <code>debug_to_console( $data, $description = \'\' );</code> for debug the content of a variable to your console inside the browser, simple and easy, but useful. More comfort for debug on console is the possibilities with ChromePhp, active and documented in the settings. The second param is optional for a helpful description in the console.',
					                  'debug_objects' ), $allowed_tags ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save network settings
	 *
	 * @since   2.0.0
	 * @return  void
	 */
	public function save_network_settings_page() {

		$value = [];
		if ( isset( $_POST[ self::$option_string ] ) ) {
			// validate options
			$value = $this->validate_settings( $_POST[ self::$option_string ] );
		}
		// update options
		update_site_option( self::$option_string, $value );

		// redirect to settings page in network
		wp_safe_redirect(
			add_query_arg(
				array( 'page' => plugin_basename( __FILE__ ), 'updated' => 'true' ),
				network_admin_url( 'settings.php' )
			)
		);
		exit();
	}

	/**
	 * Return string before update message
	 *
	 * @since   2.0.0
	 */
	public function get_network_admin_notices() {

		// if updated and the right page
		if ( isset( $_GET[ 'updated' ] ) && 'settings_page_Debug-Objects/inc/autoload/class-settings-network' === $GLOBALS[ 'current_screen' ]->id
		) {
			echo '<div id="message" class="updated"><p>' . esc_html__( 'Options saved.' ) . '</p></div>';
		}
	}

	/**
	 * Validate settings for options
	 *
	 * @uses     normalize_whitespace
	 * @access   public
	 *
	 * @param    array|null $values
	 *
	 * @internal param array $value
	 * @since    2.0.0
	 * @return   array|null $value
	 */
	public function validate_settings( $values = [] ) {

		foreach ( (array) $values as $key => $value ) {
			$value = (int) $value;
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
		add_option( self::$option_string, array( 'php' => '1', 'about' => '1' ) );
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
}

new Debug_Objects_Settings();
