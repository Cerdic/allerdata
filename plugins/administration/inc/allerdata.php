<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */

// l'argument align n'est plus jamais fourni
// http://doc.spip.org/@icone
function allerdata_icone_etendue($texte, $lien, $fond, $fonction="", $align="", $afficher='oui', $expose=false){
	global $spip_display;

	if ($fonction == "supprimer.gif") {
		$style = '-danger';
	} else {
		$style = '';
		if ($expose) $style=' on';
		if (strlen($fonction) < 3) $fonction = "rien.gif";
	}

	if ($spip_display == 1){
		$hauteur = 20;
		$largeur = 100;
		$title = $alt = "";
	}
	else if ($spip_display == 3){
		$hauteur = 30;
		$largeur = 30;
		$title = "\ntitle=\"$texte\"";
		$alt = $texte;
	}
	else {
		$hauteur = 70;
		$largeur = 100;
		$title = '';
		$alt = $texte;
	}

	$size = 24;
	if (preg_match("/-([0-9]{1,3})[.](gif|png)$/i",$fond,$match))
		$size = $match[1];
	if ($spip_display != 1 AND $spip_display != 4){
		if ($fonction != "rien.gif"){
		  $icone = http_img_pack($fonction, $alt, "$title width='$size' height='$size'\n" .
					  http_style_background($fond, "no-repeat center center"));
		}
		else {
			$icone = http_img_pack($fond, $alt, "$title width='$size' height='$size'");
		}
	} else $icone = '';

	if ($spip_display != 3){
		$icone .= "<span>$texte</span>";
	}

	// cas d'ajax_action_auteur: faut defaire le boulot 
	// (il faudrait fusionner avec le cas $javascript)
	if (preg_match(",^<a\shref='([^']*)'([^>]*)>(.*)</a>$,i",$lien,$r))
	  list($x,$lien,$atts,$texte)= $r;
	else $atts = '';
	$lien = "\nhref='$lien'$atts";

	$icone = "\n<div style='width:$largeur' class='icone36$style'>"
	. ($expose?"":"<a"
	. $lien
	. '>')
	. $icone
	. ($expose?"":"</a>")
	. "</div>\n";

	if ($afficher == 'oui')	echo $icone; else return $icone;
}

function allerdata_barre_nav_gauche($page_actuelle,$liste_items){
	static $deja_style=false;
	$out = "";
	if (!$deja_style){
		$out = "<style>
#navigation .icone36 span {height:auto;}
.icone36.on{text-align:center;text-decoration:none;}
.icone36.on img {
background-color:#FFFFFF;border:2px solid #666666;display:inline;margin:0pt;padding:4px;}
.icone36.on span {color:#000000;display:block;font-family:Verdana,Arial,Sans,sans-serif;font-size:10px;font-weight:bold;margin:2px;width:100%;}
.barre_nav .pointeur {margin-bottom:0.5em;}

#contenu .barre_nav {float:right;}
</style>";
		$deja_style = true;
	}
	$out .= "<div class='barre_nav'>";
	foreach($liste_items as $item){
		$out .= allerdata_icone_etendue($item['titre'], isset($item['url'])?$item['url']:generer_url_ecrire($item['page']), $item['icone'], isset($item['action'])?$item['action']:"rien.gif","", false, $page_actuelle==$item['page']);
	}
	return $out."</div>";
}
