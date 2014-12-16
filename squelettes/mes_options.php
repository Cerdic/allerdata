<?php
# les webmestres
#define('_ID_WEBMESTRES', '1:2');

$GLOBALS['ligne_horizontale'] = "\n<div class='hrspip'><hr /></div>\n";

# patch compatibilite vieux plugins
$table_des_traitements['TITRE'][]= 'typo(trim(supprimer_numero(%s)))';

#$GLOBALS['type_urls'] = 'propres2';

$GLOBALS['debut_intertitre'] = "<h2 class='spip'>";
$GLOBALS['fin_intertitre'] = '</h2>';

$GLOBALS['forcer_lang'] = true;
if ($lang = _request('lang')){
	include_spip('inc/cookie');
	spip_setcookie('spip_lang',$_COOKIE['spip_lang']=$lang);
}
else {
	if (isset($_COOKIE['spip_lang']))
		$_GET['lang'] = $_COOKIE['spip_lang'];
}
