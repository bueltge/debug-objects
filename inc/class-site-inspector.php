<?php
/**
 * Site Inspector Class
 *
 * @author Benjamin J. Blater
 * @version 0.1
 * @pacakge siteinspector
 * @license GPL2
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

 /**
  * Hat tip to:
  * PHP CMS Detector http://www.phpclasses.org/package/6926-PHP-Detect-software-used-by-a-site-analsying-its-HTML.html and
  * Chrome Sniffer https://chrome.google.com/webstore/detail/homgcnaoacgigpkkljjjekpignblkeae
  * For providing the initial inspiration and approach
  */

/**
 * Main Site-Inspecting Class
 */
class SiteInspector {

	static $instance;

	public $cachelife = 3600;

	//defaults to look for; can be overriden by user
	//format [search] => [label]
	public $searches = array(

				'cloud' => array(
					'amazon'=>'Amazon',
					'rackspace' => 'Rackspace'
				),

				'cdn' => array(
					'Akamai' => 'akamai',
					'edgekey.net' => 'Akamai',
					'akam.net' => 'Akamai',
					'akadns.net' => 'Akamai',
				),

				'cms' => array(
					'joomla' => 'Joomla',
					'wordpress' => 'WordPress',
					'wp-content' => 'WordPress',
					'drupal' => 'Drupal',
					'sites\/default\/' => 'Drupal',
					'sites\/all\/' => 'Drupal',
					'xoops' => 'Xoops',
					'mediawiki' => 'MediaWiki',
					'php-nuke' => 'PHP-Nuke',
					'typepad' => 'Typepad',
					'Movable Type' => 'Moveable Type',
					'bbpress' => 'BBPress',
					'blogger' => 'Blogger',
					'sharepoint' => 'Sharepoint',
					'zencart' => 'Zencart',
					'phpbb' => 'PhpBB',
					'tumblr' => 'tumblr',
					'liferay' => 'Liferay',
					'percussion rhythmyx' => 'Percussion',
				),

				'analytics' => array(
					'google-analytics' => 'Google Analytics',
					'ga.js' => 'Google Analytics',
					'ua-[0-9]{8}-[0-9]' => 'Google Analytics',
					'_gaq' => 'Google Analytics',
					'quantcast' => 'Quantcast',
					'disqus' => 'Disqus',
					'GetSatisfaction' => 'GetSatisfaction',
					'AdSense' => 'AdSense',
					'AddThis' => 'AddThis',
				),

				'scripts' => array(
					'__proto__' => 'Prototype',
					'jquery' => 'jQuery',
					'mootools' => 'Mootools',
					'dojo\.' => 'Dojo',
					'scriptalicious' => 'Scriptaculous',
				),

				'gapps' => array (
					'ghs.google.com' => 'Google Docs',
					'aspmx.l.google.com' => 'GMail',
					'googlemail.com' => 'GMail'
				),
	);

	//user agent to identify as
	public $ua = 'Site Inspector';

	//whether to follow location headers
	public $follow = 5;

	public $data = null;

	/**
	 * Initiates the class
	 * @since 0.1
	 */
	function __construct() {
		self::$instance = $this;
	}

