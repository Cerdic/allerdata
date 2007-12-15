<?php

	$l = mysql_connect('localhost','jpyrat','allerdata007');
	
	mysql_query("ALTER TABLE  `tbl_items`  ADD `nom_sans_accent` VARCHAR( 255 ) NOT NULL AFTER `nom`,
	ADD  `source` VARCHAR( 100 ) NOT NULL COMMENT  'de type 4' AFTER  `nom_sans_accent` ,
	ADD  `famille` VARCHAR( 100 ) NOT NULL COMMENT  'de type 2' AFTER  `source`,
	ADD  `source_sans_accent` VARCHAR( 100 ) NOT NULL AFTER  `source` ;");
	
	$q = mysql_query('select nom, id_item from allerdata.tbl_items');
	while($r = mysql_fetch_assoc($q))	{
		$chaine = $r['nom'];
		$chaine = str_replace('œ','oe',$chaine);
		$chaine = strtr($chaine, 'àâäåãáÂÄÀÅÃÁçÇéèêëÉÊËÈïîìíÏÎÌÍñÑöôóòõÓÔÖÒÕùûüúÜÛÙÚÿ','aaaaaaAAAAAAcCeeeeEEEEiiiiIIIInNoooooOOOOOuuuuUUUUy');
		mysql_query("update allerdata.tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item']);	
		echo "update tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item'].";\n";	
	}	
	
	mysql_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.source = b.nom, a.source_sans_accent = b.nom_sans_accent
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 4
	and a.id_type_item IN (3,5)");
	
	mysql_query("UPDATE `tbl_items` a, tbl_items b, tbl_est_dans ab
	set a.famille = b.nom
	WHERE 
	a.id_item = ab.id_item
	and ab.est_dans_id_item = b.id_item 
	and b.id_type_item = 2
	and a.id_type_item IN (3,5)");
	
?>