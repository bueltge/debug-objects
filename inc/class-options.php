<?php
/**
 * Return all options items from options-table
 *
 * @package     Debug Objects
 * @subpackage  options content
 * @author      Frank Bültge
 * @since       03/06/2014
 * @version     2017-01-25
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_Options extends Debug_Objects {

	/**
	 * The class object
	 *
	 * @since  09/24/2013
	 * @var    String
	 */
	protected static $class_object;

	protected $autoload_mu_options = array();

	/**
	 * List of options entries.
	 *
	 * @type array
	 */
	protected $autoload_options = array();

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
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
	 * @since   09/25/2013
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		$this->get_options();

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

		$tabs[] = array(
			'tab'      => esc_attr__( 'Options', 'debug_objects' ),
			'function' => array( $this, 'show_options' )
		);

		return $tabs;
	}

	/**
	 * Get options and write in variable from database.
	 */
	public function get_options() {
		global $wpdb;

		if ( is_multisite() ) {
			$this->autoload_mu_options = (array) $wpdb->get_results(
				"SELECT option_id, option_name, option_value, autoload FROM " . $wpdb->base_prefix . "options"
			);
		}

		$this->autoload_options = (array) $wpdb->get_results(
			"SELECT option_id, option_name, option_value, autoload FROM $wpdb->options"
		);
	}

	public function show_options() {

		echo '<ul><li><a href="#multisite">Multisite Options</a></li>';
		echo '<li><a href="#site">Site Options</a></li></ul>';
		echo '<hr />';

		if ( is_multisite() ) {
			echo '<h4 id="multisite">Multisite Options</h4>';
			echo $this->table_content( $this->autoload_mu_options );
		}

		echo '<h4 id="site">Site Options</h4>';
		echo $this->table_content( $this->autoload_options );
	}

	/**
	 * Format the data values in table, sortable
	 *
	 * @since   03/18/2014
	 *
	 * @param   array $data
	 *
	 * @return  string
	 */
	public function table_content( array $data ) {

		$output = '';

		$output .= '<table class="tablesorter">';
		$output .= '<thead>';
		$output .= '<tr><th>' . esc_attr__( 'ID' ) . '</th><th>'
		           . esc_attr__( 'Name' ) . '</th><th>'
		           . esc_attr__( 'Value' ) . '</th><th>'
		           . esc_attr__( 'Autoload' ) . '</th>';
		$output .= '</tr>';
		$output .= '</thead><tbody>';

		foreach ( $data as $key => $values ) {

			$class = '';

			$output .= '<tr>';
			$output .= '<td>' . $values->option_id . '</td>';

			// Check for serialized data
			if ( is_serialized( $values->option_value ) ) {

				$name = $values->option_name . ' ' . esc_attr__( '(SERIALIZED DATA)' );

				if ( is_serialized_string( $values->option_value ) ) {
					$value = maybe_unserialize( $values->option_value );
				} else {
					$value = $values->option_value;
					$class = ' class="alternate"';
				}

			} else {

				$name  = $values->option_name;
				$value = $values->option_value;
			}

			$output .= '<td' . $class . '>' . $name . '</td>';
			$output .= '<td' . $class . ' style="word-break: break-all">' . esc_attr( $value ) . '</td>';
			$output .= '<td>' . $values->autoload . '</td>';
			$output .= '</tr>';
		}

		$output .= '</tbody></table>';

		return $output;
	}
}
