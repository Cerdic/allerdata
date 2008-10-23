<?php
include_spip('inc/json');

function exergue($nom,$query){
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