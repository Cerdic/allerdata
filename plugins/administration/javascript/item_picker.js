/**
<li class='picker'>
...
<label>...</label>
... 
<ul class='item_picked'>..</ul>
... 
<div class='item_picker'>
</div>
</li>
**/
$(document).ready(function(){
	var picked = $('ul.item_picked');
	if (picked.length) {
		picked.find('>li').removeClass('last').find(':last').addClass('last');
	}
});

jQuery.fn.item_pick = function(id_item,name){
	var picked = this.parents('li.picker').find('ul.item_picked');
	if (!picked.length) {
		this.parents('li.picker').find('label:first').after("<ul class='item_picked'></ul>");
		picked = this.parents('li.picker').find('ul.item_picked');
	}
	var sel=jQuery('input[value='+id_item+']',picked);
	$('li.show',picked).removeClass('show');
	if (sel.length==0){
		jQuery('li:last',picked).removeClass('last');
		picked.append('<li class="last show">'
		+'<input type="hidden" name="'+name+'[]" value="'+id_item+'"/>'
		+ this.html()
		+" <a href='#' onclick='jQuery(this).item_unpick();return false;'>"
		+"<img src='"+img_unpick+"' /></a>"
		+'<em>, </em></li>');
	}
	else
		sel.parent().addClass('show');
	return this; // don't break the chain
}
jQuery.fn.item_unpick = function(){
	var picked = this.parents('li.picker').find('ul.item_picked');
	this.parent().remove();
	picked.find('>li').removeClass('last').find(':last').addClass('last');
}