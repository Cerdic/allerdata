(function($) {
	$.ui.plugin.oldadd = function(w, c, o, p) {
		$.ui[w].prototype.plugins = $.ui[w].prototype.plugins || {};
		$.ui.plugin.add(w,o,{c:p});
	};
	
	// options.activeClass
	$.ui.plugin.oldadd("droppable", "activate", "activeClass", function(e,ui) {
		$(this).addClass(ui.options.activeClass);
	});
	$.ui.plugin.oldadd("droppable", "deactivate", "activeClass", function(e,ui) {
		$(this).removeClass(ui.options.activeClass);
	});
	$.ui.plugin.oldadd("droppable", "drop", "activeClass", function(e,ui) {
		$(this).removeClass(ui.options.activeClass);
	});

	// options.hoverClass
	$.ui.plugin.oldadd("droppable", "over", "hoverClass", function(e,ui) {
		$(this).addClass(ui.options.hoverClass);
	});
	$.ui.plugin.oldadd("droppable", "out", "hoverClass", function(e,ui) {
		$(this).removeClass(ui.options.hoverClass);
	});
	$.ui.plugin.oldadd("droppable", "drop", "hoverClass", function(e,ui) {
		$(this).removeClass(ui.options.hoverClass);
	});

})(jQuery);
