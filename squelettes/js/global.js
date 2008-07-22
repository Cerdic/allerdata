
/* fonction pour 1. mettre le label dans le input, 2.vider le champs de recherche au focus et le remplir au blur si laissé vide */
jQuery.fn.labelOver = function(overClass) {
	return this.each(function(){
		var label = jQuery(this);
		var f = label.attr('for');
		if (f) {
			var input = jQuery('#' + f);
			
			this.hide = function() {
			  label.css({ textIndent: -10000 })
			}
			
			this.show = function() {
			  if (input.val() == '') label.css({ textIndent: 0 })
			}

			// handlers
			input.focus(this.hide);
			input.blur(this.show);
		   label.addClass(overClass).click(function(){ input.focus() });
			
			if (input.val() != '') this.hide(); 
		}
	})
}

	$(document).ready(function() {
		// appel de la fonction jQuery.fn.labelOver
		$('#searchForm label').labelOver('over-apply');
		
		// appel à la fonction de repliage/dépliage du menu pour rubriques
		$("ul#rubriques").treeview({unique: true, collapsed: true});
	});