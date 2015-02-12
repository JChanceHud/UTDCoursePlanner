var page = require("webpage").create();
 
page.open('http://coursebook.utdallas.edu/s/', function (status) {
	page.evaluate(function(){
	});
	var cookieStr = "";
	for(var x = 0; x < page.cookies.length; x++){
		cookieStr = cookieStr + page.cookies[x].name + "=" + page.cookies[x].value + ";";
	}
	console.log(cookieStr);
	phantom.exit();
});

