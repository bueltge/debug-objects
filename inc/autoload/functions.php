<?php
/**
 * Helper Functions to use always for debugging with more formatting or output inside the console of the browser.
 *
 * @since   2017-01-15
 * @package Debug_Objects
 */

if ( ! function_exists( 'pre_print' ) ) {

	/**
	 * Print debug output
	 *
	 * @since     03/11/2012
	 *
	 * @param     mixed  $var
	 * @param     string $before
	 * @param     bool   $return
	 */
	function pre_print( $var, $before = '', $return = FALSE ) {

		Debug_Objects::pre_print( $var, $before, $return );
	}
}

if ( ! function_exists( 'debug_to_console' ) ) {
	/**
	 * Simple helper to debug to the console
	 *
	 * @param mixed  $data
	 *
	 * @param string $description
	 */
	function debug_to_console( $data, $description = '' ) {

		Debug_Objects::debug_to_console( $data, $description );
	}
}
