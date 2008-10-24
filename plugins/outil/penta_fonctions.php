<?php

/**
 * Mapping vers var2js...
 *
 * @param unknown_type $var
 * @return unknown
 */
function penta_var2js($var){
	include_spip('inc/json');	
	return var2js($var);
}

/**
 * Exclure enfants et parents associes aux produits deja selectionnes
 *
 * @param unknown_type $p
 * @return unknown
 */
function penta_produits_exclus($p){
  // On ne veut pas proposer  :
	// - les produits du penta (dans $p)
  // - Leurs sous-produits
	// - ainsi que leurs parents
	
	if (!is_array($p))
			$p = explode(',',$p);
				
	include_spip('inc/allerdata_arbo');
	return array_merge(
	$p,
	allerdata_les_enfants($p,'',false,true),
	allerdata_les_parents($p,'',false,true)
	);
}

/**
 * Mettre en exergue la recherche dans la chaine trouvee
 *
 * @param unknown_type $nom
 * @param unknown_type $query
 * @return unknown
 */
function penta_exergue($nom,$query){
	static $chaine = array();
	if (!isset($chaine[$query])){
		include_spip('inc/charsets');
		$chaine[$query] = translitteration($query);
		$chaine[$query] = strtolower($chaine[$query]);
	}
	if ($query!=$chaine[$query])
		$nom = preg_replace("/".preg_quote($query)."/i",'<b>'.$query.'</b>',$nom);
	$nom = preg_replace("/".preg_quote($chaine[$query])."/i",'<b>'.$chaine[$query].'</b>',$nom);
	return $nom;
}
?>