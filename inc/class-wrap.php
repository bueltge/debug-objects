<?php
/**
 * Add area for content
 *
 * @package     Debug Objects
 * @subpackage  Markup and Hooks for include content
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-16
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Wrap
 */
class Debug_Objects_Wrap extends Debug_Objects {

	/**
	 * @var null
	 */
	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Wrap|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Include class in plugin and init all functions
	 *
	 * @access  public
	 * @since   2.0.0
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		$options = Debug_Objects_Settings::return_options();
		if ( ! isset( $options[ 'frontend' ] ) ) {
			$options[ 'frontend' ] = 0;
		}

		// check for output on frontend
		if ( 1 === (int) $options[ 'frontend' ] || $this->debug_control()
		) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_footer', array( $this, 'get_content' ), 9999 );
			add_action( 'login_footer', array( $this, 'get_content' ), 9999 );
		}

		if ( ! isset( $options[ 'backend' ] ) ) {
			$options[ 'backend' ] = 0;
		}
		// check for output on backend
		if ( 1 === (int) $options[ 'backend' ] || $this->debug_control()
		) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_footer', array( $this, 'get_content' ), 9999 );
		}
	}

	/**
	 * Enqueue stylesheets on frontend or backend
	 *
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function enqueue_styles() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';

		// right path
		$path = str_replace( '/inc/', '', plugin_dir_url( __FILE__ ) );

		wp_register_style( 'jquery-ui-css', $path . '/css/jquery-ui-demo.css' );

		wp_register_style( 'jquery-ui-wp', $path . '/css/jquery-ui-fresh.css', 'jquery-ui-css' );

		wp_register_style(
			parent::get_plugin_data() . '_jquery_dataTables',
			$path . '/css/jquery.dataTables.css',
			array(),
			FALSE,
			'screen'
		);

		wp_register_style(
			parent::get_plugin_data() . '_style',
			$path . '/css/style' . $suffix . '.css',
			array( 'jquery-ui-css', 'jquery-ui-wp', parent::get_plugin_data() . '_jquery_dataTables' ),
			FALSE,
			'screen'
		);
		wp_enqueue_style( parent::get_plugin_data() . '_style' );

	}

	/**
	 * Enqueue scripts on frontend or backend
	 *
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function enqueue_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';

		// jquery tablesorter plugin
		wp_enqueue_script(
			parent::get_plugin_data() . '_datatables',
			str_replace( '/inc/', '', plugins_url( 'js/jquery.dataTables.min.js', __DIR__ ) ),
			array( 'jquery' ),
			filemtime(
				str_replace( '/inc/', '', plugin_dir_path( __DIR__ ) . 'js/jquery.dataTables.min.js' )
			),
			TRUE
		);

		// jquery cookie plugin
		wp_enqueue_script(
			parent::get_plugin_data() . '_cookie_script',
			str_replace( '/inc/', '', plugins_url( 'js/jquery.cookie.js', __DIR__ ) ),
			array( 'jquery' ),
			filemtime(
				str_replace( '/inc/', '', plugin_dir_path( __DIR__ ) . 'js/jquery.cookie.js' )
			),
			TRUE
		);

		// Debug Objects script
		wp_enqueue_script(
			parent::get_plugin_data() . '_script',
			str_replace( '/inc/', '', plugins_url( 'js/debug_objects' . $suffix . '.js', __DIR__ ) ),
			array(
				'jquery-ui-tabs',
				parent::get_plugin_data() . '_datatables',
				parent:: get_plugin_data() . '_cookie_script',
			),
			filemtime(
				str_replace(
					'/inc/', '', plugin_dir_path( __DIR__ ) . 'js/debug_objects' . $suffix . '.js'
				)
			),
			TRUE
		);
	}

	/**
	 * Echo markup for view output
	 *
	 * @access  public
	 * @since   2.0.0
	 */
	public function get_content() {

		?>
		<div id="debugobjects">
			<div id="debugobjectstabs">
				<ul>
					<?php
					/**
					 *  Use this filter for include new tabs with content
					 * $tabs[] = array(
					 * 'tab' => __( 'Conditional Tags', parent :: get_plugin_data() ),
					 * 'class => ' your_class_name', //optional
					 * 'function' => array( __CLASS__, 'get_conditional_tags' )
					 * );
					 */
					$tabs = (array) apply_filters( 'debug_objects_tabs', $tabs = array() );
					if ( empty( $tabs ) ) {
						echo '<li>Debug Objects: No active settings.</li>';
					}

					foreach ( $tabs as $tab ) {

						if ( ! isset( $tab[ 'class' ] ) ) {
							$tab[ 'class' ] = '';
						}

						/**
						 * Filter Hook to enhance, change classes to hint to important content
						 */
						$classes = apply_filters( 'debug_objects_tab_css_classes', $tab[ 'class' ], $tab[ 'tab' ] );
						if ( is_array( $classes ) ) {
							$classes = implode( ' ', $classes );
						}

						echo '<li  class="' . esc_attr( $classes ) . '"><a href="#' . esc_html(
								tag_escape( $tab[ 'tab' ] )
							) . '">' . esc_attr( $tab[ 'tab' ] ) . '</a></li>';
					}
					?>
				</ul>

				<?php
				foreach ( $tabs as $tab ) {
					echo '<div id="' . esc_html( tag_escape( $tab[ 'tab' ] ) ) . '">';
					call_user_func( array( $tab[ 'function' ][ 0 ], $tab[ 'function' ][ 1 ] ) );
					do_action( 'debug_objects_function' . $tab[ 'tab' ] );
					echo '</div><!-- ' . esc_html( tag_escape( $tab[ 'tab' ] ) ) . '-->';
				}
				?>
			</div>
			<!-- end id=debugobjectstabs -->
		</div> <!-- end id=debugobjects -->
		<br style="clear: both;" />
		<?php
	}

} // end class
