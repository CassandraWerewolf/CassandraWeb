<?php
echo json_encode($_GET['function']($_GET));
exit();

function get_pkeys($data) {
	$config = array(
		"digest_alg" => "sha256",
		"private_key_bits" => 2048
	);

	$k = array();
	if(isset($data['priv_key']) && !empty($data['priv_key'])) {
		$res = openssl_pkey_get_private($data['priv_key']);
		$k['priv_key'] = $data['priv_key'];
	}else{
		$res=openssl_pkey_new($config);
		openssl_pkey_export($res, $k['priv_key']);
	}

	$details = openssl_pkey_get_details($res);
	$k['pub_key'] = $details["key"];
	return($k);
}

function rsa_pub_enc($data) {
	$k = array();

	# encrypt with the given public key
    openssl_public_encrypt($data['plain_text'], $k['cipher_text'], $data['pub_key'], OPENSSL_PKCS1_OAEP_PADDING);

	# add header
	$cipher_text  = "-----BEGIN RSAES-OAEP ENCRYPTED MESSAGE-----\n";

	# base64 encode and add newline to split into 64 charater chunks
	$cipher_text .= chunk_split(base64_encode($k['cipher_text']), 64, "\n");

	# add footer
	$cipher_text .= "-----END ENCRYPTED MESSAGE-----";

	$k['cipher_text'] = $cipher_text;
	return($k);
}

function rsa_priv_dec($data) {
	$k = array();

	# convert str to array
	$data['cipher_text'] = explode("\n", $data['cipher_text']); 

	# remove header and footer
	array_pop($data['cipher_text']);
	array_shift($data['cipher_text']);

	# convert array back to string and then base4 decode
	$data['cipher_text'] = implode("", $data['cipher_text']);
	$data['cipher_text'] = base64_decode($data['cipher_text']);

	# decrypt with the given private key
	openssl_private_decrypt($data['cipher_text'], $k['plain_text'], $data['priv_key'], OPENSSL_PKCS1_OAEP_PADDING);
	return($k);
}
?>
