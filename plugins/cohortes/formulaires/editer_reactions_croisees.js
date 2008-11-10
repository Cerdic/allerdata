function set_id_item(li,$input){
	var id_item = li.extra[0];
	jQuery($input).siblings('input[@type=hidden]').attr('value',id_item);
}
function formulaire_rc_init(){
	var s = jQuery('td.editer_produit1 input.text,td.editer_produit2 input.text');
	if (s.get(0).autocompleter==undefined) {
		s.autocomplete(url_autocomp_produit_rc, {minChars:3, matchSubset:0, matchContains:1, cacheLength:10, onItemSelect:set_id_item });
	}
}
$('document').ready(function(){
	formulaire_rc_init();
	onAjaxLoad(formulaire_rc_init);
});