<?php
/**
 * Add information about the domain.
 *
 * @package     Debug Objects
 * @subpackage  Site Inspector
 * @author      Frank Bültge
 * @since       2012-07-29
 * @version     2016-03-31
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Inspector' ) ) {
	class Debug_Objects_Inspector extends Debug_Objects {

		protected static $classobj;

		/**
		 * Handler for the action 'init'. Instantiates this class.
		 *
		 * @access  public
		 * @return \Debug_Objects_Inspector|null $classobj
		 */
		public static function init() {

			NULL === self::$classobj and self::$classobj = new self();

			return self::$classobj;
		}

		public function __construct() {

			if ( ! current_user_can( '_debug_objects' ) ) {
				return;
			}

			require_once __DIR__ . '/class-site-inspector.php';
			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}

		public function get_conditional_tab( $tabs ) {

			$tabs[] = array(
				'tab'      => __( 'Inspector', parent:: get_plugin_data() ),
				'function' => array( $this, 'get_site_inspector_data' ),
			);

			return $tabs;
		}

		public function get_domain( $url ) {

			$nowww  = str_replace( 'www.', '', $url );
			$domain = parse_url( $nowww );

			if ( ! empty( $domain[ "host" ] ) ) {
				return $domain[ "host" ];
			} else {
				return $domain[ "path" ];
			}
		}

		/**
		 * Print all values. data.
		 */
		public function get_site_inspector_data() {

			$inspector = new SiteInspector;
			$data      = $inspector->inspect( $this->get_domain( $_SERVER[ 'HTTP_HOST' ] ) );
			?>
			<h4>Check Host: <?php echo $_SERVER[ 'HTTP_HOST' ]; ?></h4>
			<ul>
				<li>Status: <?php echo $inspector->status; ?></li>
				<li>IPv6 Support: <?php echo ( $inspector->ipv6 ) ? 'Yes' : 'No'; ?></li>
				<li>Non-WWW Support: <?php echo ( $inspector->nonwww ) ? 'Yes' : 'No'; ?></li>
				<li>CDN: <?php echo $inspector->cdn; ?></li>
				<li>Cloud: <?php echo $inspector->cloud; ?></li>
				<li>Https: <?php echo ( $inspector->https ) ? 'Yes' : 'No'; ?></li>
				<li>Non www: <?php echo ( $inspector->nonwww ) ? 'Yes' : 'No'; ?></li>
			</ul>

			<h4>Software</h4>
			<ul>
				<li>Google Apps: <?php echo $inspector->gapps; ?></li>
				<li>Server Software: <?php if ( isset( $data[ 'server_software' ] ) ) {
						echo $data[ 'server_software' ];
					} else {
						echo 'undefined';
					} ?></li>
				<li>Analytics: <?php if ( isset( $inspector->analytics ) ) {
						echo implode( ', ', $inspector->analytics );
					} else {
						echo 'undefined';
					} ?></li>
				<li>JavaScript Libraries: <?php if ( isset( $inspector->scripts ) ) {
						echo implode( ', ', $inspector->scripts );
					} else {
						echo 'undefined';
					} ?></li>
			</ul>

			<?php if ( isset( $inspector->headers ) ) { ?>
				<h4>Headers</h4>
				<ul>
					<?php foreach ( $inspector->headers as $k => $v ) { ?>
						<li><?php echo $k; ?>: <?php if ( is_array( $v ) ) {
								print_r( $v );
							} else {
								echo $v;
							} ?></li>
					<?php } ?>
				</ul>
			<?php }

			if ( isset( $data[ 'redirect' ] ) ) { ?>
				<h4>Redirects</h4>
				<ul>
					<?php foreach ( $data[ 'redirect' ] as $r ) { ?>
						<li><?php echo $r[ 'code' ]; ?>: <?php echo $r[ 'destination' ]; ?></li>
					<?php } ?>
				</ul>
			<?php } ?>

			<h4>DNS Record</h4>
			<?php foreach ( $inspector->dns as $domain => $records ) { ?>
				<strong><?php echo $domain; ?></strong>
				<?php $this->format_records( $records ); ?>
			<?php } ?>

			<h4>Reverse Lookup</h4>
			<?php $this->print_reverse_loockup( $inspector->hosts );
		}

		public function format_records( $records ) {

			if ( ! is_array( $records ) || empty( $records ) ) {
				esc_attr_e( 'No data available.' );

				return;
			}

			?>
			<table>
				<tr>
					<th>Host</th>
					<th>Class</th>
					<th>Type</th>
					<th>TTL</th>
					<th>Additional Info</th>
				</tr>
				<?php
				foreach ( $records as $record ) { ?>
					<tr>
						<td><?php echo $record[ 'host' ]; ?></td>
						<td><?php echo $record[ 'class' ]; ?></td>
						<td><?php echo $record[ 'type' ]; ?></td>
						<td><?php echo $record[ 'ttl' ]; ?></td>
						<td>
							<?php
							unset( $record[ 'host' ], $record[ 'class' ], $record[ 'type' ], $record[ 'ttl' ] );
							foreach ( $record as $field => $value ) {
								echo "$field: $value<br />";
							}
							?>
						</td>
					</tr>
				<?php } ?>
			</table>
			<?php
		}

		public function print_reverse_loockup( $data ) {

			if ( ! is_array( $data ) || empty( $data ) ) {
				esc_attr_e( 'No data available.' );

				return;
			}
			?>
			<h4>Reverse Lookup</h4>
			<table>
				<tr>
					<th>IP</th>
					<th>Hostname</th>
				</tr>
				<?php
				foreach ( $data as $ip => $host ) {
					if ( ! isset( $ip ) ) {
						continue;
					}
					?>
					<tr>
						<td>
							<a href="http://www.bing.com/search?q=ip%3A<?php echo trim( $ip ); ?>"><?php echo $ip; ?></a>
						</td>
						<td><?php echo $host; ?></td>
					</tr>
				<?php } ?>
			</table>
			<?php
		}

	} // end class

}// end if class exists
