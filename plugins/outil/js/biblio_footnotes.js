/*
 *
$().ready(function footnotes()
{
	var notes = 0;
	
	$('img.biblio','#main').each( function() {
			notes++;
			$('#footnotes').append("<div class='footnote'><a name='note"+notes+"'></a>[<a href='#nlink"+notes+"' title='retour au texte'>" + notes + "</a>] " + $(this).attr('title') + " <a href='#nlink"+notes+"' title='retour au texte'>&#8617;</a></div>");
			$(this).wrap("<a name='nlink"+notes+"' href='#note"+notes+"'></a>");
		}
	);
		
});
*/