<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

if (!defined(_TRANSLATE_API_KEY))
	define('_TRANSLATE_API_KEY','AIzaSyDwdmOUIFWdfwqMadQcFxHElAmATG40S-Y');



/**
 * Traduire une chaine ou un tableau de chaine a la volee
 * en utilisant un cache si possible
 * @param string|array $q
 * @param string $lang_src
 * @param string $lang_dest
 * @return string|array
 */
function translate($q,$lang_src,$lang_dest){
	$qp = $q;
	if (!is_array($qp))
		$qp = array($q);


	list($done,$todo) = translate_split_known_tr($qp, $lang_src, $lang_dest);
	$todo = translate_process_tr($todo, $lang_src, $lang_dest);

	foreach($qp as $k=>$v){
		if (isset($done[$k]))
			$qp[$k] = $done[$k];
		else
			$qp[$k] = $todo[$k];
	}

	if (!is_array($q))
		return reset($qp);

	return $qp;
}




/**
 * Recuperer un texte en cache si dispo, null sinon
 * @param string $texte
 * @param string $lang_src
 * @param string $lang_dest
 * @return string|null
 */
function translate_get_cache_tr($texte,$lang_src,$lang_dest){
	$md5 = md5(serialize(array($texte,$lang_src,$lang_dest)));

	// TODO : piocher dans le cache

	// pas de trad en cache dispo pour ce texte
	return null;
}

/**
 * Memoriser en cache un tableau de chaines
 * @param string $tr
 *   tableau au format texte source => texte traduit
 * @param string $lang_src
 * @param string $lang_dest
 */
function translate_set_cache_tr($tr, $lang_src, $lang_dest){

	$insert = array();
	foreach($tr as $src=>$t){
		$insert = array('md5'=>md5(serialize(array($src,$lang_src,$lang_dest))),'trad'=>$t);
	}
	// TODO : a inserer en base
	
}

/**
 * Separer le tableau de chaine a traduire $q
 * en deux sous tableaux :
 * les trads deja connues et celle a faire
 *
 * @param array $q
 * @param string $lang_src
 * @param string $lang_dest
 * @return array
 */
function translate_split_known_tr($q,$lang_src,$lang_dest){

	$done = array();
	$todo = array();

	foreach($q as $k=>$v){
		if ($t = translate_get_cache_tr($v,$lang_src,$lang_dest))
			$done[$k] = $t;
		else
			$todo[$k] = $v;
	}

	return array($done,$todo);
}

/**
 * Traduire un tableau de chaines
 * les cles initiales du tableau sont conservees
 *
 * @param array $q
 * @param string $lang_src
 * @param string $lang_dest
 * @return array
 */
function translate_process_tr($q,$lang_src,$lang_dest){
	if (!count($q))
		return $q;

	$url = "https://www.googleapis.com/language/translate/v2?key="._TRANSLATE_API_KEY;
	$url .= "&source=$lang_src&target=$lang_dest";

	foreach($q as $s)
		$url .= "&q=".urlencode($s);

	include_spip('inc/distant');
	$res = recuperer_page($url);
	$translations = json_decode($res);
	$translations = $translations->data->translations;
	$t = array();
	foreach($translations as $translation)
		$t[] = str_replace("#esp","#spp",$translation->translatedText);

	$to_cache = array();
	foreach($q as $k=>$v){
		$q[$k] = array_shift($t);
		$to_cache[$v] = $t;
	}

	// appeler la mise en cache
	translate_set_cache_tr($to_cache,$lang_src,$lang_dest);

	return $q;
}