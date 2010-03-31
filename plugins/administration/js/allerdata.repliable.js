function set_items_repliables(){
	jQuery('ul.liste_items > li.item').each(function(){
		var h=jQuery(this).find('h3.repliable').eq(0);
		h.not('.clicable')
		.click(function(){
		var p=jQuery(this).parents('li.item').eq(0);
		p.toggleClass('court');
		})
		.addClass('clicable');
		h.parents('li.item').not('.on').addClass('court');
	});
}
function set_items_plies(){
	var h=jQuery('ul.liste_items li.item').find('h3.repliable');
	h.parents('li.item').not('.on').addClass('court');
}
jQuery('document').ready(function(){set_items_repliables();onAjaxLoad(set_items_repliables);});