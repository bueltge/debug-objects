<?php
/**
 * Add small screen with information about queries of WP
 *
 * @package        Debug Queries
 * @subpackage     Cache
 * @author         Frank Bültge
 * @since          2.0.0
 * @version        2017-01-20
 */

if ( ! function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

// disable mySQL Session Cache
if ( ! defined( 'QUERY_CACHE_TYPE_OFF' ) ) {
	define( 'QUERY_CACHE_TYPE_OFF', TRUE );
}

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', TRUE );
}

if ( ! defined( 'STACKTRACE' ) ) {
	define( 'STACKTRACE', FALSE );
}

if ( ! defined( 'SQL_FORMATTING' ) ) {
	define( 'SQL_FORMATTING', TRUE );
}

if ( SQL_FORMATTING ) {
	require_once __DIR__ . '/SqlFormatter/SqlFormatter.php';
}

class Debug_Objects_Db_Query extends Debug_Objects {

	private $replaced_functions = array( 'require_once', 'require', 'include', 'include_once' );

	private $replaced_actions = array( 'do_action, call_user_func_array' );

	/**
	 * Stored Backtrace Data from hooked query
	 *
	 * @var   array
	 */
	protected $_query = array();

	protected $_content_query = array();

	/**
	 * Stored Backtrace Data from global query
	 *
	 * @var   array
	 */
	protected $_queries = array();

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return \Debug_Objects_Db_Query|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Db_Query constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'query', array( $this, 'store_queries' ) );
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	/**
	 * Add Tabs and his context to output
	 *
	 * @param   array
	 *
	 * @return  array
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'DB Queries' ),
			'function' => array( $this, 'get_queries' )
		);

		$tabs[] = array(
			'tab'      => esc_attr__( 'Plugin DB Queries' ),
			'function' => array( $this, 'render_wp_plugins_data' )
		);

		$tabs[] = array(
			'tab'      => esc_attr__( 'WP_Content DB Queries' ),
			'function' => array( $this, 'render_wp_content_data' )
		);

		return $tabs;
	}

	/**
	 * Filters wpdb::query
	 * This filter stores all queries and their backtraces for later use
	 *
	 * @param  string $query
	 *
	 * @return string
	 */
	public function store_queries( $query ) {

		$trace = debug_backtrace();
		array_splice( $trace, 0, 3 ); // Get rid of the tracer's fingerprint (and wpdb::query)

		$this->_query[] = $this->_content_query[] = array(
			'query'     => $query,
			'backtrace' => $trace
		);

		return $query;
	}

	/**
	 * Map Plugins to the queries and create array with data
	 *
	 * @return  array  All data to query, if is a plugin query
	 */
	public function validate_wp_plugins_to_query() {

		global $wpdb;

		// Gather data about existing plugins
		$root_data = array();
		foreach ( get_plugins() as $filename => $data ) {
			list( $root ) = explode( '/', $filename, 2 );
			$root_data[ $root ] = array_change_key_case( $data );
		}

		// set var with query data
		$raw_data = $this->_query;
		// clear var
		$this->_query = array();

		$query_counter = 0;
		foreach ( $raw_data as $key => $data ) {

			foreach ( (array) $data[ 'backtrace' ] as $call ) {

				$function_chain[] = ( isset( $call[ 'class' ] ) ? "{$call['class']}::"
						: '' ) . $call[ 'function' ];

				// same strings in local and web envirement
				$wp_plugin_dir = str_replace( '\\', '/', WP_PLUGIN_DIR );
				if ( ! empty( $call[ 'file' ] ) ) {
					$call[ 'file' ] = str_replace( '\\', '/', $call[ 'file' ] );
				}

				// if is a plugin
				if ( ! empty( $call[ 'file' ] )
				     && FALSE !== strpos( $call[ 'file' ], $wp_plugin_dir )
				     && FALSE === strpos( $call[ 'file' ], 'Debug-Objects' )
				) {

					// get only the plugin file path, without plugin dir
					list( $root ) = explode( '/', plugin_basename( $call[ 'file' ] ), 2 );
					$file = str_replace( $wp_plugin_dir, '', $call[ 'file' ] );

					// Make sure the array is set up
					if ( ! isset( $this->_query[ $root ] ) ) {
						$this->_query[ $root ]                = $root_data[ $root ];
						$this->_query[ $root ][ 'backtrace' ] = array();
					}

					// Make sure the backtrace for this file is set up
					if ( ! isset( $this->_query[ $root ][ 'backtrace' ][ $file ] ) ) {
						$this->_query[ $root ][ 'backtrace' ][ $file ] = array();
					}

					$data[ 'time' ] = 'FALSE';
					// add time stamp of query
					foreach ( $wpdb->queries as $query => $arr ) {
						if ( FALSE !== strpos( $arr[ 0 ], $data[ 'query' ] ) ) {
							$data[ 'time' ] = $arr[ 1 ];
						}
					}

					// Save parsed data
					$this->_query[ $root ][ 'backtrace' ][ $file ][] = array(
						'line'           => $call[ 'line' ],
						'query'          => $data[ 'query' ],
						'time'           => $data[ 'time' ],
						'function_chain' => array_reverse( $function_chain ),
					);

					// add 1 to query counter
					$query_counter ++;
				}

			}

		}

		// sorting
		usort( $this->_query, array( $this, 'sort_by_name' ) );
		$this->_query[ 'query_count' ] = $query_counter;

		return $this->_query;
	}

	/**
	 * Map Queries from wp-content, typical from Theme, and create array with data
	 *
	 * @return  array  All data to query, if is a query from wp-content
	 */
	public function validate_wp_content_to_query() {

		global $wpdb;

		// set var with query data
		$raw_data = $this->_content_query;
		// clear var
		$this->_content_query = array();

		$query_counter = 0;
		foreach ( $raw_data as $key => $data ) {

			foreach ( (array) $data[ 'backtrace' ] as $call ) {

				$function_chain[] = ( isset( $call[ 'class' ] ) ? "{$call['class']}::"
						: '' ) . $call[ 'function' ];

				// Same strings in local and web envirement
				$wp_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );

				// Same strings in local and web envirement
				if ( ! empty( $call[ 'file' ] ) ) {
					$call[ 'file' ] = str_replace( '\\', '/', $call[ 'file' ] );
				}

				// If is a part from wp-content
				if ( ! empty( $call[ 'file' ] )
				     && FALSE !== strpos( $call[ 'file' ], $wp_content_dir )
				) {

					$data[ 'time' ] = 'FALSE';
					// add time stamp of query
					foreach ( (array) $wpdb->queries as $query => $arr ) {
						if ( FALSE !== strpos( $arr[ 0 ], $data[ 'query' ] ) ) {
							$data[ 'time' ] = $arr[ 1 ];
						}
					}

					// Save parsed data
					$this->_content_query[] = array(
						'file'           => $call[ 'file' ],
						'line'           => $call[ 'line' ],
						'function'       => $call[ 'function' ],
						'query'          => $data[ 'query' ],
						'time'           => $data[ 'time' ],
						'function_chain' => array_reverse( $function_chain ),
					);

					// add 1 to query counter
					$query_counter ++;
				}

			}

		}

		$this->_content_query[ 'query_count' ] = $query_counter;

		return $this->_content_query;
	}

	/**
	 * Render tracer's data
	 *
	 * @param array $data
	 */
	public function render_wp_plugins_data( $data = NULL ) {

		if ( NULL === $data ) {
			$data = $this->validate_wp_plugins_to_query();
		}

		$plugin_count = count( $data ) - 1;

		$output = '';

		$output .= '<ul>' . "\n";
		$output .= '<li><strong>' . esc_attr__( 'Plugins Total:' ) . ' '
		           . $plugin_count . ' ' . '</strong></li>' . "\n";
		$output .= '<li><strong>' . esc_attr__( 'Queries Total:' ) . ' ' . $data[ 'query_count' ] . '</strong></li>' . "\n";
		$output .= '</ul><hr />' . "\n";

		// remove counter, not necessary from here
		unset( $data[ 'query_count' ] );

		$output .= '<ol>' . "\n";

		$x = 1;
		foreach ( $data as $plugin_data ) {
			$output .= '<li><a href="#anker_' . $x . '">'
			           . $plugin_data[ 'name' ] . '</a></li>' . "\n";
			$x ++;
		}

		$output .= '</ol><hr />' . "\n";

		$x = 1;
		foreach ( $data as $plugin_data ) {

			$output .= '<h1 id="anker_' . $x . '">' . $x . '. ' . esc_attr__(
					'Plugin:'
				) . ' ' . $plugin_data[ 'name' ] . '</h1>' . "\n";

			foreach ( (array) $plugin_data[ 'backtrace' ] as $filename => $querys ) {

				$filename = htmlspecialchars( $filename );

				$output .= sprintf(
					'<p><code>%s</code></p>
						<table class="tablesorter">
							<thead>
								<tr>
									<th>%s</th>
									<th>%s</th>
								</tr>
							</thead>',
					htmlspecialchars( $filename ),
					__( 'Line' ),
					__( 'Query &amp; Function Chain' )
				);

				foreach ( (array) $querys as $query ) {

					// format the query
					$formatted_query = $query[ 'query' ];
					if ( class_exists( 'SqlFormatter' ) ) {
						$formatted_query = SqlFormatter::highlight( $formatted_query );
					}

					$query[ 'query' ] =
						number_format_i18n( sprintf( '%0.1f', $query[ 'time' ] * 1000 ), 1 ) . esc_attr__( 'ms' )
						. ' (' . $query[ 'time' ] . esc_attr__( 's)' )
						. '<br><code>' . $formatted_query . '</code>';
					// build function chain/backtrace
					$function_chain = implode( ' &#8594; ', $query[ 'function_chain' ] );

					$output .= '<tr>
								<td>' . $query[ 'line' ] . '</td>
								<td>' . $query[ 'query' ] . '</td>
							</tr>';

					if ( STACKTRACE ) {
						$output .=
							"<tr>
									<td></td>
									<td>$function_chain</td>
								</tr>";
					}

				}
				$output .= '</table>' . "\n";
			}
			$x ++;
		}

		echo $output;
	}

	/**
	 * Render tracer's data
	 *
	 * @param array $data
	 */
	public function render_wp_content_data( $data = NULL ) {

		if ( NULL === $data ) {
			$data = $this->validate_wp_content_to_query();
		}

		$output = '';

		$output .= '<ul>' . "\n";
		$output .= '<li><strong>' . esc_attr__( 'Queries Total:' ) . ' ' . $data[ 'query_count' ] . '</strong></li>' . "\n";
		$output .= '</ul><hr />' . "\n";

		if ( 0 === $data[ 'query_count' ] ) {
			$output .= esc_attr__( 'No queries from wp-content.' );
		}

		if ( 0 !== $data[ 'query_count' ] ) {

			// remove counter, not necessary from here
			unset( $data[ 'query_count' ] );

			$output .= '<ol>' . "\n";

			$x = 1;
			foreach ( $data as $content_data ) {
				$output .= '<li><a href="#content_anker_' . $x . '">'
				           . $content_data[ 'function' ] . '</a></li>' . "\n";
				$x ++;
			}

			$output .= '</ol><hr />' . "\n";

			$x = 1;
			foreach ( $data as $content_data ) {

				$output .= '<h1 id="content_anker_' . $x . '">' . $x . '. ' . esc_attr__(
						'Function:'
					) . ' ' . $content_data[ 'function' ] . '</h1>' . "\n";

				$filename = htmlspecialchars( $content_data[ 'file' ] );

				$output .= sprintf(
					'<p><code>%s</code></p>
						<table class="tablesorter">
							<thead>
								<tr>
									<th>%s</th>
									<th>%s</th>
								</tr>
							</thead>',
					htmlspecialchars( $filename ),
					__( 'Line' ),
					__( 'Query &amp; Function Chain' )
				);

				// format the query
				$formatted_query = $content_data[ 'query' ];
				if ( class_exists( 'SqlFormatter' ) ) {
					$formatted_query = SqlFormatter::highlight( $formatted_query );
				}

				$content_data[ 'query' ] =
					number_format_i18n( sprintf( '%0.1f', $content_data[ 'time' ] * 1000 ), 1 ) . esc_attr__( 'ms' )
					. ' (' . $content_data[ 'time' ] . esc_attr__( 's)' )
					. '<br><code>' . $formatted_query . '</code>';
				// build function chain/backtrace
				$function_chain = implode( ' &#8594; ', $content_data[ 'function_chain' ] );

				$output .= '<tr>
							<td>' . $content_data[ 'line' ] . '</td>
							<td>' . $content_data[ 'query' ] . '</td>
						</tr>';

				if ( STACKTRACE ) {
					$output .=
						"<tr>
								<td></td>
								<td>$function_chain</td>
							</tr>";
				}

				$output .= '</table>' . "\n";

				$x ++;
			}
		}

		echo $output;
	}

	/**
	 * Faux-private function for sorting data
	 *
	 * @param   array $a
	 * @param   array $b
	 *
	 * @return  integer
	 */
	public function sort_by_name( $a, $b ) {

		return strcmp( $a[ 'name' ], $b[ 'name' ] );
	}

	/**
	 * Get queries about the globals, incl. all queries
	 * Format the queries for readable output
	 *
	 * @param bool $echo Default is True, Use FALSE for return data
	 * @param int  $sorting
	 *
	 * @return Mixed , Use SORT_DESC, SORT_ASC for Sorting direction; Use FALSE for deactivate the sorting
	 * @internal param $String
	 */
	public function get_queries( $echo = TRUE, $sorting = SORT_DESC ) {

		global $wpdb, $EZSQL_ERROR;

		$wpdb->flush();

		// @see  http://www.mysqlfaqs.net/mysql-faqs/Speed-Up-Queries/What-is-query-cache-in-MySQL
		// Disable query cache for current client
		if ( QUERY_CACHE_TYPE_OFF ) {
			$wpdb->query( 'SET SESSION query_cache_type = OFF' );
		}

		// save all queries in var
		$this->_queries = (array) $wpdb->queries;

		$debug_queries    = '';
		$x                = 0;
		$total_time       = timer_stop( 0, 22 );
		$total_query_time = 0;
		$phpper           = '';
		$mysqlper         = '';

		if ( ! empty( $this->_queries ) ) {

			$php_time = $total_time - $total_query_time;
			// Create the percentages
			if ( 0 < $total_time ) {
				$mysqlper = number_format_i18n( $total_query_time / $total_time * 100, 2 );
				$phpper   = number_format_i18n( $php_time / $total_time * 100, 2 );
			}

			$debug_queries .= '<h3>' . esc_attr__( 'Queries' ) . '</h3>';

			// Database errors
			$debug_queries .= '<hr /><h3>' . esc_attr__( 'Database Errors' ) . '</h3>';
			if ( ! empty( $EZSQL_ERROR ) ) {

				$debug_queries .= '<ol class="important">';

				foreach ( (array) $EZSQL_ERROR as $e ) {
					$query = nl2br( esc_html( $e[ 'query' ] ) );
					$debug_queries .= "<li>$query<br/><div class='qdebug'>{$e['error_str']}</div></li>\n";
				}
				$debug_queries .= '</ol>';
			} else {
				$debug_queries .= '<ul><li>' . esc_attr__( 'No database errors.', 'debug_objects' ) . '</li></ul>';
			}

			// List time for generated the page.
			$debug_queries .= '<hr /><h3><strong>' . esc_attr__( 'Page generated in' ) . ' '
			                  . number_format_i18n( sprintf( '%0.1f', $total_time * 1000 ), 1 ) . esc_attr__( 'ms ( ' )
			                  . timer_stop() . esc_attr__( 's' ) . ' )</strong></h3>';

			$debug_queries .= '<hr /><ul>' . "\n";
			$debug_queries .= '<h3><strong>' . esc_attr__( 'Total:' ) . ' '
			                  . count( $this->_queries ) . ' ' . esc_attr__( 'queries' )
			                  . '</strong></h3>';

			// List caller to each query, filtered to the caller.
			$debug_queries .= esc_attr__( 'Queries by Caller' );
			$caller = array();
			foreach ( $this->_queries as $q ) {

				if ( isset( $q[ 2 ] ) && ! empty( $q[ 2 ] ) ) {

					$st                  = explode( ', ', $q[ 2 ] );
					$functions           = array_diff( $st, $this->replaced_functions );
					$caller[ 'calls' ][] = end( $functions );
				}
			}
			// Remove redundant values and count redundant call.
			$caller[ 'counter' ] = array_count_values( $caller[ 'calls' ] );

			$debug_queries .= '<table class="tablesorter"><thead><tr><th>'
			                  . esc_attr__( 'Count' ) . '</th><th>'
			                  . esc_attr__( 'Call' ) . '</th></tr></thead>';

			foreach ( $caller[ 'counter' ] as $call => $value ) {
				$debug_queries .= '<tr><td>' . $value . '</td><td><code>' . $call . '</code></td></tr>';
			}
			$debug_queries .= '</table>';

			$debug_queries .= '<hr /><ul>';
			if ( count( $this->_queries ) !== get_num_queries() ) {
				$debug_queries .= '<li><strong>' . esc_attr__( 'Total:' ) . ' '
				                  . get_num_queries() . ' '
				                  . esc_attr__( 'num_queries.' ) . '</strong></li>' . "\n";
				$debug_queries .= '<li>'
				                  . esc_attr__( '&raquo; Different values in num_query and query? - please set the constant' )
				                  . ' <code>define( \'SAVEQUERIES\', TRUE );</code>' . esc_attr__(
					                  'in your'
				                  ) . ' <code>wp-config.php</code></li>' . "\n";
			}

			$debug_queries .= '</ul>' . "\n";
			$debug_queries .= '<hr />' . "\n";

			/**
			 * Hook to filter the queries array
			 *
			 * @since  09/13/13
			 */
			$this->_queries = (array) apply_filters(
				'debug_objects_sort_queries', $this->_queries, $sorting
			);

			foreach ( $this->_queries as $key => $row ) {
				$queries[ $key ] = $row[ 0 ];
			}
			array_multisort( $queries, $sorting, $this->_queries );

			foreach ( $this->_queries as $q ) {

				$time    = $q[ 1 ];
				$time_ms = number_format( sprintf( '%0.1f', $time * 1000 ), 1, '.', ',' );

				$class = '';
				if ( '0.5' <= $time_ms ) {
					$class = ' high_query_time';
				} elseif ( '1.' <= $time_ms ) {
					$class = ' big_query_time';
				}

				$class = ' class="alternate' . $class . '"';
				if ( $x % 2 !== 0 ) {
					$class = ' class="default' . $class . '"';
				}

				$total_query_time += $time;
				$debug_queries .= '<li' . $class . '><ul>';
				$debug_queries .= '<li><strong>'
				                  . esc_attr__( 'Time:' ) . '</strong> '
				                  . $time_ms . esc_attr__( 'ms' )
				                  . ' (' . $time . esc_attr__( 's' ) . ')</li>';

				if ( isset( $time ) ) {

					//$s = nl2br( $q[ 0 ] );
					$s = trim( preg_replace( '/[[:space:]]+/', ' ', $q[ 0 ] ) );

					// format the query
					if ( class_exists( 'SqlFormatter' ) ) {
						$s = SqlFormatter::highlight( $s );
					}

					$debug_queries .= '<li><strong>'
					                  . esc_attr__( 'Query:' ) . '</strong> '
					                  . $s . '</li>';
				}

				if ( isset( $q[ 2 ] ) && ! empty( $q[ 2 ] ) ) {

					$st       = explode( ', ', $q[ 2 ] );
					$st_array = array_diff( $st, $this->replaced_functions );

					$markup_st = array();
					foreach ( $st_array as $s ) {
						$markup_st[] = '<code>' . esc_html( $s ) . '</code>';
					}

					if ( ! STACKTRACE ) {
						$debug_queries .= '<li><strong>Function:</strong> <code>'
						                  . end( $st_array ) . '()</code></li>';
					} else {
						$st = implode( ', ', $markup_st );
						$st = str_replace( $this->replaced_actions, 'do_action', $st );
						$debug_queries .= '<li><strong>'
						                  . '<a href="http://en.wikipedia.org/wiki/Stack_trace">Stack trace</a>:</strong> '
						                  . $st . '</li>';
					}
				}

				$debug_queries .= '</ul></li>' . "\n";
				$x ++;
			}

			$debug_queries .= '</ol>' . "\n\n";

		}

		$php_time = $total_time - $total_query_time;
		// Create the percentages
		if ( 0 < $total_time ) {
			$mysqlper = number_format_i18n( $total_query_time / $total_time * 100, 2 );
			$phpper   = number_format_i18n( $php_time / $total_time * 100, 2 );
		}

		$debug_queries .= '<ul>' . "\n";

		$debug_queries .= '<li><strong>' . esc_attr__( 'Total query time:' ) . ' '
		                  . number_format_i18n( sprintf( '%0.1f', $total_query_time * 1000 ), 1 )
		                  . esc_attr__( 'ms for' ) . ' ' . count( $this->_queries ) . ' ' . esc_attr__( 'queries (' )
		                  . number_format_i18n( $total_query_time, 15 ) . esc_attr__( 's) ' ) . '</strong></li>';

		if ( count( $this->_queries ) !== get_num_queries() ) {
			$debug_queries .= '<li><strong>' . esc_attr__( 'Total num_query time:' ) . ' '
			                  . timer_stop() . ' ' . esc_attr__( 'for' ) . ' ' . get_num_queries() . ' '
			                  . esc_attr__( 'num_queries.' ) . '</strong></li>' . "\n";
			$debug_queries .= '<li>'
			                  . esc_attr__( '&raquo; Different values in num_query and query? - please set the constant' )
			                  . ' <code>define( \'SAVEQUERIES\', TRUE );</code>' . esc_attr__(
				                  'in your'
			                  ) . ' <code>wp-config.php</code></li>' . "\n";
		}

		if ( $total_query_time === 0 ) {
			$debug_queries .= '<li>' . esc_attr__( '&raquo; Query time is null (0)? - please set the constant' )
			                  . ' <code>SAVEQUERIES</code>' . ' ' . esc_attr__( 'at' ) . ' <code>TRUE</code> ' . esc_attr__( 'in your' )
			                  . ' <code>wp-config.php</code></li>' . "\n";
		}

		if ( 0 < $total_time ) {
			$debug_queries .= '<li>' . esc_attr__( 'Page generated in' ) . ' '
			                  . number_format_i18n( sprintf( '%0.1f', $total_time * 1000 ), 1 ) . esc_attr__( 'ms; (' )
			                  . $total_time . esc_attr__( 's); ' )
			                  . $phpper . esc_attr__( '% PHP' ) . '; ' . $mysqlper
			                  . esc_attr__( '% MySQL' ) . '</li>' . "\n";
		}

		$debug_queries .= '</ul>' . "\n";

		if ( $echo ) {
			echo $debug_queries;
		}

		return $debug_queries;
	}

} // end class
