<?php
/*
 * Plugin Cohortes & RC
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

/**
 * Declaration des champs pour la recherche sur la tbl_items
 * utilisee dans le back office
 *
 * @param array $liste
 * @return array
 */
function cohorte_rechercher_liste_des_champs($liste){
	$liste['tbl_groupes_patient'] = array('nom'=>4,'description'=>1);
	return $liste;
}


?>