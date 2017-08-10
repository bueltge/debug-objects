<?php
/**
 * Display data from the Fields API
 *
 * The Fields API is a core proposal for a new wide-reaching API for WordPress core.
 *
 * @see         https://github.com/sc0ttkclark/wordpress-fields-api
 *
 * @package     Debug_Objects
 * @subpackage  Debug_Objects_Fields_API
 * @author      Frank Bültge <frank@bueltge.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since       2016-02-01
 * @version     2017-01-21
 *
 * Php Version 5.6
 */

/**
 * Class Debug_Objects_Fields_API
 */
class Debug_Objects_Fields_API extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @var    string
	 */
	static protected $class_object;

	/**
	 * Load the object and get the current state
	 *
	 * @return  string $class_object
	 */
	public static function init() {

		if ( NULL === self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 */
	public function __construct() {

		parent::__construct();

		// Bail if we're already in WP core (depending on the name used)
		if ( ! class_exists( 'WP_Fields_API' ) && ! class_exists( 'Fields_API' ) ) {
			return;
		}

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Create tab for this data.
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Fields API', 'debug_objects' ),
			'function' => array( $this, 'print_stats' ),
		);

		return $tabs;
	}

	/**
	 * Return the global fields var.
	 */
	public function get_fields() {

		/** @var $wp_fields WP_Fields_API */
		global $wp_fields;

		return $wp_fields->get_stats();
	}

	/**
	 * Print the global in a table.
	 */
	public function print_stats() {

		$stats = (array) $this->get_fields();
		?>
		<div class="wrap">
			<h4>Fields API <code>global $wp_fields</code></h4>
			<table class="tablesorter">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'Field', 'debug_objects' ); ?></th>
					<th><?php esc_attr_e( 'Count', 'debug_objects' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $stats as $type => $count ) {
					?>
					<tr>
						<td><?php echo $type; ?></td>
						<td><?php echo number_format_i18n( $count ); ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>

		</div>
		<?php
	}
}
