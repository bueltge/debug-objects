<?php
/**
 * Add small screen with information about enqueued scripts and style in WP
 *
 * @package     Debug Objects
 * @subpackage  Enqueued Scripts and Stylesheets
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-21
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_Enqueue_Stuff extends Debug_Objects {

	protected static $classobj;

	/**
	 * Store options of the plugin.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Enqueue_Stuff|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Enqueue_Stuff constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	public function get_conditional_tab( $tabs ) {

		$this->get_options();

		$tabs[] = array(
			'tab'      => esc_attr__( 'Scripts & Styles', 'debug_objects' ),
			'function' => array( $this, 'get_enqueued_stuff' )
		);

		return $tabs;
	}

	/**
	 * Get all stuff on enqueued scripts and styles
	 *
	 */
	public function get_enqueued_stuff() {
		global $wp_scripts, $wp_styles;

		/**
		 * Get all enqueue scripts
		 * Current is do_items() not usable, echo all scripts
		 *
		 * @see https://github.com/bueltge/Debug-Objects/issues/22#issuecomment-24728637
		 */
		$wp_scripts->all_deps( $wp_scripts->queue );
		$loaded_scripts = $wp_scripts->to_do;
		$loaded_scripts = $this->filter_debug_objects_files( $loaded_scripts );

		// Get all enqueue styles
		$loaded_styles = $wp_styles->do_items();
		$loaded_styles = $this->filter_debug_objects_files( $loaded_styles );
		?>

		<h4><?php _e( 'Enqueued Scripts' ); ?></h4>
		<table class="tablesorter">
			<thead>
			<tr>
				<th>Order</th>
				<th>Loaded</th>
				<th>Dependencies</th>
				<th>Path</th>
				<th>Version</th>
			</tr>
			</thead>
			<?php
			$i     = 1;
			foreach ( $loaded_scripts as $loaded_script ) {

				$deps         = $wp_scripts->registered[ $loaded_script ]->deps;
				$dependencies = ( count( $deps ) > 0 ) ? implode(  ', ', $deps ) : '';
				echo '<tr><td>' . $i . '</td>';
				echo '<td>' . esc_html( $loaded_script ) . '</td>';
				echo '<td>' . esc_html( $dependencies ) . '</td>';
				echo '<td>' . esc_html( $wp_scripts->registered[ $loaded_script ]->src ) . '</td>';
				echo '<td>' . esc_html( $wp_scripts->registered[ $loaded_script ]->ver ) . '</td></tr>' . "\n";

				$i ++;
			}
			?>
		</table>

		<h4><?php esc_attr_e( 'Enqueued Styles', 'debug_objects' ); ?></h4>
		<table class="tablesorter">
			<thead>
			<tr>
				<th>Order</th>
				<th>Loaded</th>
				<th>Dependencies</th>
				<th>Path</th>
				<th>Version</th>
			</tr>
			</thead>

			<?php
			$i     = 1;
			foreach ( $loaded_styles as $loaded_style ) {

				$deps         = $wp_styles->registered[ $loaded_style ]->deps;
				$dependencies = ( count( $deps ) > 0 ) ? implode( ', ', $deps ) : '';
				echo '<tr><td>' . $i . '</td>';
				echo '<td>' . esc_html( $loaded_style ) . '</td>';
				echo '<td>' . esc_html( $dependencies ) . '</td>';
				echo '<td>' . esc_html( $wp_styles->registered[ $loaded_style ]->src ) . '</td>';
				echo '<td>' . esc_html( $wp_styles->registered[ $loaded_style ]->ver ) . '</td></tr>' . "\n";

				$i ++;
			}
			?>
		</table>

		<?php
	}

	/**
	 * Store options of the plugin.
	 */
	private function get_options() {

		$this->options = Debug_Objects_Settings::return_options();
	}
	/**
	 * Filter hooks to remove the hooks from this plugin.
	 *
	 * @param  array
	 *
	 * @return array
	 */
	private function filter_debug_objects_files( array $data ) {

		if ( ! isset( $this->options[ 'filter' ] ) ) {
			return $data;
		}

		if ( 1 !== (int) $this->options[ 'filter' ] ) {
			return $data;
		}

		foreach ( $data as $count => $slug ) {
			if ( 0 === strpos( strtolower( $slug ), 'debug_objects' ) ) {
				unset( $data[ $count ] );
			}
		}

		return $data;
	}
} // end class
