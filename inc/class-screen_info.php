<?php
 /**
 * Get information in backend about screen information and different globals
 * 
 * @package     Debug Objects
 * @subpackage  Screen Info
 * @author      Frank Bültge
 * @since       09/24/2013
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Screen_Info' ) )
	return NULL;

class Debug_Objects_Screen_Info {
	
	/**
	 * The class object
	 * 
	 * @since  09/24/2013
	 * @var    String
	 */
	static protected $class_object = NULL;
	
	public $wp_globals = array(
		'table_prefix',
		'prefix',
		'base_prefix',
		'wp_version',
		'wp_db_version',
		'tinymce_version',
		'required_php_version',
		'required_mysql_version',
		'blog_id',
		'blogid',
		'siteid',
		'tables',
		'charset',
		'collate',
		'domain',
		'cookie_domain',
		'page',
		'pagenow',
		'hook_suffix',
		'typenow',
		//'current_user',
		'parent_file',
		'self',
		'submenu_file',
		'taxnow',
		'typenow'
	);

	/**
	 * Load the object and get the current state
	 *
	 * @since   09/24/2013
	 * @return Debug_Objects_Screen_Info|String $class_object
	 */
	public static function init() {

		if ( NULL == self::$class_object )
			self::$class_object = new self;
		
		return self::$class_object;
	}

	/**
	 * Init function to register all used hooks
	 *
	 * @since   09/24/2013
	 */
	public function __construct() {
		
		if ( ! current_user_can( '_debug_objects' ) )
			return NULL;
		
		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}
	
	/**
	 * Create tab for this data
	 * 
	 * @param  Array $tabs
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {
		
		$tabs[] = array( 
			'tab' => __( 'Screen Info' ),
			'function' => array( $this, 'get_output' )
		);
		
		return $tabs;
	}
	
	public function get_output() {
		
		$output  = $this->get_screen_info();
		$output .= $this->get_formated_globals();
		
		echo $output;
	}
	
	public function get_screen_info() {
		
		if ( ! function_exists( 'get_current_screen' ) ) {
			$output = '<tr><td colspan="2">' . __( 'Current no screen info found.' ) . '</td></tr>';
		} else {
		
			$screen = get_current_screen();
			
			$output = '';
			$class  = '';
			
			if ( NULL !== $screen ) {
				
				foreach ( $screen as $key => $data ) {
					
					// class for formatting
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
					
					if ( empty( $data ) )
						$data = __( 'Empty' );
					
					$output .= '<tr' . $class . '><td>' . $key . '</td><td>';
					$output .= $data . '</td></tr>';
					
				}
				
			} else {
				$output = __( 'Current no screen info found.' );
			}
			
		}
		
		$output = '<table class="tablesorter"><thead><tr><th><code>current_screen</code> ' 
			. __( 'Key, <a href="http://codex.wordpress.org/Function_Reference/get_current_screen">Help</a>' ) 
			. '</th><th>' . __( 'Data' ) . '</th></tr></thead>'
			. $output . '</table>';
		
		return $output;
	}
	
	public function get_formated_globals() {
		
		$globals = apply_filters( 'debug_objects_globals', $this->wp_globals );
		
		$output = '';
		$class  = '';
		
		foreach ( $globals as $key => $value ) {
			
			if ( ! empty( $GLOBALS[ $value ] ) ) {
				
				$global_value = $GLOBALS[ $value ];
				
				// class for formatting
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				
				// check for object
				if ( is_object( $global_value ) ) {
					$global_value = (array) $global_value;
					$global_value = Debug_Objects::pre_print( $global_value, '', TRUE );
				} else {
					$global_value = '<code>' . $global_value . '</code>';
				}
				
				$output .= '<tr' . $class . '>';
				$output .= '<td><code>' . $value . '</code></td>';
				$output .= '<td>' . $global_value . '</td>';
				$output .= '</tr>';
				
				unset( $globals[ $key ] );
			}
			
		}
		
		$output = '<table class="tablesorter"><thead><tr><th>' . __( 'Global variable' ) . '</th><th>' . __( 'Value' ) . '</th></tr></thead>' 
			. $output . '</table>';
		
		return $output;
	}
	
}
