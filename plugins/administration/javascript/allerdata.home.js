jQuery(function() {
	var ul = jQuery("<ul></ul>");
	jQuery("#listes > .tabListe").each(function(){
		var id = jQuery(this).attr('id');
		ul.append("<li class='"+id+"'><a href='#"+id+"'>"+jQuery("h2,h3.title",this).eq(0).html()+"</a></li>");
	});
	jQuery("#listes").prepend(ul);
	jQuery('#listes > ul').tabs({ fx: { opacity: 'toggle' } });
});
function update_tab_titles(){
	jQuery("#listes > .tabListe").each(function(){
		var id = jQuery(this).attr('id');
		var title = '-';
		if (jQuery("h2,h3.title",this).length)
			title = jQuery("h2,h3.title",this).eq(0).html();
		jQuery('#listes > ul > li.'+id+' > a').html(title);
	});
}
onAjaxLoad(update_tab_titles);