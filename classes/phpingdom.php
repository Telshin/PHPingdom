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
		if (!$appkey || !$username || !$password) {
			return false;
		}
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setAppkey($appkey);
	}

	private function getUsername() {
		$this->username;
	}

	private function setUsername($username) {
		$this->username = $username;
	}

	private function getPassword() {
		return $this->password;
	}

	private function setPassword($password) {
		$this->password = $password;
	}

	private function getAppKey() {
		return $this->appkey;
	}

	private function setAppkey($appkey) {
		$this->appkey = htmlspecialchars($appkey);
	}

/* Curl Function */
	private function curlPingdom($method, $url, $postData = null, $debug = false){
		//Let's setup CURL to get our information
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Follow Redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return Transfer as a string
		curl_setopt($ch, CURLOPT_USERPWD, $this->getUsername().':'.$this->getPassword());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('App-Key: ' . $this->getAppKey()));

		//Let's get some methods determined for CURL
		switch ($method){
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
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
		$endpoint = $this->base.'/checks';

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/* Get a check by ID */
	public function getCheckById($checkId) {
		$endpoint = '/checks/'.$checkId;

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	public function getCheckSummaryAverage($checkId, $args = null) {
		$endpoint = '/summary.average/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		//Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	public function getCheckSummaryHourly($checkId, $args = null) {
		$endpoint = '/summary.hoursofday/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		//Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	public function getCheckOutageSummary($checkId, $args = null) {
		$endpoint = '/summary.outage/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/* URL Arguements for Curl URL */
	private function curlArguments($arguements){
		foreach($arguements as $key => $value){
			$args[] = $key.'='.$value;
		}
		return $args;
	}

	/* Build the URL needed to cURL */
	private function buildUrl($task, $arguments = null) {
		$url = $this->base.$task;
		if ($arguments) {
			$args = $this->curlArguments($arguments);
			$url = $url.'/'.implode('&', $args);
			$url = str_replace( '&amp;', '&', urldecode(trim($url)));
		}
		return $url;
	}
}