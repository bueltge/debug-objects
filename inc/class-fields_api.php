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
 * @version     2016-02-01
 *
 * Php Version 5.3
 */

class Debug_Objects_Fields_API {

	/**
	 * The class object
	 *
	 * @var    string
	 */
	static protected $class_object = NULL;

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
	 *
	 */
	public function __construct() {

		// Bail if we're already in WP core (depending on the name used)
		if ( ! class_exists( 'WP_Fields_API' ) || ! class_exists( 'Fields_API' ) ) {
			return;
		}

		if ( ! current_user_can( '_debug_objects' ) ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Create tab for this data
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[ ] = array(
			'tab'      => __( 'Fields API' ),
			'function' => array( $this, 'get_fields' )
		);

		return $tabs;
	}

	public function get_fields() {
		global $wp_fields;
pre_print($wp_fields);
	}
}