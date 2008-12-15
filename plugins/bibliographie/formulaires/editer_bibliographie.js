function formulaire_biblio_init(){
	if ($("#journal")[0].autocompleter==undefined) {
		$('#journal').autocomplete(url_autocomp_journal, {minChars:3, matchSubset:0, matchContains:1, cacheLength:10 });
	}
}
jQuery('document').ready(function(){
	formulaire_biblio_init();
	onAjaxLoad(formulaire_biblio_init);
	$('#auteurs,#titre,#journal,#annee,#volume,#premiere_page,#derniere_page,#supplement,#numero').bind('change',function(){
		$('blockquote.citation').load(url_cite,{
			auteurs:$('#auteurs').attr('value'),
			titre:$('#titre').attr('value'),
			journal:$('#journal').attr('value'),
			annee:$('#annee').attr('value'),
			volume:$('#volume').attr('value'),
			premiere_page:$('#premiere_page').attr('value'),
			derniere_page:$('#derniere_page').attr('value'),
			supplement:$('#supplement').attr('value'),
			numero:$('#numero').attr('value')
		},
		function(){
			$('blockquote.citation').css('background','#FFFFCF');
			setTimeout(function() {$('blockquote.citation').css('background','#FFFFDF');
				setTimeout(function() {$('blockquote.citation').css('background','transparent');},1000);
			},2000);
		});
	});
});
