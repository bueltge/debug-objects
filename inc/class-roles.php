<?php
/**
 * Get information about all roles and his capabilities.
 *
 * @package     Debug Objects
 * @subpackage  Roles and his capabilities
 * @author      Frank Bültge
 * @since       2016-03-24
 * @version     2017-01-15
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Roles
 */
class Debug_Objects_Roles extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @since  2016-03-24
	 * @var    String
	 */
	protected static $class_object;

	/**
	 * Load the object and get the current state
	 *
	 * @since   2016-03-24
	 * @return \Debug_Objects_Roles
	 */
	public static function init() {

		if ( NULL === self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since   2016-03-24
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Create tab for this data
	 *
	 * @since   2016-03-24
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_html__( 'Role Inspector' ),
			'function' => array( $this, 'print_roles' ),
		);

		return $tabs;
	}

	/**
	 * Display roles of the installation.
	 */
	public function print_roles() {

		echo '<table class="tablesorter">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Capability</th>';
		foreach ( $this->get_roles() as $role ) {
			echo '<th>' . esc_attr( $role[ 'name' ] ) . '</th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach ( $this->get_capabilities() as $capability ) {
			echo '<tr>';
			echo '<td>' . esc_attr( $capability ) . '</td>';
			foreach ( $this->get_roles() as $role ) {
				if ( array_key_exists( $capability, $role['capabilities'] ) ) {
					echo '<td class="has-capability">X</td>';
				} else {
					echo '<td class="hasnt-capability">o</td>';
				}
			}
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Get all roles of the installation.
	 *
	 * @return array
	 */
	private function get_roles() {

		return wp_roles()->roles;
	}

	/**
	 * Get all capabilities for each role.
	 *
	 * @return array
	 */
	private function get_capabilities() {

		$capabilities = array();
		foreach ( $this->get_roles() as $role ) {
			$capabilities = array_merge( array_keys( $role[ 'capabilities' ] ), $capabilities );
		}

		return array_unique( $capabilities );
	}
}