	/**
	 * Allows user to overload data array
	 * @since 0.1
	 * @param string $name data key
	 * @param mixed $value data value
	 */
	function __set( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	/**
	 * Returns property from data array
	 * @since 0.1
	 * @param string $name data key
	 * @returns mixed the value requested
	 */
	function __get( $name ) {

		if ( array_key_exists($name, $this->data) )
			return $this->data[ $name ];

		return 'undefined';

		$trace = debug_backtrace();
		trigger_error(
		    'Undefined property via __get(): ' . $name .
		    ' in ' . $trace[0]['file'] .
		    ' on line ' . $trace[0]['line'],
		    E_USER_NOTICE);

		return null;
	}

	/**
	 * Checks site for HTTPs support
	 * @param string $domain the domain
	 * @returns bool true if supports, otherwise false
	 */
	function check_https( $domain = '' ) {

		$domain = $this->get_domain( $domain );
		$domain = 'https://' . $this->remove_http( $domain );

		$args = array( 'user-agent' => $this->ua, 'sslverify' => false );
		$get = $this->maybe_remote_get( $domain, $args);

		if ( is_wp_error( $get ) )
			return false;

		return true;

	}

	/**
	 * Checks site for apps
	 * @param string $body the site's html
	 * @param array $apps array of apps to search for
	 * @param bool $script whether this is a JS file
	 * @returns array array of apps found
	 */
	function check_apps( $body, $apps, $script = false ) {

		$output = array();

		//this is a javascript file, just check the whole thing
		if ( $script )  {

		foreach ( $apps as $search=>$app ) {

			if ( preg_match_all( "/$search/i", $body, $matches) != 0 )
				$output[] = $app;
			}
			return $output;
		}


		//grab external scripts
		preg_match_all( '/<script[^>]* src=(\"|\')([^>]*)(\"|\')[^>]*>/i', $body, $matches);

		foreach ( $matches[2] as $url ) {

				//exclude addthis because it will trip every CMS search
				if ( stripos( $url, 'addthis.com' ) !== false )
					continue;

				$args = array( 'user-agent' => $this->ua );
				$data = wp_remote_retrieve_body( $this->maybe_remote_get( $this->url_to_absolute( $this->domain, $url ), $args) );
				if ( $data )
					$output = array_merge( $output, $this->check_apps( $data, $apps, true ) );
		}

		//loop and regex
		foreach ( $apps as $search=>$app ) {

			//look inside link and meta attributes to find app names
			if ( preg_match_all( '/<(link|meta)[^>]+' . $search . '[^>]+>/i', $body, $matches) != 0 )
				$output[] = $app;

			//Look inside script tags
			$found_tags = preg_match_all( "#<script[\s\S]*?>[\s\S]*?</script>#si", $body, $matches);
			if (  $found_tags ) {
				foreach( $matches[0] as $match) {
					if ( preg_match ( '/$search/ism', $body) )
						$output[] = $app;
				}
			}

		}

			//should fix this
			return array_unique( $output );


	}

	/**
	 * Checks a domain to see if there's a CNAME or A record on the non-www domain
	 *
	 * Updates $this->domain to www. if there's no non-www support
	 * @since 0.1
	 * @param string $domain the domain
	 * @return bool true if non-www works, otherwise false
	 */
	function check_nonwww( $domain  = '' ) {

		$domain = $this->get_domain( $domain );

		//grab the DNS
		$dns = $this->get_dns_record( $domain );

		if ( $dns ) {

			//check for for CNAME or A record on non-www
			foreach ( $dns as $d ) {

				foreach ( $d as $record ) {
					 if ( isset( $record['type'] ) && ( $record['type'] == 'A' || $record['type'] == 'CNAME' ) )
						 return true;
				}

			}

		}

		//if there's no non-www, subsequent actions should be taken on www. instead of the TLD.
		$this->domain = $this->maybe_add_www ( $domain );

		return false;

	}

	/**
	 * Loops through an array of needles to see if any are in the haystack
	 *
	 * @param array $haystack the haystack
	 * @param       $key
	 * @param       $needle
	 *
	 * @return bool|string needle if found, otherwise false
	 * @internal param array $needles array of needle strings
	 * @since    0.1
	 */
	function find_needles_in_haystack( $haystack, $key, $needle ) {

		$needles = $this->searches[$needle];

		foreach ( $needles as $n => $label ) {

			if ( stripos( $haystack, $n ) !== FALSE ) {

				$this->data[$needle] = $label;
				return;
			}
		}

		return false;

	}


	/**
	 * Checks for an AAAA record on a domain
	 * @since 0.1
	 * @param array $dns the DNS Records
	 * @returns bool true if ipv6, otherwise false
	 */
	function check_ipv6 ( $dns = '' ) {

		if ( $dns == '' )
			$dns = $this->get_dns_record();

		if ( !$dns )
			return false;

		foreach ( $dns as $domain ) {

			foreach ($domain as $record) {
				if ( isset($record['type']) && $record['type'] == 'AAAA') {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * Helper function to allow domain arguments to be optional
	 *
	 * If domain is passed as an arg, will return that, otherwise will check $this->domain for the domain
	 * @since 0.1
	 * @param string $domain the domain
	 * @returns string the true domain
	 */
	function get_domain( $domain ) {

		if ( $domain != '' )
			return $domain;

		if ( $this->domain == '' )
			die('No Domain Supplied.');

		return $this->domain;

	}

	/**
	 * Retrieves DNS record and caches to $this->data
	 * @param string $domain the domain
	 * @returns array dns data
	 * @since 0.1
	 */
	function get_dns_record( $domain  = '' ) {

		$domain =  $this->remove_http( $this->get_domain( $domain ) );

		$domain = $this->remove_path( $domain );

		if ( !isset( $this->data['dns'][ $domain ] ) )
			@ $this->data['dns'][ $domain ] = dns_get_record( $domain, DNS_ALL - DNS_PTR );

		return $this->dns[ $domain ];

	}

	/**
	 * Main function of the class; propegates data array
	 * @since 0.1
	 * @param string $domain domain to inspect
	 * @returns array data array
	 */
	function inspect ( $domain = '' ) {

		//cleanup public vars
		$this->body = '';
		$this->headers = '';
		$this->data = array();

		//set the public if an arg is passed
		if ( $domain != '' )
			$this->domain = $domain;

		//if we don't have a domain, kick
		if ( $this->domain == '')
			return false;

		//cleanup domain
		$this->domain = strtolower( $this->domain );
		$this->domain = trim( $this->domain );
		$this->maybe_add_http( );
		$this->remove_www( );

		//check nonwww
		$this->nonwww = $this->check_nonwww( );
		$this->https = $this->check_https( );

		//get DNS
		$this->get_dns_record( $this->domain );

		//IP & Host
		$this->ip = gethostbyname( $this->remove_http( $this->domain ) );

		$live = false;

		if ( $ips = gethostbynamel( $this->remove_http( $this->domain ) ) ) {

			foreach ( $ips as $ip ) {

				//some sites (e.g., privacy.gov) returns localhost as their IP, this prevents scanning self
				if ( $ip != '127.0.0.1' )
					$live = true;

				$this->data['hosts'][$ip] = gethostbyaddr( $ip );

			}

		}

		//grab the page
		if ( $live )
			$data = $this->remote_get( $this->domain );

		//if there was an error, kick
		if ( !$live || !$data ) {
			$this->status = 'unreachable';
			return $this->data;
		} else if ( wp_remote_retrieve_response_code( $data ) > 400 ) {
			$this->status = wp_remote_retrieve_response_code( $data );
		} else {
			$this->status = 'live';
		}

		$this->body = $data['body'];
		$this->md5 = md5( $this->body );
		$this->headers = $data['headers'];

		if ( isset( $data['headers']['server'] ) ) {
			$this->server_software = $data['headers']['server'];
		}

		//merge DNS and hosts from reverse DNS lookup
		$haystack = array_merge( $this->dns, $this->hosts );

		//IPv6
		$this->ipv6 = $this->check_ipv6( $this->dns );

		//check CDN
		array_walk_recursive( $haystack, array( &$this, 'find_needles_in_haystack'), 'cdn');

		//check cloud
		array_walk_recursive( $haystack, array( &$this, 'find_needles_in_haystack'), 'cloud');

		//check google apps 
		array_walk_recursive( $haystack, array( &$this, 'find_needles_in_haystack'), 'gapps');

		$this->cms = $this->check_apps( $this->body, $this->searches['cms'] );
		$this->analytics = $this->check_apps( $this->body, $this->searches['analytics'] );
		$this->scripts = $this->check_apps( $this->body, $this->searches['scripts'] );

		asort( $this->data );

		return $this->data;
	}

	/**
	 * Smart remote get function
	 *
	 * Prefers wp_remote_get, but falls back to file_get_contents
	 * @param $domain string site to retrieve
	 * @returns array assoc. array of page data
	 * @since 0.1
	 */
	function remote_get( $domain = '' ) {

		$domain = $this->get_domain( $domain );

		$this->get_dns_record( $this->remove_trailing_slash( $domain ) );

		$args = array( 'redirection' => 0, 'user-agent' => $this->ua );

		$data = $this->maybe_remote_get( $domain, $args );

		//if there was an error, try to grab the headers to potentially follow a location header
		if ( is_wp_error( $data ) ) {

			if ( $data->get_error_message() == 'connect() timed out!' )
				return false;

			//use custom get_headers function (rather than WP's because WP doesn't provide headers if there's a redirect and PHP's doesn't allow for timeouts
			$data = array( 'headers' => $this->get_headers( $domain ) );

			if ( !$data || !$data['headers'] )
				return false;

		}

		$data = $this->maybe_follow_location_header ( $data );

		return $data;
	}

	/**
	 * Gets headers returned, used because other transports fail if > 200, redirect, etc.
	 * @param string $domain the domain
	 * @returns array assoc. array of headers
	 */
	function get_headers( $domain ) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $domain );
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		$data = curl_exec($ch);
		curl_close ($ch);

		if ( !$data )
			return false;

		$data = explode("\n", $data );

		foreach ( $data as $line => $content ) {
			$line = explode( ":", $content, 2 );
			isset( $line[1] ) ? $headers[ trim( strtolower( $line[0] ) ) ] = trim( $line[1] ) : $headers[] = trim( $content );
		}

		return array_filter( $headers );


	}

	/**
	 * Checks cashe and then gets URL
	 * @param string $url the URL to get
	 * @param array $args args to pass to HTPP API
	 */
	function maybe_remote_get( $url, $args ) {

		if ( !($data = get_transient( $url ) ) ) {
			$data = wp_remote_get( $url , $args);
			set_transient( $url, $data, $this->cachelife );
		}

		return $data;

	}

	/**
	 * Get's and follows location header if within redirect limit
	 * @param array $data the data returned from get
	 * @returns array $data the data of the actual site
	 */
	function maybe_follow_location_header ( $data ) {

		//check flag
		if ( !$this->follow )
			return $data;

		//if there's a location header, follow
		if ( !isset ( $data['headers']['location'] ) )
			return $data;

		//store the redirect 
		$this->data['redirect'][] = array( 'code' => substr( $data['headers'][0], 9, 3 ), 'destination' => $data['headers']['location'] );


var_dump( sizeof( $this->data['redirect'] ) ); flush();


		if ( sizeof( $this->data['redirect'] ) < $this->follow )
			$data = $this->remote_get( $data['headers']['location'] );

		return $data;
	}

	/**
	 * Conditionally prepends http:// to a string
	 * @since 0.1
	 * @param string $input domain to modify
	 * @returns string modified domain
	 */
	function maybe_add_http( $input = '' ) {

		$domain = $this->get_domain( $input );

		$domain = ( substr( $domain, 0, 7) == 'http://' ) ? $domain : 'http://' . $domain;


		//if no domain was passed, asume we should update the class
		if ( $input == '' )
			$this->domain = $domain;

		return $domain;

	}

	/**
	 * Strips HTTP:// from URLs
	 * @param string $input the url
	 * @returns string URL without HTTP
	 */
	function remove_http ( $input ) {

		$domain = $this->get_domain( $input );

		//kill the http
		$domain = str_ireplace('http://', '', $domain);

		//if no domain arg was passed, update the class
		if ( $input == '' )
			$this->domain = $domain;

		return $domain;
	}

	/**
	 * Removes www from domains
	 * @since 0.1
	 * @param string $input domain
	 * @returns string domain with www removed
	 */
	function remove_www( $input = '' ) {

		$domain = $this->get_domain( $input );

		//force http so check will work
		$domain = $this->maybe_add_http( $domain );

		//kill the www
		$domain = str_ireplace('http://www.', 'http://', $domain);

		//if no domain arg was passed, update the class
		if ( $input == '' )
			$this->domain = $domain;

		return $domain;

	}

	/**
	 * Strips file path from URL, if any
	 * @param string $input the URL to sanitize
	 * @return string the host of the URL
	 */
	function remove_path( $input = '' ) {

		$input = $this->get_domain( $input );
		$input = $this->maybe_add_http( $input );

		return parse_url( $input, PHP_URL_HOST );

	}

	/**
	 * Conditionally adds www to a domain
	 * @since 0.1
	 * @param string $input the domain
	 * @returns string the domain with www.
	 */
	function maybe_add_www ( $input = '' ) {

		$domain = $this->get_domain( $input );

		//force http so check will work
		$domain = $this->maybe_add_http( $domain );

		//check if it's already there
		if ( strpos( $domain, 'http://www.' ) !== FALSE )
			return $domain;

		//add the www
		$domain = str_ireplace('http://', 'http://www.', $domain);

		//if no domain arg was passed, update the class
		if ( $input == '' )
			$this->domain = $domain;

		return $domain;
	}

	function remove_trailing_slash( $domain ) {

		if ( substr( $domain, -1, 1) == '/' )
			return substr( $domain, 0, -1);

		return $domain;

	}
/**
 * Converts a relative URL (/bar) to an absolute URL (http://www.foo.com/bar)
 *
 * Inspired from code available at http://nadeausoftware.com/node/79,
 * Code distributed under OSI BSD (http://www.opensource.org/licenses/bsd-license.php)
 *
 * @params string $baseUrl Directory of linking page
 * @params string $relativeURL URL to convert to absolute
 * @return string Absolute URL
 */
	function url_to_absolute( $baseUrl, $relativeUrl ) {
		// If relative URL has a scheme, clean path and return.
		$r = $this->split_url( $relativeUrl );
		if ( $r === FALSE )
		    return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
		    if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
		        $r['path'] = $this->url_remove_dot_segments( $r['path'] );
		    return $this->join_url( $r );
		}

		// Make sure the base URL is absolute.
		$b = $this->split_url( $baseUrl );
		if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
		    return FALSE;
		$r['scheme'] = $b['scheme'];

		// If relative URL has an authority, clean path and return.
		if ( isset( $r['host'] ) )
		{
		    if ( !empty( $r['path'] ) )
		        $r['path'] = $this->url_remove_dot_segments( $r['path'] );
		    return $this->join_url( $r );
		}
		unset( $r['port'] );
		unset( $r['user'] );
		unset( $r['pass'] );

		// Copy base authority.
		$r['host'] = $b['host'];
		if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
		if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
		if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];

		// If relative URL has no path, use base path
		if ( empty( $r['path'] ) )
		{
		    if ( !empty( $b['path'] ) )
		        $r['path'] = $b['path'];
		    if ( !isset( $r['query'] ) && isset( $b['query'] ) )
		        $r['query'] = $b['query'];
		    return $this->join_url( $r );
		}

		// If relative URL path doesn't start with /, merge with base path
		if ( $r['path'][0] != '/' )
		{
		    $base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
		    if ( $base === FALSE ) $base = '';
		    $r['path'] = $base . '/' . $r['path'];
		}
		$r['path'] = $this->url_remove_dot_segments( $r['path'] );
		return $this->join_url( $r );
	}

	/**
	 * Required function of URL to absolute
	 *
	 * Inspired from code available at http://nadeausoftware.com/node/79,
	 * Code distributed under OSI BSD (http://www.opensource.org/licenses/bsd-license.php)
	 *
	 */
	function url_remove_dot_segments( $path ) {

		// multi-byte character explode
		$inSegs  = preg_split( '!/!u', $path );
		$outSegs = array( );
		foreach ( $inSegs as $seg )
		{
		    if ( $seg == '' || $seg == '.')
		        continue;
		    if ( $seg == '..' )
		        array_pop( $outSegs );
		    else
		        array_push( $outSegs, $seg );
		}
		$outPath = implode( '/', $outSegs );
		if ( $path[0] == '/' )
		    $outPath = '/' . $outPath;
		// compare last multi-byte character against '/'
		if ( $outPath != '/' &&
		    (mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
		    $outPath .= '/';
		return $outPath;
	}

	/**
	 * Required function of URL to absolute
	 *
	 * Inspired from code available at http://nadeausoftware.com/node/79,
	 * Code distributed under OSI BSD (http://www.opensource.org/licenses/bsd-license.php)
	 *
	 */
	function split_url( $url, $decode=TRUE ) {

		$xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
		$xpchar        = $xunressub . ':@%';

		$xscheme       = '([a-zA-Z][a-zA-Z\d+-.]*)';

		$xuserinfo     = '((['  . $xunressub . '%]*)' .
		                 '(:([' . $xunressub . ':%]*))?)';

		$xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

		$xipv6         = '(\[([a-fA-F\d.:]+)\])';

		$xhost_name    = '([a-zA-Z\d-.%]+)';

		$xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
		$xport         = '(\d*)';
		$xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
		                 '?(:' . $xport . ')?)';

		$xslash_seg    = '(/[' . $xpchar . ']*)';
		$xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
		$xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
		$xpath_abs     = '(/(' . $xpath_rel . ')?)';
		$xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
		                 '|' . $xpath_rel . ')';

		$xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

		$xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
		                 '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';


		// Split the URL into components.
		if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
		    return FALSE;

		if ( !empty($m[2]) )        $parts['scheme']  = strtolower($m[2]);

		if ( !empty($m[7]) ) {
		    if ( isset( $m[9] ) )   $parts['user']    = $m[9];
		    else            $parts['user']    = '';
		}
		if ( !empty($m[10]) )       $parts['pass']    = $m[11];

		if ( !empty($m[13]) )       $h=$parts['host'] = $m[13];
		else if ( !empty($m[14]) )  $parts['host']    = $m[14];
		else if ( !empty($m[16]) )  $parts['host']    = $m[16];
		else if ( !empty( $m[5] ) ) $parts['host']    = '';
		if ( !empty($m[17]) )       $parts['port']    = $m[18];

		if ( !empty($m[19]) )       $parts['path']    = $m[19];
		else if ( !empty($m[21]) )  $parts['path']    = $m[21];
		else if ( !empty($m[25]) )  $parts['path']    = $m[25];

		if ( !empty($m[27]) )       $parts['query']   = $m[28];
		if ( !empty($m[29]) )       $parts['fragment']= $m[30];

		if ( !$decode )
		    return $parts;
		if ( !empty($parts['user']) )
		    $parts['user']     = rawurldecode( $parts['user'] );
		if ( !empty($parts['pass']) )
		    $parts['pass']     = rawurldecode( $parts['pass'] );
		if ( !empty($parts['path']) )
		    $parts['path']     = rawurldecode( $parts['path'] );
		if ( isset($h) )
		    $parts['host']     = rawurldecode( $parts['host'] );
		if ( !empty($parts['query']) )
		    $parts['query']    = rawurldecode( $parts['query'] );
		if ( !empty($parts['fragment']) )
		    $parts['fragment'] = rawurldecode( $parts['fragment'] );
		return $parts;
	}

	/**
	 * Required function of URL to absolute
	 *
	 * Inspired from code available at http://nadeausoftware.com/node/79,
	 * Code distributed under OSI BSD (http://www.opensource.org/licenses/bsd-license.php)
	 *
	 */
	function join_url( $parts, $encode = TRUE ) {

	    if ( $encode )
	    {
	        if ( isset( $parts['user'] ) )
	            $parts['user']     = rawurlencode( $parts['user'] );
	        if ( isset( $parts['pass'] ) )
	            $parts['pass']     = rawurlencode( $parts['pass'] );
	        if ( isset( $parts['host'] ) &&
	            !preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
	            $parts['host']     = rawurlencode( $parts['host'] );
	        if ( !empty( $parts['path'] ) )
	            $parts['path']     = preg_replace( '!%2F!ui', '/',
	                rawurlencode( $parts['path'] ) );
	        if ( isset( $parts['query'] ) )
	            $parts['query']    = rawurlencode( $parts['query'] );
	        if ( isset( $parts['fragment'] ) )
	            $parts['fragment'] = rawurlencode( $parts['fragment'] );
	    }

	    $url = '';
	    if ( !empty( $parts['scheme'] ) )
	        $url .= $parts['scheme'] . ':';
	    if ( isset( $parts['host'] ) )
	    {
	        $url .= '//';
	        if ( isset( $parts['user'] ) )
	        {
	            $url .= $parts['user'];
	            if ( isset( $parts['pass'] ) )
	                $url .= ':' . $parts['pass'];
	            $url .= '@';
	        }
	        if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
	            $url .= '[' . $parts['host'] . ']'; // IPv6
	        else
	            $url .= $parts['host'];             // IPv4 or name
	        if ( isset( $parts['port'] ) )
	            $url .= ':' . $parts['port'];
	        if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
	            $url .= '/';
	    }
	    if ( !empty( $parts['path'] ) )
	        $url .= $parts['path'];
	    if ( isset( $parts['query'] ) )
	        $url .= '?' . $parts['query'];
	    if ( isset( $parts['fragment'] ) )
	        $url .= '#' . $parts['fragment'];
	    return $url;
	}

} // end class