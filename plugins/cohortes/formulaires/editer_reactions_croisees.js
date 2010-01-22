function set_id_item(li,$input){
	var id_item = li.extra[0];
	jQuery($input).siblings('input[@type=hidden]').val(id_item);
	var texte = jQuery($input).val();
	var reg=new RegExp("(<b>|</b>)", "gi");
	texte = texte.replace(reg,'');
	// virer les span fermants
	reg=new RegExp("</span>", "gi");
	texte = texte.replace(reg,'');
	//alert(texte);
	reg=new RegExp("<span>", "i");
	texte = texte.split(reg);
	//alert(texte);
	var nom = texte.shift();
	if (!nom) // FF et Safari renvoient une premiere capture vide (avant le span), IE non
		nom = texte.shift();
	jQuery($input).val(nom); // le nom
	// le reste va en description
	texte = "<span>"+texte.join('</span><span>')+'</span>';
	//alert(texte);
	jQuery($input).siblings('.more').html(texte);
	jQuery($input).get(0).focus();
}
function formulaire_rc_init(){
	var s = jQuery('td.editer_produit1 input.text,td.editer_produit2 input.text');
	if (s.get(0).autocompleter==undefined) {
		s.autocomplete(url_autocomp_produit_rc, 
		{minChars:3, matchSubset:0, matchContains:1, cacheLength:10, width:"400px", resultsClass:"ac_results ac_produits_rc", onItemSelect:set_id_item }
		);
	}
	var err = jQuery('.formulaire_editer_reactions_croisees td.erreur').find('input:visible,textarea:visible');
	if (err.length) err.get(0).focus();
	/*
	$("td.editer_produit1 textarea,td.editer_produit2 textarea,td.editer_remarques textarea").focus(function(){
			$(this).animate({ width:"200px"}, 300,'linear'); // enlarge width
	}).blur(function(){
			$(this).css('width','100%'); // return to original value
	});*/
}

function ajouter_nouvelle_rc(node){
	jQuery('#editer_rc-new').css('visibility','visible');
	jQuery(node).parents('p.boutons').hide().siblings('p.boutons').show();
	jQuery('#editer_rc-new input:visible').get(0).focus();
}
$('document').ready(function(){
	formulaire_rc_init();
	onAjaxLoad(formulaire_rc_init);
});
