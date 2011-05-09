<?php
# les webmestres
#define('_ID_WEBMESTRES', '1:2');

$GLOBALS['ligne_horizontale'] = "\n<div class='hrspip'><hr /></div>\n";

# patch compatibilite vieux plugins
$table_des_traitements['TITRE'][]= 'typo(trim(supprimer_numero(%s)))';

#$GLOBALS['type_urls'] = 'propres2';

$GLOBALS['debut_intertitre'] = "<h2 class='spip'>";
$GLOBALS['fin_intertitre'] = '</h2>';

?>