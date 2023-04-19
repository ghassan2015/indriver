<?php namespace Taxi\Encrypter;

class Encrypter {
	
	const INSERT_STRING_AT = 5;
	const RANDOM_STRING_LENGTH = 40;
	const RANDOM_STRING_INSERTED_AT = 4;
	private $randomString;
	const KEY = 'gC07XPccldkuPrZUThOmyOrybtumsb2hjiIAk25C';
	
	public function __construct () {
		$this->randomString = str_random(self::RANDOM_STRING_LENGTH);
	}
	
	public function encrypt($data) {
		return $data;
		return $encodeString = base64_encode(json_encode($data));
		//dd($encodeString);
		//$lastChars = substr($encodeString, -2);
		//$removeLastChars = ($lastChars === '==') ? 2 : 1;
		//dd($encodeString);
		//$encryptedString = substr_replace($encodeString,"",-$removeLastChars);
		//dd('i mhere')
		//return  substr_replace( $encryptedString,$this->randomString,self::RANDOM_STRING_INSERTED_AT,0) ;
		//return  substr_replace( $encodeString,$this->randomString,self::RANDOM_STRING_INSERTED_AT,0) ;
	}

	public function decrypt( $payload) {
		//$this->validateKey( $payload);
	//dd($payload);
		$removedRandomString = substr_replace($payload,"",self::RANDOM_STRING_INSERTED_AT,self::RANDOM_STRING_LENGTH);
		return base64_decode(  ( $removedRandomString). '==');
	}
	
	
	private function validateKey($payload){
		try {
				$extractedKey = substr($payload,self::INSERT_STRING_AT - 1,self::RANDOM_STRING_LENGTH);
				if($extractedKey !== self::KEY) {
					throw new EncryptException( "Key not valid" );
				}
		} catch (\Exception $e) {
			return response($this->encrypt( $e->getMessage() ), 403);
		}
	}
}
