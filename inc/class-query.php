<?php
/**
 * Add small screen with informations about queries of WP
 *
 * @package     Debug Queries
 * @subpackage  Cache
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Query' ) ) {
	
	// disable mySQL Session Cache
	if ( ! defined( 'QUERY_CACHE_TYPE_OFF' ) )
		define( 'QUERY_CACHE_TYPE_OFF', TRUE );
	
	if ( ! defined( 'SAVEQUERIES' ) )
		define( 'SAVEQUERIES', TRUE );
	
	if ( ! defined( 'STACKTRACE' ) )
		define( 'STACKTRACE', FALSE );
	
	//add_action( 'admin_init', array( 'Debug_Objects_Query', 'init' ) );
	
	class Debug_Objects_Query extends Debug_Objects {
		
		static private $replaced_functions = array( 'require_once', 'require', 'include', 'include_once' );
		
		static private $replaced_actions   = array( 'do_action, call_user_func_array' );
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Queries', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_queries' )
			);
			
			return $tabs;
		}
		
		public static function get_queries( $echo = TRUE ) {
			global $wpdb, $EZSQL_ERROR;
			
			$wpdb->flush();
			
			// @see  http://www.mysqlfaqs.net/mysql-faqs/Speed-Up-Queries/What-is-query-cache-in-MySQL
			// Disable query cache for current client
			if ( QUERY_CACHE_TYPE_OFF )
				mysql_query( "SET SESSION query_cache_type = OFF" );
				// return php warnings on the default Wpd-query
				//$wpdb->query( "SET SESSION query_cache_type = OFF;" );
			
			$debug_queries = '';
			$total_query_time = 0;
			$x = 0;
			$total_time = timer_stop( 0, 22 );
			$total_query_time = 0;
			$class = ''; 
			
			if ( ! empty( $wpdb->queries ) ) {
				$debug_queries .= '<ol>' . "\n";
				
				foreach ( $wpdb->queries as $q ) {
					
					$time = $q[1];
					$time_ms = number_format( sprintf('%0.1f', $q[1] * 1000), 1, '.', ',' );
					if ( '0.5' <= $time_ms )
						$class = ' high_query_time';
					elseif ( '1.' <= $time_ms )
						$class = ' big_query_time';
					else 
						$class = '';
					
					if ( $x % 2 != 0 )
						$class = ' class="default' . $class . '"';
					else
						$class = ' class="alternate' . $class . '"';
					
					$total_query_time += $q[1];
					$debug_queries .= '<li' . $class . '><ul>';
					$debug_queries .= '<li class="none_list"><strong>' . __( 'Time:', parent :: get_plugin_data() ) . '</strong> ' 
						. $time_ms . __( 'ms', parent :: get_plugin_data() ) 
						. ' (' . $time . __( 's', parent :: get_plugin_data() ) . ')</li>';
					if ( isset($q[1]) && ! empty($q[1]) ) {
						$s = nl2br( esc_html( $q[0] ) );
						$s = trim( preg_replace( '/[[:space:]]+/', ' ', $s) );
						$debug_queries .= '<li class="none_list"><strong>' . __( 'Query:', parent :: get_plugin_data() ) . '</strong> <code>' . $s . '</code></li>';
					}
					if ( isset($q[2]) && ! empty( $q[2] ) ) {
						$st = explode( ', ', $q[2] );
						$st_array = array_diff( $st, self :: $replaced_functions );
						foreach ( $st_array as $s ) {
							$markup_st[] = '<code>' . esc_html( $s ) . '</code>';
						}
						$st = implode( ', ', $markup_st );
						$st = str_replace( self :: $replaced_actions, array( 'do_action' ), $st );
						if ( ! STACKTRACE )
							$debug_queries .= '<li class="none_list"><strong>Function:</strong> <code>' . end( $st_array ) . '()</code></li>';
						if ( STACKTRACE )
							$debug_queries .= '<li class="none_list"><strong>' 
								. '<a href="http://en.wikipedia.org/wiki/Stack_trace">Stack trace</a>:</strong> ' 
								. $st . '</li>';
					}
					
					$debug_queries .= '</ul></li>' . "\n";
					$x++;
				}
				
				$debug_queries .= '</ol>' . "\n\n";
			
			}
			
			if ( ! empty($EZSQL_ERROR) ) {
				$debug_queries .= '<h3>' . __( 'Database Errors', parent :: get_plugin_data() ) . '</h3>';
				$debug_queries .= '<ol>';
	
				foreach ( $EZSQL_ERROR as $e ) {
					$query = nl2br(esc_html($e['query']));
					$debug_queries .= "<li>$query<br/><div class='qdebug'>{$e['error_str']}</div></li>\n";
				}
				$debug_queries .= '</ol>';
			}
			
			$php_time = $total_time - $total_query_time;
			// Create the percentages
			if ( 0 < $total_time ) {
				$mysqlper = number_format_i18n( $total_query_time / $total_time * 100, 2 );
				$phpper   = number_format_i18n( $php_time / $total_time * 100, 2 );
			}
			
			$debug_queries .= '<ul>' . "\n";
			$debug_queries .= '<li><strong>' . __( 'Total query time:', parent :: get_plugin_data() ) . ' ' 
				. number_format_i18n( sprintf('%0.1f', $total_query_time * 1000), 1 ) 
				. __( 'ms for' ) . ' ' . count($wpdb->queries) . ' ' . __( 'queries (', parent :: get_plugin_data() ) 
				. number_format_i18n( $total_query_time, 15 ) . __( 's) ', parent :: get_plugin_data() ). '</strong></li>';
			if ( count($wpdb->queries) != get_num_queries() ) {
				$debug_queries .= '<li><strong>' . __( 'Total num_query time:', parent :: get_plugin_data() ) . ' ' 
					. timer_stop() . ' ' . __( 'for' ) . ' ' . get_num_queries() . ' ' 
					. __( 'num_queries.', parent :: get_plugin_data() ) . '</strong></li>' . "\n";
				$debug_queries .= '<li class="none_list">' 
					. __( '&raquo; Different values in num_query and query? - please set the constant', parent :: get_plugin_data() ) 
					. ' <code>define(\'SAVEQUERIES\', true);</code>' . __( 'in your' ) . ' <code>wp-config.php</code></li>' . "\n";
			}
			if ( $total_query_time == 0 )
				$debug_queries .= '<li class="none_list">' . __( '&raquo; Query time is null (0)? - please set the constant', parent :: get_plugin_data() ) 
					. ' <code>SAVEQUERIES</code>' . ' ' . __( 'at' ) . ' <code>TRUE</code> ' . __( 'in your', parent :: get_plugin_data() ) 
					. ' <code>wp-config.php</code></li>' . "\n";
			if ( 0 < $total_time )
				$debug_queries .= '<li>' . __( 'Page generated in', parent :: get_plugin_data() ). ' ' 
					. number_format_i18n( sprintf('%0.1f', $total_time * 1000), 1 ) . __( 'ms; (', parent :: get_plugin_data() ) 
					. $total_time . __( 's); ', parent :: get_plugin_data() )
					. $phpper . __( '% PHP', parent :: get_plugin_data() ) . '; ' . $mysqlper 
					. __( '% MySQL', parent :: get_plugin_data() ) . '</li>' . "\n";
			$debug_queries .= '</ul>' . "\n";
			
			if ( $echo )
				echo $debug_queries;
			else
				return $debug_queries;
		}
		
	} // end class
}// end if class exists
