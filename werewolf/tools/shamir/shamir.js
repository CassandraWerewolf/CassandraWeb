$(function() {

	// don't cache ajax content
	$.ajaxSetup ({  
		cache: false  
	});	

	// setup ajax callbacks
    $(":button").click(function () {
		window[this.id](this.id);
    });

});

function reset() {
	$(":input").val("");
	return false;
}

function get_shares(name) {
	var num_shares = $("#num_shares").val();
	var num_thresh = $("#num_thresh").val();
	var ident = $("#ident").val();
	var secret = $("#secret").val();

	$.getJSON('shamir.php', 
		{'function'   : name,
		 'num_shares' : num_shares,
		 'num_thresh' : num_thresh,
		 'ident' : ident,
		 'secret'     : secret},
		function(data) {
			$("#shares_list").val(data.shares_list);
		}
	);
    return false;
}

function get_secret(name) {
	var num_thresh = $("#num_thresh").val();
	var shares_list = $("#shares_list").val();

	$.getJSON('shamir.php', 
		{'function'   : name,
		 'num_thresh' : num_thresh,
		 'shares_list': shares_list},
		function(data) {
			$("#secret").val(data.secret);
		}
	);
    return false;
}
