include =  function(url){
    var con = new Ext.data.Connection();
    con.request({
        url: 'spip.php?page=' + url + '.js&ts=' + (new Date().format('Ymd_his')),
        method: 'GET',
        callback: function(opts, success, response)  {

	        if (!success) {
		        Ext.MessageBox.alert("Error", 
		            success ? response.responseText  : 
		        "Error saving data - try again");
		        return;
	        }
	        var o = document.getElementById('script_' + url)
	        if (o) {
		        o.parentNode.removeChild(o);
	        }

	        s = document.createElement('script');
	        s.setAttribute('id', 'script_' + url);
	        s.setAttribute('type', 'text/javascript');
	        s.appendChild(document.createTextNode(response.responseText));
	        document.getElementsByTagName("body")[0].appendChild(s);
        }
    });

}