function formulaire_biblio_init(){
	if ($("#journal")[0].autocompleter==undefined) {
		$('#journal').autocomplete(url_autocomp_journal, {minChars:3, matchSubset:0, matchContains:1, cacheLength:10 });
	}
}
$('document').ready(function(){
	formulaire_biblio_init();
	onAjaxLoad(formulaire_biblio_init);
});