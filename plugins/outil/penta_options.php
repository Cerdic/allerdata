<?php
define('_FEED_GLOBALS', true);
#include_spip('inc/vieilles_defs');

# patch compatibilite php < 5.1
if (!function_exists('json_encode')){
	function json_encode($var){
		include_spip('inc/json');
		return json_export($var);
	}
}

// le contexte de spip doit ignorer les variables _dc_xxx ajoutees par l'ajax de ext
// pour tromper le cache navigateur
// mais qui n'a pas d'impact sur le calcul de la page, et ne doit donc pas
// forcer un calcul
@define('_CONTEXTE_IGNORE_VARIABLES',"/(^var_|^PHPSESSID$|^_dc$)/");


?>