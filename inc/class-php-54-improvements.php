<?php

/**
 * Adding PHP 5.4.x specific improvements for the Debug Objects capabilities
 *
 * PHP Version 5.4
 *
 * @package    Debug_Objects
 * @subpackage Debug_Objects_Php54
 * @since      09/13/2013  2.1.16
 * @author     nofearinc, frank@bueltge.de
 */
 
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

/**
 * Class Debug_Objects_Php54
 *
 * PHP Version 5.4
 */
class Debug_Objects_Php54 {
	
	/**
	 * Add all required filters/actions
	 */
	public function __construct() {
		
		add_filter( 'debug_objects_sort_queries', array( $this, 'sort_queries' ), 10, 2 );
	}
	
	/**
	 * Sort queries for the class-query listing if PHP 5.4 is active
	 *   (using shorthand for arrays)
	 *
	 * @param array $queries WP_Query array
	 * @param int|false $sorting
	 *
	 * @return array $queries sorted queries list
	 */
	public function sort_queries( $queries, $sorting ) {
		
		if ( ! empty( $sorting ) || ! $sorting )
			usort( $queries, self::make_comparer( [1, $sorting] ) );
			
		return $queries;
	}
	
	/**
	 * Sorting of Multidimensional arrays
	 * Only >= PHP 5.4
	 *
	 * @since   08/18/2013
	 * @see     http://stackoverflow.com/questions/96759/how-do-i-sort-a-multidimensional-array-in-php
	 * @return  Boolean
	 */
	public static function make_comparer() {
		// Normalize criteria up front so that the comparer finds everything tidy
		$criteria = func_get_args();
		foreach ($criteria as $index => $criterion) {
			$criteria[$index] = is_array($criterion)
			? array_pad($criterion, 3, null)
			: array($criterion, SORT_ASC, null);
		}
			
		return function( $first, $second ) use ( $criteria ) {
	
			foreach ($criteria as $criterion) {
				// How will we compare this round?
				list($column, $sortOrder, $projection) = $criterion;
				$sortOrder = $sortOrder === SORT_DESC ? -1 : 1;
					
				// If a projection was defined project the values now
				if ($projection) {
					$lhs = call_user_func($projection, $first[$column]);
					$rhs = call_user_func($projection, $second[$column]);
				} else {
					$lhs = $first[$column];
					$rhs = $second[$column];
				}
					
				// Do the actual comparison; do not return if equal
				if ($lhs < $rhs) {
					return -1 * $sortOrder;
				} else if ($lhs > $rhs) {
					return 1 * $sortOrder;
				}
			}
			
			return 0; // tiebreakers exhausted, so $first == $second
		};
	}
	
	/**
	 * Sorting of Multidimensional arrays
	 * Hint: Slow and inefficient which to much foreach, usort is a better way
	 *
	 * @since   08/18/2013
	 * @see     http://stackoverflow.com/questions/2699086/sort-multidimensional-array-by-value-2
	 * @param   Array,  Input array
	 * @param   String, key in array
	 */
	public function aasort( &$array, $key ) {
			
		$sorter = array();
		$ret    = array();
		reset( $array );
		
		foreach( $array as $ii => $va ) {
			$sorter[$ii] = $va[$key];
		}
		asort( $sorter );
			
		foreach( $sorter as $ii => $va ) {
			$ret[$ii] = $array[$ii];
		}
		$array = $ret;
	}
	
}

new Debug_Objects_Php54();