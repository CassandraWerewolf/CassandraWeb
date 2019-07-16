$(function() {

	// don't cache ajax content
	$.ajaxSetup ({  
		cache: false  
	});	

	// setup ajax callbacks
    $(":button").click(function () {
		window[this.id](this.id);
    });

	// If a link has been clicked, scroll the page to the link's hash target:
	$('.nav a,.footer a.up').click(function(e){
		$.scrollTo( this.hash || 0, 1500);
		e.preventDefault();
	});
});

function reset() {
	$(":input").val("");
	return false;
}

function get_pkeys(name) {
	var priv_key = $("#priv_key").val();

	$.getJSON('rsa.php', 
		{'function' : name, 
		'priv_key' : priv_key },
		function(data) {
			$("#priv_key").val(data.priv_key);
			$("#pub_key").val(data.pub_key);
		}
	);
    return false;
}

function rsa_pub_enc(name) {
	var pub_key = $("#pub_key").val();
	var plain_text = $("#plain_text").val();

	$.getJSON('rsa.php', 
		{'function' : name, 
		'pub_key' : pub_key,
        'plain_text' : plain_text },
		function(data) {
			$("#cipher_text").val(data.cipher_text);
		}
	);
    return false;
}

function rsa_priv_dec(name) {
	var priv_key = $("#priv_key").val();
	var cipher_text = $("#cipher_text").val();

	$.getJSON('rsa.php', 
		{'function' : name, 
		'priv_key' : priv_key,
        'cipher_text' : cipher_text },
		function(data) {
			$("#plain_text").val(data.plain_text);
		}
	);
    return false;
}
