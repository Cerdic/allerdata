<?php
	// Un appel ajax à ce fichier au moment du chergement de la page est nécessaire
	// Sinon un rafraichissement de la page (type 'F5') garde en mémoire les anciens éléments saisis
	// dans la liste des produits. Ceux-ci ne seraient alors plus suggérés dans les combos de saisie.
	function action_purge_sessions() {
	session_start();
	$_SESSION['produits_choisis'] = array();
	session_write_close();
	}
?>