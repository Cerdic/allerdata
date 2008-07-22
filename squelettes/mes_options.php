<?php
/**
 * ajout Yterium
 * 
 */

if (!function_exists('json_encode')){
	function json_encode($var){
		include_spip('inc/json');
		return json_export($var);
	}
}

?>