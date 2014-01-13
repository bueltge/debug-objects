<?php
function super_var_dump($var) {
	super_var_dump::init($var);
}

class super_var_dump {

	private static $instance;
	private static $current_depth;
	static function init($a) {
		if ( empty(self::$instance) || ! self::$instance )
			self::$instance = 1;
		else
			self::$instance++;
		if ( empty(self::$current_depth) || ! self::$current_depth ) {
			self::$current_depth = 1; ?>
			<script type="text/javascript">
				function super_var_dump_toggle_div_display(instance, depth) {
					var div = document.getElementById( 'super-var-dump-container-' + instance + '-' + depth );
					var span = document.getElementById( 'super-var-dump-arrow-' + instance + '-' + depth );
					if ( div.style.display == 'none' ) {
						div.style.display = 'block';
						span.innerHTML = ' <span style="font-weight: bold; color: red;">v</span> ';
					}
					else {
						div.style.display = 'none';
						span.innerHTML = ' <span style="font-weight: bold; color: red;">></span> ';
					}
					window.getSelection().removeAllRanges();
				}
			</script>
			<?php
			echo '<pre style="font: 14px/22px Lucida Console; overflow: auto; background-color: #FCF7EC; overflow-x: auto; white-space: pre-wrap; white-space: -moz-pre-wrap !important; word-wrap: break-word; white-space: normal; padding: 20px;">';
		} else
			self::$current_depth++;

		self::select_variable_type($a);

		self::$current_depth--;
		if ( ! self::$current_depth )
			echo '</pre>';
	}

	static function select_variable_type($a) {
		if ( is_object($a) )
			self::output_object($a);
		else if ( is_array($a) )
			self::output_array($a);
		else if ( is_numeric($a) )
			self::output_numeric($a);
		else if ( is_string($a) )
			self::output_string($a);
		else if ( is_bool($a) )
			self::output_bool($a);
		else if ( is_null($a) )
			self::output_null($a);

	}
	static function output_object($a) {
		$object_vars = get_object_vars($a);
		echo '<ul style="padding:10px" id="super-var-dump-' . self::$instance . '">';
		echo '<span id="super-var-dump-arrow-' .  self::$instance . '-' . self::$current_depth . '" style="cursor: pointer" onclick="super_var_dump_toggle_div_display(' . self::$instance . ', ' . self::$current_depth . ')"> <span style="font-weight: bold; color: red;">></span> </span>Object';
		echo '<ul id="super-var-dump-container-' .  self::$instance . '-' . self::$current_depth . '" style="display:none; padding: 10px">';
		echo '<li>{</li>';
		echo '<ul style="padding: 10px">';
		foreach ( $object_vars as $object_var_key => $object_var_value ) {
			echo '<li style="padding: 0 0 5px 5px">';
			echo $object_var_key . ' => ';
			self::init($a->$object_var_key);
			echo '</li>';
		}
		echo '</ul>';
		echo '<li>}</li>';

		echo '</ul>';
		echo '</ul>';
	}
	static function output_array($a) {
		$keys = array_keys($a);

		$element = ( !empty( $keys ) ) ? 'ul' : 'span';

		$top = $element == 'ul' ? '<'. $element. ' style="padding:10px" id="super-var-dump-' . self::$instance . '">' : '<'. $element. ' id="super-var-dump-' . self::$instance . '">';

		echo $top;
		if ( !empty( $keys ) ) {
			echo '<span id="super-var-dump-arrow-' .  self::$instance . '-' . self::$current_depth . '" style="cursor: pointer" onclick="super_var_dump_toggle_div_display(' . self::$instance . ', ' . self::$current_depth . ')"> <span style="font-weight: bold; color: red;">></span> </span>array';
				echo '<ul id="super-var-dump-container-' .  self::$instance . '-' . self::$current_depth . '" style="display:none; padding: 10px">';
			echo '{';
			foreach($keys as $key) {
				echo '<li style="padding: 0 0 5px 5px">';
				echo "['" . $key  . "'] => ";
				super_var_dump($a[$key]);
				echo '</li>';
			}
			echo '}';
			echo '</ul>';
		} else {
			echo 'array()';
		}

		echo '</' .$element .'>';
	}
	static function output_numeric($a) {
		echo $a;
		// echo '<br style="padding: 5px 0;">';
	}
	static function output_string($a) {
		if ( ! $a ) echo '""';
		else echo $a;
		// echo '<br style="padding: 5px 0;">';
	}
	static function output_bool($a) {
		if ( $a ) echo 'true';
		else echo 'false';
		// echo '<br style="padding: 5px 0;">';
	}
	static function output_null($a) {
		echo 'NULL';
	}
}
?>