<?php
/**
 * Return different information about transients
 *
 * Praise and lot of thanks for the source of Pippin Williamson with his plugin Transient Manager,
 * save a lot of time for this class
 *
 * @see         http://pippinsplugins.com/transients-manager
 *
 * @package     Debug_Objects
 * @subpackage  Debug_Objects_Transient
 * @author      Frank Bültge <frank@bueltge.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since       2014-08-26
 * @version     2015-01-22
 *
 * Php Version 5.3
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Transient
 */
class Debug_Objects_Transient {

	/**
	 * The class object
	 *
	 * @since  2014-08-26
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * List of log entries.
	 *
	 * @type array
	 */
	protected $cache_key = 'debug_objects_transients';

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
	 * @since
	 * @return \Debug_Objects_Transient
	 */
	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return NULL;
		}

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
			'tab'      => __( 'Transients' ),
			'function' => array( $this, 'dummy' )
		);

		return $tabs;
	}

	public function dummy() {

		$transients = $this->get_transients();
		?>
		<div class="wrap">
			<h4>Total Transients</h4>

			<p><?php echo (int) count( $transients ); ?></p>
			<table class="tablesorter">
				<thead>
				<tr>
					<th><?php _e( 'ID' ); ?></th>
					<th><?php _e( 'Name' ); ?></th>
					<th><?php _e( 'Value' ); ?></th>
					<th><?php _e( 'Expires In' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( $transients ) { ?>
					<?php foreach ( $transients as $transient ) { ?>

						<tr>
							<td><?php echo $transient->option_id; ?></td>
							<td><?php echo $this->get_transient_name( $transient ); ?></td>
							<td><?php echo $this->get_transient_value( $transient ); ?></td>
							<td><?php echo $this->get_transient_expiration( $transient ); ?></td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<tr>
						<td colspan="5"><?php _e( 'No transients found' ); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

		</div>
	<?php
	}

	/**
	 * Retrieve transients from the database
	 *
	 * @access   private
	 * @return   array
	 * @internal param array $args
	 *
	 * @since    2015-01-22
	 */
	private function get_transients() {

		global $wpdb;

		$transients = wp_cache_get( $this->cache_key );

		if ( FALSE === $transients ) {

			$sql = "SELECT * FROM $wpdb->options WHERE option_name LIKE '\_transient\_%' AND option_name NOT LIKE '\_transient\_timeout%' ORDER BY option_id DESC;";

			$transients = $wpdb->get_results( $sql );

			wp_cache_set( $this->cache_key, $transients, '', 3600 );

		}

		return $transients;

	}

	/**
	 * Retrieve the transient name from the transient object
	 *
	 * @access  private
	 *
	 * @param $transient
	 *
	 * @return string
	 * @since   2015-01-22
	 */
	private function get_transient_name( $transient ) {

		return substr( $transient->option_name, 11, strlen( $transient->option_name ) );
	}

	/**
	 * Retrieve the human-friendly transient value from the transient object
	 *
	 * @access  private
	 *
	 * @param $transient
	 *
	 * @return string /int
	 * @since   2015-01-22
	 */
	private function get_transient_value( $transient ) {

		$value = maybe_unserialize( $transient->option_value );

		if ( is_array( $value ) ) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$value = Debug_Objects::pre_print( $value, '', TRUE );
		} elseif ( is_object( $value ) ) {
			$value = 'object';
		} else {
			$value = esc_attr( $value );
		}

		return $value; //wp_trim_words( $value, 5 );
	}

	/**
	 * Retrieve the expiration timestamp
	 *
	 * @access  private
	 *
	 * @param $transient
	 *
	 * @return int
	 * @since   2015-01-22
	 */
	private function get_transient_expiration_time( $transient ) {

		return get_option( '_transient_timeout_' . $this->get_transient_name( $transient ) );

	}

	/**
	 * Retrieve the human-friendly expiration time
	 *
	 * @access  private
	 *
	 * @param $transient
	 *
	 * @return string
	 * @since   2015-01-22
	 */
	private function get_transient_expiration( $transient ) {

		$time_now   = time();
		$expiration = $this->get_transient_expiration_time( $transient );

		if ( empty( $expiration ) ) {
			return __( 'Does not expire', 'debug_objects-transients-manager' );
		}

		if ( $time_now > $expiration ) {
			return __( 'Expired', 'debug_objects-transients-manager' );
		}

		return human_time_diff( $time_now, $expiration );

	}
} // end class