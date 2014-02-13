<?php
/**
 * PHPivtoal
 * PHPivotal Class File
 *
 * @author: 	Telshin
 * @license: 	All Rights Reserved
 * @package: 	PHPivotal
 * @link		http://www.telshin.com/
 *
**/

class phpingdom {
	private $appkey;
	private $username;
	private $password;
	private $base = 'https://api.pingdom.com/api/2.0';

	public function __construct($appkey, $username, $password){
		$this->username = $username;
		$this->password = $password;
		$this->appkey = htmlspecialchars($appkey);
	}

/* Curl Function */
	private function curlPingdom($method, $job, $data = null){
		$debug = true;
		//Build the URL for CURL
		$job = str_replace( '&amp;', '&', urldecode(trim($job)));

		//Let's setup CURL to get our information
		$ch = curl_init($job);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Follow Redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return Transfer as a string
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('App-Key: ' . $this->appkey));

		//Let's get some methods determined for CURL
		switch ($method){
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case 'GET':
			default:
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
				break;
		}

		if ($debug === true) {
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}

		$data = curl_exec($ch);

		if ($debug === true) {
			$lastRequestInfo = curl_getinfo($ch);
		}

		//Close CURL if there are no errors, if error, let's see it.
		if(curl_errno($ch)){
			return curl_errno($ch);
		} else {
			curl_close($ch);
		}

		return json_decode($data);
	}

	/* Get Check List */
	public function getCheckList($args = null) {
		$job = $this->base.'/checks';

		// Time to cURL
		$data = $this->curlPingdom('GET', $job, $args);

		return $this->verifyData($data);
	}

	/* Get a check by ID */
	public function getCheckById($checkId) {
		$job = $this->base.'/checks/'.$checkId;

		// Time to cURL
		$data = $this->curlPingdom('GET', $job);

		return $this->verifyData($data);
	}


	/* Miscellaneous Functions */
	public function verifyData($data){
		if($data){
			return $data;
		} else {
			return false;
		}
	}

	/* URL Arguements for Curl URL */
	private function curlArguments($arguements){
		foreach($arguements as $key => $value){
			$args[] = $key.'='.$value;
		}
		return $args;
	}
}
?>