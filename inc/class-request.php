<?php
/**
 * .
 *
 * @package     Debug_Objects
 * @subpackage  Debug_Objects_Request
 * @author      Frank Bültge <frank@bueltge.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since       2017-04-20
 * @version     2017-11-02
 *              Php Version 5.6
 */

/**
 * Class Debug_Objects_Woocommerce
 */
class Debug_Objects_Request extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @var    string
	 */
	static protected $class_object;

	/**
	 * Store query var key, value pear if existent.
	 *
	 * @var array $vars
	 */
	private $vars = [];

	/**
	 * Store query value, if existent.
	 *
	 * @var array $query_string
	 */
	private $query_string = [];

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

		if ( ! $this->get_capability() ) {
			return;
		}

		add_action( 'wp', [ $this, 'get_query_items' ] );
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
			'tab'      => esc_attr__( 'Request', 'debug_objects' ),
			'function' => array( $this, 'print_stuff' ),
		);

		return $tabs;
	}

	/**
	 * Get the request uri as string.
	 *
	 * @return string
	 */
	public function get_request() {

		return (string) esc_url( $_SERVER['REQUEST_URI'] );
	}

	/**
	 * Filter WP Query to store only data with values.
	 */
	public function get_query_items() {
		global $wp_query;

		foreach ( $wp_query->query_vars as $key => $value ) {
			if ( ! empty( $value ) ) {
				$this->vars[ $key ] = $value;
			}
		}

		if ( ! empty( $wp_query->query ) ) {
			$this->query_string = $wp_query->query;
		}

		ksort( $this->vars );
	}

	/**
	 * Print all data in tables.
	 */
	public function print_stuff() {

	?>
	<h4><?php esc_attr_e( 'WP Query data for this request.' ); ?></h4>
	<table class="tablesorter">
		<thead>
			<tr>
				<th><?php esc_attr_e( 'Key' ); ?></th>
				<th><?php esc_attr_e( 'Value' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Request</td><td><?php echo $this->get_request(); // WPCS: XSS ok. ?></td>
			</tr>
			<tr>
				<td>Query</td><td><?php print_r( $this->query_string ); ?></td>
			</tr>
		</tbody>
	</table>

	<table class="tablesorter">
		<thead>
			<tr>
				<th><?php esc_attr_e( 'Query_vars Key' ); ?></th>
				<th><?php esc_attr_e( 'Value' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $this->vars as $key => $value ) {
			?>
			<tr>
				<td><?php echo esc_attr( $key ); ?></td>
				<td><?php print_r( $value ); ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php
	}
}