<?php
/*
 * Plugin Bibliographie / Admin des biblios
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

function action_supprimer_biblio_note_dist(){
	$securiser_action = charger_fonction('securiser_action','inc');
	$arg = $securiser_action();
	
	sql_delete('tbl_biblio_notes','id_biblio_note='.intval($arg));
	
}


?>