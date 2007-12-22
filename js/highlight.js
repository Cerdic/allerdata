var timerRunning = false; // boolean flag
var myTimer = null;

function nohighlight(){
	timerRunning = false;
	jQuery('.actif').css('backgroundColor', "white");
	jQuery('.actif').removeClass('actif');
}

jQuery.fn.highlight = function(color, delay) {
    
	jQuery('.actif').css('backgroundColor', "white");
	jQuery('.actif').removeClass('actif');
	
	if (timerRunning)
		clearTimeout(myTimer);
		
	jQuery(this).addClass('actif');
	jQuery(this).css('backgroundColor', color);
	
	myTimer = setTimeout(nohighlight,delay);
	timerRunning = true;
};