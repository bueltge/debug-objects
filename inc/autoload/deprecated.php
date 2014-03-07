<?php
/**
 * Deprecated functions that are being phased out 
 *   completely or should be replaced with other functions.
 * 
 * @package     Debug Objects
 * @subpackage  Deprecated functions
 * @author      Frank Bültge
 * @since       2.1.17
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
