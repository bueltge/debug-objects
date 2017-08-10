<?php
/**
 * Display data from the WooCommerce plugin to help to debug different thinks.
 *
 * @package     Debug_Objects
 * @subpackage  Debug_Objects_Woocommerce
 * @author      Frank Bültge <frank@bueltge.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since       2017-01-17
 * @version     2017-01-17
 *              Php Version 5.6
 */

/**
 * Class Debug_Objects_Woocommerce
 */
class Debug_Objects_Woocommerce extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @var    string
	 */
	static protected $class_object;
	private $template = array();
	private $filter;

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

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		$this->run();
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
			'tab'      => esc_attr__( 'WooCommerce', 'debug_objects' ),
			'function' => array( $this, 'print_stuff' ),
		);

		return $tabs;
	}

	/**
	 * Run on WooCommerce hooks.
	 */
	public function run() {

		add_action( 'all', array( $this, 'get_wc_hooks' ) );
		add_filter( 'template_include', array( $this, 'get_include_template' ) );
		add_action( 'woocommerce_before_template_part', array( $this, 'get_template_data' ), 10, 4 );
	}

	/**
	 * Filter global filter for hooks from WooCommerce.
	 */
	public function get_wc_hooks() {

		global $wp_current_filter;

		$needles = array( 'wc', 'woocommerce' );

		// Search needles for a string part in values of the filter array.
		foreach ( $needles as $needle ) {
			array_filter(
				$wp_current_filter,
				function( $var ) use ( $needle ) {
					if ( FALSE !== strpos( $var, $needle ) ) {
						$this->filter[] = $var;
					}
				} );
		}
	}

	/**
	 * Return the current use template.
	 *
	 * @param  string $template
	 *
	 * @return string $template
	 */
	public function get_include_template( $template ) {

		$this->template[ 'theme_template' ] = $template;

		return $template;
	}

	/**
	 * Store data from callback of hook.
	 *
	 * @param string $template_name
	 * @param string $template_path
	 * @param string $located
	 * @param array  $args
	 */
	public function get_template_data( $template_name, $template_path, $located, $args ) {

		$this->template[ 'template_name' ] = $template_name;
		$this->template[ 'template_path' ] = $template_path;
		$this->template[ 'located' ]       = $located;
		$this->template[ 'args' ]          = $args;
	}

	/**
	 * Print data inside the tab.
	 */
	public function print_stuff() {

		$this->filter = array_unique( $this->filter );
		$count = count( $this->filter );
		?>
		<div class="wrap">
			<h4><?php esc_attr_e( 'Template parts, via Hook' ); ?> <code>woocommerce_before_template_part</code></h4>
			<table class="tablesorter">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'Parameter' ); ?></th>
					<th><?php esc_attr_e( 'Value' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $this->template as $param => $value ) {
					if ( ! $value ) {
						$value = '<em>empty</em>';
					}
					?>
					<tr>
						<td><?php echo esc_attr( $param ); ?></td>
						<td><?php print_r( $value ); ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>

			<h4><?php esc_attr_e( 'Fired Hooks, filtered for \'wc\' or \'woocommerce\'' ); ?></h4>
			<table class="tablesorter">
				<thead>
				<tr>
					<th><?php
						echo (int) $count . ' ';
						esc_attr_e( 'Filter Hooks' );
						?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $this->filter as $key => $filter ) {
					?>
					<tr>
						<td><?php echo esc_attr( $filter ); ?></td>
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
