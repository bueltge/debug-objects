<?php
/**
 * Add helper for crons in WP
 *
 * Without i18n possibility for load faster; not important for debug plugin
 *
 * @package     Debug Objects
 * @subpackage  Helper for crons
 * @author      Frank Bültge
 * @since       2.1.5  11/04/2012
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Cron' ) ) {
	class Debug_Objects_Cron extends Debug_Objects {

		/**
		 * Holds all of the cron events.
		 *
		 * @var array
		 */
		private static $_crons;

		/**
		 * Holds only the cron events initiated by WP core.
		 *
		 * @var array
		 */
		private static $_core_crons;

		/**
		 * Holds the cron events created by plugins or themes.
		 *
		 * @var array
		 */
		private static $_user_crons;

		/**
		 * Total number of cron events
		 *
		 * @var int
		 */
		private static $_total_crons = 0;

		/**
		 * String for the transient key
		 *
		 * @var string
		 */
		public $transient_key = 'debug_objects_http_';

		/**
		 * Time window for save cron log
		 * in seconds
		 *
		 * @var int
		 */
		public $transient_time = 3600;

		protected static $classobj = NULL;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return \Debug_Objects_Cron|null $classobj
		 */
		public static function init() {

			NULL === self::$classobj and self::$classobj = new self();

			return self::$classobj;
		}

		/**
		 * Init the class and include this in the plugin
		 *
		 * @return \Debug_Objects_Cron
		 */
		public function __construct() {

			if ( ! current_user_can( '_debug_objects' ) ) {
				return;
			}

			add_action( 'http_api_debug', array( $this, 'log_cron_http_api_debug' ), 10, 3 );
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}

		/**
		 * Add this content and render tab
		 *
		 * @param   $tabs  Array
		 *
		 * @return Array $tabs  Array
		 */
		public function get_conditional_tab( $tabs ) {

			$tabs[ ] = array(
				'tab'      => __( 'Cron', parent:: get_plugin_data() ),
				'function' => array( $this, 'render_data' )
			);

			return $tabs;
		}

		/**
		 * Render data to view
		 *
		 * @param   $echo  boolean for display or return data
		 *
		 * @return  String
		 */
		public function render_data( $echo = TRUE ) {

			self::get_crons();

			$doing_cron = get_transient( 'doing_cron' ) ? 'Yes' : 'No';

			// Get the time of the next event
			$cron_times = array_keys( self::$_crons );

			$output = '';
			$output .= '<h4>Total Events</h4>';
			$output .= '<p>' . (int) self::$_total_crons . '</p>';

			$output .= '<h4>Doing Cron</h4>';
			$output .= '<p>' . $doing_cron . '</p>';

			$output .= '<h4>Next Events</h4>';
			$_next_events = array(
				$cron_times[ 0 ] => array(
					date( 'Y-m-d H:i:s' ) => array(
						'hook' => array(
							'schedule' => ' ',
							'args'     => '',
						)
					)
				)
			);
			// custom values for display functions
			$thead = array(
				'Next Execution',
				'Current Time',
				'',
				'',
				'',
				'',
			);

			$output .= self::display_events( $_next_events, $thead );

			$output .= '<h4>Core Events</h4>';
			if ( ! is_null( self::$_core_crons ) ) {
				$output .= self::display_events( self::$_core_crons );
			} else {
				$output .= '<p>No core events scheduled.</p>';
			}

			$output .= '<h4>Custom Events</h4>';
			if ( ! is_null( self::$_user_crons ) ) {
				$output .= self::display_events( self::$_user_crons );
			} else {
				$output .= '<p>No custom events scheduled.</p>';
			}

			$output .= '<h4>Schedules</h4>';
			$output .= self::get_schedules( FALSE );

			if ( $echo ) {
				echo $output;
			}

			return $output;
		}

		/**
		 * Display the event data
		 *
		 * @since  1.0.0  10/04/2012
		 *
		 * @param              $events  Array
		 *
		 * @param bool | array $thead
		 *
		 * @return String
		 */
		private function display_events( $events, $thead = FALSE ) {

			if ( ! $thead ) {
				$thead = array(
					'Next Execution',
					'Hook',
					'Hooked functions',
					'Interval Hook',
					'Interval Value',
					'Args',
				);
			}

			if ( is_null( $events ) || empty( $events ) ) {
				return NULL;
			}

			$class = ' class="alternate"';

			$output = '';
			$output .= '<table class="tablesorter">';
			$output .= '<thead><tr>';
			$output .= '<th>' . $thead[ 0 ] . '</th>';
			$output .= '<th>' . $thead[ 1 ] . '</th>';
			$output .= '<th>' . $thead[ 2 ] . '</th>';
			$output .= '<th>' . $thead[ 3 ] . '</th>';
			$output .= '<th>' . $thead[ 4 ] . '</th>';
			$output .= '<th>' . $thead[ 5 ] . '</th>';
			$output .= '</tr></thead>';

			foreach ( $events as $time => $time_cron_array ) {

				foreach ( $time_cron_array as $hook => $data ) {
					$output .= '<tr' . $class . '>';
					$output .= '<td valign="top">' . date(
							'Y-m-d H:i:s', $time
						) . '<br />' . $time . '<br />' . human_time_diff( $time ) . '</td>';
					$output .= '<td valign="top"><code>' . wp_strip_all_tags( $hook ) . '</code></td>';

					$functions = array();
					if ( date( 'Y-m-d H:i:s' ) != $hook && isset( $GLOBALS[ 'wp_filter' ][ $hook ] ) ) {

						foreach ( (array) $GLOBALS[ 'wp_filter' ][ $hook ] as $priority => $function ) {

							foreach ( $function as $hook_details ) {

								if ( is_object( $hook_details[ 'function' ][ 0 ] ) ) {

									$functions[ ] = get_class(
											$hook_details[ 'function' ][ 0 ]
										) . '::' . $hook_details[ 'function' ][ 1 ] . '()';
								} else {

									$functions[ ] = ( isset( $hook_details[ 'class' ] )
											? $hook_details[ 'class' ] . '::'
											: '' ) . $hook_details[ 'function' ] . '()';
								}

							}

						}

					} // end if

					$output .= '<td valign="top">';
					$output .= implode( ', ', $functions );
					$output .= '</td>';

					foreach ( $data as $hash => $info ) {
						// Report the schedule
						$output .= '<td valign="top">';
						if ( $info[ 'schedule' ] ) {
							$output .= wp_strip_all_tags( $info[ 'schedule' ] );
						} else {
							$output .= 'Single Event';
						}
						$output .= '</td>';

						// Report the interval
						$output .= '<td valign="top">';
						if ( isset( $info[ 'interval' ] ) ) {
							$output .= wp_strip_all_tags( $info[ 'interval' ] ) . ' second<br />';
							$output .= $info[ 'interval' ] / 60 . ' minute<br />';
							$output .= $info[ 'interval' ] / ( 60 * 60 ) . ' hour';
						} else {
							$output .= 'Single Event';
						}
						$output .= '</td>';

						// Report the args
						$output .= '<td valign="top">';
						if ( ! empty( $info[ 'args' ] ) ) {
							foreach ( $info[ 'args' ] as $key => $value ) {
								$output .= wp_strip_all_tags( $key ) . ' => ' . wp_strip_all_tags( $value ) . '<br />';
							}
						} else {
							$output .= 'No Args';
						}
						$output .= '</td>';
					}

					$output .= '</tr>';
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				}

			}

			$output .= '</table>';

			return $output;
		}

		/**
		 * Get crons, there registered in WP
		 *
		 * @since   2.1.5 11/04/2012
		 * @return  Array
		 */
		public function get_crons() {

			if ( ! is_null( self::$_crons ) ) {
				return self::$_crons;
			}

			if ( ! $crons = _get_cron_array() ) {
				return self::$_crons;
			}

			self::$_crons = $crons;

			// Lists all crons that are defined in WP Core
			$core_cron_hooks = array(
				'wp_scheduled_delete',
				'upgrader_scheduled_cleanup',
				'importer_scheduled_cleanup',
				'publish_future_post',
				'akismet_schedule_cron_recheck',
				'akismet_scheduled_delete',
				'do_pings',
				'wp_version_check',
				'wp_update_plugins',
				'wp_update_themes',
			);

			// Sort and count crons
			foreach ( self::$_crons as $time => $time_cron_array ) {
				foreach ( $time_cron_array as $hook => $data ) {
					self::$_total_crons ++;

					if ( in_array( $hook, $core_cron_hooks ) ) {
						self::$_core_crons[ $time ][ $hook ] = $data;
					} else {
						self::$_user_crons[ $time ][ $hook ] = $data;
					}
				}
			}

			return self::$_crons;
		}

		/**
		 * Displays all of the schedules defined
		 *
		 * @param   $echo  boolean  set to echo or return for content
		 *
		 * @return string $output  string
		 */
		private function get_schedules( $echo = TRUE ) {

			$output = '';
			$output .= '<table class="tablesorter">';
			$output .= '<thead><tr>';
			$output .= '<th> Interval Hook</th>';
			$output .= '<th> Interval (second)</th>';
			$output .= '<th> Interval (minute)</th>';
			$output .= '<th> Interval (hour)</th>';
			$output .= '<th> Display Name</th>';
			$output .= '</tr></thead>';

			$class = ' class="alternate"';

			foreach ( wp_get_schedules() as $interval_hook => $data ) {
				$output .= '<tr' . $class . '>';
				$output .= '<td valign="top"> ' . esc_html( $interval_hook ) . '</td>';
				$output .= '<td valign="top"> ' . wp_strip_all_tags( $data[ 'interval' ] ) . '</td>';
				$output .= '<td valign="top"> ' . wp_strip_all_tags( $data[ 'interval' ] ) / 60 . '</td>';
				$output .= '<td valign="top"> ' . wp_strip_all_tags( $data[ 'interval' ] ) / ( 60 * 60 ) . '</td>';
				$output .= '<td valign="top"> ' . esc_html( $data[ 'display' ] ) . '</td>';
				$output .= '</tr>';

				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
			}

			$output .= '</table>';

			if ( $echo ) {
				echo $output;
			}

			return $output;
		}

		/**
		 * Log cron from http api
		 *
		 * @param      $response
		 * @param      $type
		 * @param bool $transport
		 *
		 * @return null|void
		 */
		public function log_cron_http_api_debug( $response, $type, $transport = FALSE ) {

			if ( ! $transport || 'response' != $type ) {
				return;
			}

			if ( is_wp_error( $response ) ) {
				$response = $response->get_error_message();
			}

			if ( is_array( $response )
				&& is_array( $response[ 'response' ] )
				&& ! empty( $response[ 'response' ][ 'code' ] )
				&& '200' == $response[ 'response' ][ 'code' ]
			) {
				return NULL;
			}

			$meta_key = $this->transient_key . $transport . time();
			$response = stripslashes_deep( $response );
			set_transient( $meta_key, $response, $this->transient_time );
		}

	} // end class
}// end if class exists
