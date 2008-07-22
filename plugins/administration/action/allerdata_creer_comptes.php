<?php
/*
 * Plugin allerdata / Admin du site
 * Licence GPL
 * (c) 2008 C.Morin Yterium pour Allerdata SARL
 *
 */


include_spip('inc/acces');

function allerdata_pass_clair(){
	static $seeded;
	$pass = "aller";

	if (!$seeded) {
		$seed = (double) (microtime() + 1) * time();
		mt_srand($seed);
		srand($seed);
		$seeded = true;
	}

	$s = mt_rand(1000,9999);
	if (!$s) $s = rand(1000,9999);
	return "$pass$s";
}

function allerdata_creer_comptes(){
	$liste = _request('creer_comptes');
	$liste = preg_split(';[,\s]+;Uims',$liste,null,PREG_SPLIT_NO_EMPTY);

	include_spip('base/abstract_sql');
	$ids = array();
	$out = "";
	include_spip('inc/filtres');
	if (!autoriser('creer', 'auteur', 0, NULL))
		return array("Acces interdit",array());
	
	foreach($liste as $email){
		if (email_valide($email)){
			$res = sql_select("*","spip_auteurs","email=".sql_quote($email)." OR login=".sql_quote($email));
			if (!$row = sql_fetch($res)){
				$pass_clair = allerdata_pass_clair();
				$htpass = generer_htpass($pass_clair);
				$alea_actuel = creer_uniqid();
				$alea_futur = creer_uniqid();
				$pass = md5($alea_actuel.$pass_clair);
				
				$id_auteur = sql_insertq("spip_auteurs",
					array(
						'nom'=>$email,
						'login'=>$email,
						'email'=>$email,
						'source'=>'spip',
						'statut'=>'6forum',
						'pass_clair'=>$pass_clair,
						'pass'=>$pass,
						'htpass'=>$htpass,
						'alea_actuel'=>$alea_actuel,
						'alea_futur'=>$alea_futur,
						'low_sec'=>'',					
					)
				);
			}
			$res = sql_select("*","spip_auteurs","email=".sql_quote($email)." OR login=".sql_quote($email));
			if ($row = sql_fetch($res)){
				$droit = (autoriser('modifier', 'auteur',  $row['id_auteur'], NULL, array('mail'=>1)));				
				$ids[] = $row['id_auteur'];
				$out .= "<tr>"
				. "<td>".$row['id_auteur']."</td>"
				. "<td><a href='".generer_url_ecrire('auteur_infos','id_auteur='.$row['id_auteur'])."'>".$row['login']."</a>"."</td>"
				. "<td>".$row['email']."</td>"
				. "<td>".($droit?($row['pass_clair']?$row['pass_clair']:'mot de passe inconnu'):"*****")."</td>"
				."</tr>";
				
				// verifier les droits d'acces aux zones restreintes
				if ($droit AND !sql_getfetsel('id_zone','spip_zones_auteurs','id_auteur='.intval($row['id_auteur']).' AND id_zone=2')){
					sql_insertq('spip_zones_auteurs',array('id_auteur'=>$row['id_auteur'],'id_zone'=>2));
				}
			}
		}
		else {
				$out .= "<tr>"
				. "<td>"."</td>"
				. "<td>"."email invalide"."</td>"
				. "<td>".$email."</td>"
				. "<td>"."</td>"
				."</tr>";
		}
		
	}
	$out ="<table><tr><td>ID</td><td>login</td><td>email</td><td>mot de passe</td></tr>".
	"$out</table>";
	return array($out,$ids);	
}

function action_allerdata_creer_comptes_dist(){
	$securiser_action = charger_fonction('securiser_action','inc');
	$securiser_action();
	

	include_spip('inc/headers');
	redirige_par_entete($redirect);
}

?>