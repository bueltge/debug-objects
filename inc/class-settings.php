<?php
/**
 * Add settings page
 *
 * @package     Debug Objects
 * @subpackage  Settings
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Settings' ) ) {
	
	class Debug_Objects_Settings extends Debug_Objects {
		
		static private $classobj = NULL;
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
		 * @return  $classobj
		 */
		public function get_object() {
			
			if ( NULL === self :: $classobj ) {
				self :: $classobj = new self;
			}
			
			return self :: $classobj;
		}
		
		/**
		 * Constructor, init on defined hooks of WP and include second class
		 * 
		 * @access  public
		 * @since   0.0.2
		 * @uses    register_activation_hook, register_uninstall_hook, add_action
		 * @return  void
		 */
		public function __construct() {
			
			// textdomain from parent class
			self :: $textdomain    = parent :: get_plugin_data();
			self :: $option_string = parent :: $option_string;
			self :: $nonce_string  = parent :: get_plugin_data() . '_nonce';
			
			//register_uninstall_hook( __FILE__,       array( 'Debug_Objects_Settings', 'unregister_settings' ) );
			// settings for an active multisite
			if ( is_multisite() && is_plugin_active_for_network( parent :: $plugin ) ) {
				add_action( 'network_admin_menu',    array( __CLASS__, 'add_settings_page' ) );
				// add settings link
				add_filter( 'network_admin_plugin_action_links', array( __CLASS__, 'network_admin_plugin_action_links' ), 10, 2 );
				// save settings on network
				add_action( 'network_admin_edit_' . self :: $option_string, array( __CLASS__, 'save_network_settings_page' ) );
				// return message for update settings
				add_action( 'network_admin_notices', array( __CLASS__, 'get_network_admin_notices' ) );
			} else {
				add_action( 'admin_menu',            array( __CLASS__, 'add_settings_page' ) );
				// add settings link
				add_filter( 'plugin_action_links',   array( __CLASS__, 'plugin_action_links' ), 10, 2 );
				// use settings API
				add_action( 'admin_init',            array( __CLASS__, 'register_settings' ) );
			}
			//
			add_action( 'debug_objects_settings_page', array( __CLASS__, 'get_inside_form' ) );
			// add meta boxes on settings pages
			add_action( 'debug_objects_settings_page_sidebar', array( __CLASS__, 'get_plugin_infos' ) );
			add_action( 'debug_objects_settings_page_sidebar', array( __CLASS__, 'get_about_plugin' ) );
		}
		
		
		/**
		 * Return Textdomain string
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  string
		 */
		public function get_textdomain() {
			
			return self :: $textdomain;
		}
		
		/**
		 * Add settings link on plugins.php in backend
		 * 
		 * @uses   
		 * @access public
		 * @param  array $links, string $file
		 * @since  2.0.0
		 * @return string $links
		 */
		public function plugin_action_links( $links, $file ) {
			
			if ( parent :: get_plugin_string() == $file  )
				$links[] = '<a href="tools.php?page=debug-objects/inc/class-settings.php">' . __('Settings') . '</a>';
			
			return $links;
		}
		
		/**
		 * Add settings link on plugins.php on network admin in backend
		 * 
		 * @uses   
		 * @access public
		 * @param  array $links, string $file
		 * @since  2.0.0
		 * @return string $links
		 */
		public function network_admin_plugin_action_links( $links, $file ) {
			
			if ( parent :: get_plugin_string() == $file  )
				$links[] = '<a href="settings.php?page=debug-objects/inc/class-settings.php">' . __('Settings') . '</a>';
			
			return $links;
		}
		
		/**
		 * Add settings page in WP backend
		 * 
		 * @uses   add_options_page
		 * @access public
		 * @since  2.0.0
		 * @return void
		 */
		public function add_settings_page () {
			
			if ( is_multisite() && is_plugin_active_for_network( parent :: $plugin ) ) {
				add_submenu_page(
					'settings.php',
					parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings', self :: get_textdomain() ),
					parent :: get_plugin_data( 'Name' ),
					'manage_options',
					plugin_basename(__FILE__),
					array( __CLASS__, 'get_settings_page' )
				);
			} else {
				add_submenu_page(
					'tools.php',
					parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings', self :: get_textdomain() ),
					parent :: get_plugin_data( 'Name' ),
					'manage_options',
					plugin_basename(__FILE__),
					array( __CLASS__, 'get_settings_page' )
				);
				add_action( 'contextual_help', array( __CLASS__, 'contextual_help' ), 10, 3 );
			}
		}
		
		/**
		 * Return options as array; observed install in MU or single install
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  array
		 */
		public function return_options() {
			
			if ( is_multisite() && is_plugin_active_for_network( parent :: $plugin ) )
				$options = get_site_option( self :: $option_string );
			else
				$options = get_option( self :: $option_string );
			
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
				<?php screen_icon('options-general'); ?>
				<h2><?php echo parent :: get_plugin_data( 'Name' ); ?> <?php _e('Settings', self :: get_textdomain() ); ?></h2>
				<?php
				if ( is_multisite() && is_plugin_active_for_network( parent :: $plugin ) )
					$action = 'edit.php?action=' . self :: $option_string;
				else
					$action = 'options.php';
				?>
				<form method="post" action="<?php echo $action; ?>">
					<?php settings_fields( self :: $option_string . '_group' ); ?>
					
					<div id="poststuff" class="metabox-holder has-right-sidebar">
						
						<div class="inner-sidebar">
							<?php do_action( 'debug_objects_settings_page_sidebar', self :: return_options() ); ?>
						</div> <!-- .inner-sidebar -->
						
						<div id="post-body">
							<div id="post-body-content">
								<?php do_action( 'debug_objects_settings_page', self :: return_options() ); ?>
							</div> <!-- #post-body-content -->
						</div> <!-- #post-body -->
						
					</div> <!-- .metabox-holder -->
					
					<br class="clear" />
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
					</p>
				</form>
				
			</div>
			<?php
		}
		
		/**
		 * Echo content in form-area of backend settings page
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @param   $options array
		 * @return  void
		 */
		public function get_inside_form( $options ) {
			?>
			<table class="form-table">
				<?php
				$defaults = array(
					'Backend'          => __( 'Output in WordPress Admin Footer. <br />Alternatively use url param "<code>debug</code>" or set a cookie via url param "<code>debugcookie</code>" in days. <br />Example: <code>example.com/?debug</code>', self :: get_textdomain() ),
					'Frontend'         => __( 'Output in Footer of Frontend. <br />Alternatively use url param "<code>debug</code>" or set a cookie via url param "<code>debugcookie</code>" in days <br />Example: <code>example.com/?debugcookie=5</code>', self :: get_textdomain() ),
					'Php'              => __( 'PHP, WordPress and global Stuff', self :: get_textdomain() ),// php, WordPress, globals and more
					'Conditional_Tags' => __( 'Conditional Tags', self :: get_textdomain() ), // conditional tags
					'Theme'            => __( 'Theme and Template informations', self :: get_textdomain() ),
					'Constants'        => __( 'All Constants', self :: get_textdomain() ),// All active Constants
					'Enqueue_Stuff'    => __( 'Introduced scripts and stylesheets', self :: get_textdomain() ),// Scripts and styles
					'Debug_Hooks'      => __( 'List existing Hooks and assigned functions and count of accepted args', self :: get_textdomain() ), // Hooks, faster
					/*'Hooks'            => __( 'List existing Hooks and assigned functions', self :: get_textdomain() ),// Hooks */
					'Page_Hooks'       => __( 'Hooks of current page, very slow and use many RAM', self :: get_textdomain() ),// Hook Instrument for active page
					'Query'            => __( 'Contents of Query', self :: get_textdomain() ),// WP Queries
					'Cache'            => __( 'Contents of Cache', self :: get_textdomain() ),// WP Cache
					'Memory'           => __( 'Memory Used, Load Time and included Files' ),
					'About'            => __( 'About the plugin', self :: get_textdomain() ),// about plugin
				);
				
				$classes = apply_filters( 'debug_objects_classes', $defaults );
				
				foreach ( $classes as $class => $hint ) {
					$key = strtolower( $class );
					?>
				<tr valign="top">
					<td scope="row">
						<label for="<?php echo self :: $option_string . '_' . $key; ?>"><?php echo str_replace( '_', ' ', $class); ?></label>
					</td>
					<td><input type="checkbox" id="<?php echo self :: $option_string . '_' . $key; ?>" name="<?php echo self :: $option_string . '[' . $key . ']'; ?>" value="1" 
						<?php if ( isset( $options[$key] ) ) checked( '1', $options[$key] ); ?> />	
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
				
				<h3><span><?php _e( 'Like this plugin?', self :: get_textdomain() ); ?></span></h3>
				<div class="inside">
					<p>
						<img style="float:right;" src="<?php echo plugins_url( '/img/bug-32.png', parent::$plugin ); ?>" alt="The Bug" />
						<?php _e( 'Here\'s how you can give back:', self :: get_textdomain() ); ?></p>
					<ul>
						<li><a href="http://wordpress.org/extend/plugins/debug-objects/" title="<?php esc_attr_e( 'The Plugin on the WordPress plugin repository', self :: get_textdomain() ); ?>"><?php _e( 'Give the plugin a good rating.', self :: get_textdomain() ); ?></a></li>
						<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6069955" title="<?php esc_attr_e( 'Donate via PayPal', self :: get_textdomain() ); ?>"><?php _e( 'Donate a few euros.', self :: get_textdomain() ); ?></a></li>
						<li><a href="http://www.amazon.de/gp/registry/3NTOGEK181L23/ref=wl_s_3" title="<?php esc_attr_e( 'Frank Bültge\'s Amazon Wish List', self :: get_textdomain() ); ?>"><?php _e( 'Get me something from my wish list.', self :: get_textdomain() ); ?></a></li>
						<li><a href="https://github.com/bueltge/Debug-Objects" title="<?php esc_attr_e( 'I waiting for your pull requests!', self :: get_textdomain() ); ?>"><?php _e( 'Fork it or improve it; open issues on github.', self :: get_textdomain() ); ?></a></li>
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
				
				<h3><span><?php _e( 'About this plugin', self :: get_textdomain() ); ?></span></h3>
				<div class="inside">
					<p>
						<strong><?php _e( 'Version:', self :: get_textdomain() ); ?></strong>
						<?php echo parent :: get_plugin_data( 'Version' ); ?>
					</p>
					<p>
						<strong><?php _e( 'Description:', self :: get_textdomain() ); ?></strong>
						<?php echo parent :: get_plugin_data( 'Description' ); ?>
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
			$value = self :: validate_settings( $_POST[self :: $option_string] );
			// update options
			update_site_option( self :: $option_string, $value );
			// redirect to settings page in network
			wp_redirect(
				add_query_arg( 
					array('page' => 'debug-objects/inc/class-settings.php', 'updated' => 'true'),
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
			if ( isset( $_GET['updated'] ) && 
				 'settings_page_debug-objects/inc/class-settings-network' === $GLOBALS['current_screen'] -> id
				) {
				$message = __( 'Options saved.', self :: get_textdomain() );
				$notice  = '<div id="message" class="updated"><p>' .$message . '</p></div>';
				echo $notice;
			}
		}
		
		/**
		 * Validate settings for options
		 * 
		 * @uses    normalize_whitespace
		 * @access  public
		 * @param   array $value
		 * @since   2.0.0
		 * @return  string $value
		 */
		public function validate_settings( $values ) {
			
			if ( empty( $values ) )
				return;
			
			foreach ( $values as $key => $value ) {
				if ( isset($value[$key]) && 1 == $value[$key] )
					$value[$key] = 1;
				else 
					$value[$key] = 0;
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
			
			register_setting( self :: $option_string . '_group', self :: $option_string, array( __CLASS__, 'validate_settings' ) );
			add_option( self :: $option_string, array( 'php' => '1', 'debug_hooks' => '1', 'about' => '1' ) );
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
			
			unregister_setting( self :: $option_string . '_group', self :: $option_string );
			delete_option( self :: $option_string );
		}
		
		/**
		 * Add help text
		 * 
		 * @uses    normalize_whitespace
		 * @param   string $contextual_help
		 * @param   string $screen_id
		 * @param   string $screen
		 * @since   2.0.0
		 * @return  string $contextual_help
		 */
		public function contextual_help( $contextual_help, $screen_id, $screen ) {
				
			if ( 'settings_page_' . self :: $option_string . '_group' !== $screen_id )
				return $contextual_help;
				
			$contextual_help = 
				'<p>' . __( '' ) . '</p>';
				
			return normalize_whitespace( $contextual_help );
		}
		
	}
	
	add_action( 'plugins_loaded', array( 'Debug_Objects_Settings', 'get_object' ) );
	$Debug_Objects_Settings = Debug_Objects_Settings :: get_object();
	
} // end if class exists
