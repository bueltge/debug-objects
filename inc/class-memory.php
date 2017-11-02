<?php
/**
 * Add small screen with information about Memory and load Time of WP
 *
 * @package     Debug Objects
 * @subpackage  Memory and Load Time
 * @author      Frank Bültge
 * @since       2.0.1
 * @version     2017-01-22
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Memory' ) ) {
	return;
}

class Debug_Objects_Memory extends Debug_Objects {

	private static $start_time;

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Memory constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		self::$start_time = $this->get_micro_time();

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Time, Mem & Files', 'debug_objects' ),
			'function' => array( $this, 'get_output' )
		);

		return $tabs;
	}

	public function get_output( $echo = TRUE ) {

		$data = array();
		$data = array_merge( $data, $this->get_speed_data() );
		$data = array_merge( $data, $this->get_memory_data() );

		$output    = '<h4>' . esc_attr__( 'Memory & Load Time' ) . ' </h4>';
		$mem_speed = '';
		foreach ( $data as $key => $item ) {
			$mem_speed .= '<li>' . ucwords( str_replace( '_', ' ', $key ) ) . ': ' . $item . '</li>';
		}
		$output .= '<ul>' . $mem_speed . '</ul>';

		$output .= '<h4>' . esc_attr__( 'Included Files, without' )
		           . ' <code>wp-admin</code>, <code>wp-includes</code></h4>';
		$file_data   = $this->get_file_data();
		$file_totals = '';
		foreach ( (array) $file_data[ 'file_totals' ] as $key => $value ) {
			$file_totals .= '<li>' . ucwords( str_replace( '_', ' ', $key ) ) . ': ' . $value . '</li>';
		}
		$output .= '<ul>' . $file_totals . '</ul>';

		$output .= '<h4>' . esc_attr__( 'Files' ) . ' </h4>';

		$files = '';
		foreach ( (array) $file_data[ 'files' ] as $key => $value ) {
			$files .= '<tr>';
			$files .= '<td>' . ucwords(
					str_replace( '_', ' ', $key )
				) . '</td><td>' . $value[ 'name' ] . '</td><td>(' . $value[ 'size' ] . ')</td>';
			$files .= '</tr>';
		}

		$output .= '<table class="tablesorter">';
		$output .= '<thead><tr><th>' . esc_attr__( 'No' )
		           . '</th><th>' . esc_attr__( 'Path' ) . '</th><th>'
		           . esc_attr__( 'Size' ) . '</th></tr></thead>';
		$output .= $files;
		$output .= '</table>';

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

	private function get_speed_data() {

		$speed_totals                         = array();
		$speed_totals[ 'load_time' ]          = $this->get_readable_time(
			( $this->get_micro_time() - self::$start_time ) * 1000
		);
		$speed_totals[ 'max_execution_time' ] = ini_get( 'max_execution_time' ) . 's';

		return $speed_totals;
	}

	private function get_micro_time() {

		$time = microtime();
		$time = explode( ' ', $time );

		return $time[ 1 ] + $time[ 0 ];
	}

	private function get_memory_data() {

		$memory_totals                   = array();
		$memory_totals[ 'memory_used' ]  = $this->get_readable_file_size( memory_get_peak_usage() );
		$memory_totals[ 'memery_total' ] = ini_get( 'memory_limit' );

		return $memory_totals;
	}

	/**
	 * Return File Size
	 * adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
	 *
	 * @param             $size
	 * @param null|string $retstring
	 *
	 * @return string
	 */
	private function get_readable_file_size( $size, $retstring = NULL ) {

		$sizes = array( 'bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );

		if ( ! $retstring ) {
			$retstring = '%01.2f%s';
		}

		$lastsizestring = end( $sizes );

		$sizestring = '';
		foreach ( $sizes as $sizestring ) {
			if ( $size < 1024 ) {
				break;
			}
			if ( $sizestring !== $lastsizestring ) {
				$size /= 1024;
			}
		}

		if ( $sizestring === $sizes[ 0 ] ) {
			$retstring = '%01d%s';
		} // Bytes aren't normally fractional

		return sprintf( $retstring, $size, $sizestring );
	}

	private function get_readable_time( $time ) {

		$ret       = $time;
		$formatter = 0;
		$formats   = array( 'ms', 's', 'm' );
		if ( $time >= 1000 && $time < 60000 ) {
			$formatter = 1;
			$ret       = ( $time / 1000 );
		}
		if ( $time >= 60000 ) {
			$formatter = 2;
			$ret       = ( $time / 1000 ) / 60;
		}
		$ret = number_format( $ret, 3, '.', '' ) . ' ' . $formats[ $formatter ];

		return $ret;
	}

	/**
	 * Get file data.
	 *
	 * @return array
	 */
	private function get_file_data() {

		$files                  = get_included_files();
		$files_without_admin    = array();
		$files_without_includes = array();

		// remove wp-admin
		foreach ( $files as $file ) {

			if ( ! strpos( $file, 'wp-admin' ) ) {
				$files_without_admin[] = $file;
			}
			unset( $file );
		}
		$files = $files_without_admin;

		// remove wp-includes
		foreach ( $files as $file ) {

			if ( ! strpos( $file, 'wp-includes' ) ) {
				$files_without_includes[] = $file;
			}
			unset( $file );
		}
		$files = $files_without_includes;

		$filtered_files = $files;

		$file_list   = array();
		$file_totals = array(
			'total_files' => count( $filtered_files ),
			'total_size'  => 0,
			'largest'     => 0,
		);

		foreach ( $filtered_files as $key => $file ) {
			$size        = filesize( $file );
			$file_list[] = array(
				'name' => $file,
				'size' => $this->get_readable_file_size( $size )
			);
			$file_totals[ 'total_size' ] += $size;
			if ( $size > $file_totals[ 'largest' ] ) {
				$file_totals[ 'largest' ] = $size;
			}
		}

		$file_totals[ 'total_size' ] = $this->get_readable_file_size( $file_totals[ 'total_size' ] );
		$file_totals[ 'largest' ]    = $this->get_readable_file_size( $file_totals[ 'largest' ] );

		$file_data                  = array();
		$file_data[ 'file_totals' ] = $file_totals;
		$file_data[ 'files' ]       = $file_list;

		return $file_data;
	}

} // end class
