<?php

	$l = mysql_connect('localhost','jpyrat','allerdata007');
	$q = mysql_query('select nom, id_item from allerdata.tbl_items');
	while($r = mysql_fetch_assoc($q))	{
		$chaine = $r['nom'];
		$chaine = strtr($chaine, 'àâäåãáÂÄÀÅÃÁçÇéèêëÉÊËÈïîìíÏÎÌÍñÑöôóòõÓÔÖÒÕùûüúÜÛÙÚÿ','aaaaaaAAAAAAcCeeeeEEEEiiiiIIIInNoooooOOOOOuuuuUUUUy');
		mysql_query("update allerdata.tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item']);	
		echo "update tbl_items set nom_sans_accent = '".addslashes($chaine)."' where id_item = ".$r['id_item'].";\n";	
	}	
	

?>
