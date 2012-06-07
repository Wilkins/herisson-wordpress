<?php

function herisson_generate_keys_pair() {

 // Create the keypair
 $res=openssl_pkey_new();

 // Get private key
 openssl_pkey_export($res, $privkey);

 // Get public key
 $pubkey=openssl_pkey_get_details($res);
 $pubkey=$pubkey["key"];

 #print "public : $pubkey\n";
 #print "private : $privkey\n";

 return array($pubkey,$privkey);
}


function herisson_encrypt($data,$friend_public_key) {
 $options = get_option('HerissonOptions');
	$my_public_key  = $options['publicKey'];
	$my_private_key = $options['privateKey'];

# $options = get_option('HerissonOptions');
# $sealed = null;

# $data = "Only I know the purple fox. Trala la !";
 $hash = sha256($data);
	if (!openssl_private_encrypt($hash,$hash_crypted,$my_private_key)) {
	 echo __('Error while encrypting hash with my private key',HERISSONTD);
	}
# echo "$hash -> $hash_crypted<br>\n";
 $data_crypted = null;
# echo "data : $data<br><br>\n";
#	echo "friend public key : $friend_public_key<br><br>\n";
	openssl_get_publickey($friend_public_key);
#	echo "friend public key : $friend_public_key<br><br>\n";
#	echo "friend public key : ".openssl_get_publickey($friend_public_key)."<br><br>\n";
 if (!openssl_seal($data,$data_crypted,$seal_key,array($friend_public_key))) {
	 echo __('Error while encrypting data with friend public key<br>',HERISSONTD);
	}
# echo "$seal_key[0] , $data -> $data_crypted<br><br>\n";

	return array(
			 'data' => base64_encode($data_crypted),
				'hash' => base64_encode($hash_crypted),
				'seal' => base64_encode($seal_key[0]),
			);
 
 /*
 $crypted0 = $data;
 echo openssl_private_encrypt($crypted0,$crypted1,$priv1)."<br>\n";
 print_r("crypted1 : $crypted1<br>\n");
 
 echo openssl_public_encrypt($crypted1,$crypted2,$pub2)."<br>\n";
 print_r("crypted2 : $crypted2<br>\n");
 
 echo openssl_private_decrypt($crypted2,$crypted3,$priv2)."<br>\n";
 print_r("crypted3 : $crypted3<br>\n");
 
 echo openssl_public_decrypt($crypted3,$crypted4,$pub1)."<br>\n";
 print_r("crypted4 : $crypted4<br>\n");
	*/

}


function herisson_decrypt($json_string,$friend_public_key) {
 $options = get_option('HerissonOptions');
	$my_public_key  = $options['publicKey'];
	$my_private_key = $options['privateKey'];


 $json_data = json_decode($json_string,1);
 

 $data_crypted = base64_decode($json_data['data']);
 $hash_crypted = base64_decode($json_data['hash']);
	$seal         = base64_decode($json_data['seal']);

# $hash = sha256($data);
	if (!openssl_open($data_crypted,$data,$seal,$my_private_key)) {
	 echo __('Error while decrypt with my private key<br>',HERISSONTD);
	}
# echo "$data_crypted -> $data<br>\n";

 
 if (!openssl_public_decrypt($hash_crypted,$hash,$friend_public_key)) {
	 echo __('Error while decrypt with friend public key<br>',HERISSONTD);
	}
# echo "$hash_crypted -> $hash<br>\n";

 if (sha256($data) != $hash) {
  echo __('Error : mismatch between hash and data, maybe a man in the middle attack ?!');
	}
	return $data;
}

