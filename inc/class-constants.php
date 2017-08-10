<?php
/**
 * Add small screen with information about constants of WP and PHP
 *
 * @package     Debug Objects
 * @subpackage  Constants
 * @author      Frank Bültge
 * @since       2.0.0
 * @version     2017-01-20
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Constants' ) ) {
	return;
}

class Debug_Objects_Constants extends Debug_Objects {

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return  Debug_Objects_Constants $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Constants constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Set string for the tab and get function to display data.
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Constants', 'debug_objects' ),
			'function' => array( $this, 'view_def_constants' )
		);

		return $tabs;
	}

	public function view_def_constants() {

		$constants = get_defined_constants();

		echo '<h4>' . esc_attr__( 'Total Actions: ', 'debug_objects' ) . count( $constants ) . '</h4>';
		echo $this->table_content( $constants );
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

		$output = '<table class="tablesorter">';
		$output .= '<thead>';
		$output .= '<tr><th>'
		           . esc_attr__( 'Constant', 'debug_objects' ) . '</th><th>'
		           . esc_attr__( 'Value', 'debug_objects' ) . '</th>';
		$output .= '</tr>';
		$output .= '</thead><tbody>';

		foreach ( $data as $key => $value ) {

			$output .= '<tr>';
			$output .= '<td>' . esc_attr( $key ) . '</td>';
			$output .= '<td>' . esc_attr( $value ) . '</td>';
			$output .= '</tr>';
		}

		$output .= '</tbody></table>';

		$allowed_html = array(
			'table' => array(
				'class' => array(),
			),
			'tr'    => array(),
			'td'    => array(),
			'th'    => array(),
			'thead' => array(),
			'tbody' => array(),
		);

		return wp_kses( $output, $allowed_html );
	}

} // end class

