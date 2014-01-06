<?php
function PhpJobs_Decrypt($str,$crypt_method="base64"){
	$filter = PHPJOBS_CRYPT_KEY;
	$filter = md5($filter);
	$letter = -1;
	$newstr = '';
	$string = NULL;
	if($crypt_method=="zlib")
		$string = gzinflate($str);
	else
		$string = base64_decode($str);
	$strlen = strlen($string);

	for ( $i = 0; $i < $strlen; $i++ ){
		$letter++;
		if ( $letter > 31 )
			$letter = 0;

		$neword = ord($string{$i}) - ord($filter{$letter});
		if ( $neword < 1 )
			$neword += 256;
		$newstr .= chr($neword);
	}//for
	return $newstr;
}//Decryt
function PhpJobs_Encrypt($str,$crypt_method="base64"){
	$filter = PHPJOBS_CRYPT_KEY;
	$filter = md5($filter);
	$letter = -1;
	$newpass = '';
	$newstr = NULL;
	$strlen = strlen($str);
	for ( $i = 0; $i < $strlen; $i++ ){
		$letter++;
		if ( $letter > 31 )
			$letter = 0;
		$neword = ord($str{$i}) + ord($filter{$letter});
		if ( $neword > 255 )
			$neword -= 256;
		$newstr .= chr($neword);
	}//for
	$return = NULL;
	if($crypt_method=="zlib")
		$return = gzdeflate($newstr);
	else
		$return = base64_encode($newstr);
	return $return;
}//Encrypt