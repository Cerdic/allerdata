<?php
# les webmestres
define('_ID_WEBMESTRES', '1:2');

$GLOBALS['ligne_horizontale'] = "\n<div class='hrspip'><hr /></div>\n";

# patch compatibilite vieux plugins
$table_des_traitements['TITRE'][]= 'typo(trim(supprimer_numero(%s)))';
define('_FEED_GLOBALS', true);
#$GLOBALS['type_urls'] = 'propres2';
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