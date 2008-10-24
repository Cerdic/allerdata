<?php
define('_FEED_GLOBALS', true);
include_spip('inc/vieilles_defs');

# patch compatibilite php < 5.1
if (!function_exists('json_encode')){
	function json_encode($var){
		include_spip('inc/json');
		return json_export($var);
	}
}

@define('_CONTEXTE_IGNORE_VARIABLES',"/(^var_|^PHPSESSID$|^_dc$)/");


?>