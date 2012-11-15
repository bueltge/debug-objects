<?php

function super_var_dump($a) {
	global $super_var_dump;
	
	if ( empty( $super_var_dump ) || ! $super_var_dump->instance ) {
		$super_var_dump = new stdClass();
		$super_var_dump->instance = 1;
	} else
		$super_var_dump->instance++;
	
	if ( !isset( $super_var_dump->current_depth ) ) {
		$super_var_dump->current_depth = 1;
		?>
		<script type="text/javascript">
			function super_var_dump_toggle_div_display(instance, depth) {
				var div = document.getElementById( 'super-var-dump-container-' + instance + '-' + depth );
				var span = document.getElementById( 'super-var-dump-arrow-' + instance + '-' + depth );
				if ( div.style.display == 'none' ) {
					div.style.display = 'block';
					span.innerHTML = ' v ';
				}
				else {
					div.style.display = 'none';
					span.innerHTML = ' > ';
				}
				window.getSelection().removeAllRanges();
			}
		</script>
		<?php 
		echo '<pre style="font: 9pt Lucida Console; overflow: auto; background-color: #FCF7EC; overflow-x: auto; white-space: pre-wrap; white-space: -moz-pre-wrap !important; word-wrap: break-word; white-space: normal;">';
	} else
		$super_var_dump->current_depth++;

	if ( is_object($a) ) {
		$object_vars = get_object_vars($a);
		echo '<div style="padding-left:10px" id="super-var-dump-' . $super_var_dump->instance . '">';
		echo '<span id="super-var-dump-arrow-' .  $super_var_dump->instance . '-' . $super_var_dump->current_depth . '" style="cursor: pointer" onclick="super_var_dump_toggle_div_display(' . $super_var_dump->instance . ', ' . $super_var_dump->current_depth . ')"> > </span>Object';
		echo '<div id="super-var-dump-container-' .  $super_var_dump->instance . '-' . $super_var_dump->current_depth . '" style="display:none; padding-left: 10px">';
		echo '{';
		echo '<div style="padding-left: 10px">';
		foreach ( $object_vars as $object_var_key => $object_var_value ) {
			echo $object_var_key . ' => ';
			super_var_dump($a->$object_var_key);
			echo "\r";
		}
		echo '</div>';
		echo '}';

		echo '</div>';
		echo '</div>';
			
	}

	if ( is_array($a) ) {
		echo '<div style="padding-left:10px" id="super-var-dump-' . $super_var_dump->instance . '">';
		echo '<span id="super-var-dump-arrow-' .  $super_var_dump->instance . '-' . $super_var_dump->current_depth . '" style="cursor: pointer" onclick="super_var_dump_toggle_div_display(' . $super_var_dump->instance . ', ' . $super_var_dump->current_depth . ')"> > </span>array';
			echo '<div id="super-var-dump-container-' .  $super_var_dump->instance . '-' . $super_var_dump->current_depth . '" style="display:none; padding-left: 10px">';
			echo '{';
		$keys = array_keys($a);
		foreach($keys as $key) {
			echo '<div style="padding-left:5px">';
			echo "['" . $key  . "'] => ";
			super_var_dump($a[$key]);
			echo '</div>';
		}
		echo '}';
		echo '</div>';
		echo '</div>';
	}

	if ( is_numeric($a) ) {
		echo $a;
		echo "<BR>";
	}

	if ( is_string($a) ) {
		if ( ! $a )
			echo '""';
		else {
			echo $a;
		}
		echo "<BR>";
	}

	if ( is_bool($a) ) {
		if ( $a ) echo 'true';
		else echo 'false';
		echo "<BR>";
	}

	if ( is_null($a) ) {
		echo 'NULL';
		echo "<BR>";
	}
		

	$super_var_dump->current_depth--;
	if ( ! $super_var_dump->current_depth )
		echo '</PRE>';
}
