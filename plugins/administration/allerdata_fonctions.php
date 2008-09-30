<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

include_spip('base/serial');
$GLOBALS['tables_principales']['spip_auteurs']['pass_clair']="tinytext DEFAULT '' NOT NULL";

/**
 * Regarder si un item a des enfants designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les enfants presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_sans_enfant($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_est_dans','ed.id_item='.intval($id_item));
}
/**
 * Regarder si un item a des parents designes par la table de liaison tbl_est_dans.
 * On fait une jointure sur les parents presumes pour verifier son existence reelle
 *
 * @param int $id_item
 * @return bool
 */
function allerdata_item_orphelin($id_item){
	include_spip('base/abstract_sql');
	return !sql_countsel('tbl_est_dans as ed JOIN tbl_items AS i ON i.id_item=ed.id_item','ed.id_est_dans='.intval($id_item));
}

?>