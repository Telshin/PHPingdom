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
	/**
	 * Appkey from Pingdom's API system.
	 *
	 * @var string
	 */
	private $appkey;

	/**
	 * Username that is usually an e-mail
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password for the Pingdom account
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Base URL for the Pingdom API
	 *
	 * @var string
	 */
	private $base = 'https://api.pingdom.com/api/2.0';

	/**
	 * Constructor for PHPingdom
	 *
	 * @param $appkey	string
	 * @param $username	string
	 * @param $password	string
	 */
	public function __construct($appkey, $username, $password){
		if (!$appkey || !$username || !$password) {
			return false;
		}
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setAppkey($appkey);
	}

	/**
	 * Username getter method
	 * @return mixed
	 */
	private function getUsername() {
		return $this->username;
	}

	/**
	 * Username setter method
	 * @param $username	string
	 */
	private function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Password getter method
	 * @return mixed
	 */
	private function getPassword() {
		return $this->password;
	}

	/**
	 * Password setter method
	 * @param $password	string
	 */
	private function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * AppKey getter method
	 * @return mixed
	 */
	private function getAppKey() {
		return $this->appkey;
	}

	/**
	 * AppKey setter method
	 * @param $appkey	string
	 */
	private function setAppkey($appkey) {
		$this->appkey = htmlspecialchars($appkey);
	}

	/**
	 * Sends out a cURL request to the Pingdom API
	 *
	 * @param $method	string
	 * @param $url	string
	 * @param null $postData
	 * @param bool $debug
	 * @return int|mixed
	 */
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

	/**
	 * Retrieve a full list of all checks by account
	 * For more info: https://www.pingdom.com/features/api/documentation/#MethodGet+Check+List
	 *
	 * @param null $args
	 * @return int|mixed
	 */
	public function getCheckList($args = null) {
		$endpoint = $this->base.'/checks';

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Get check information by Id
	 * For more info: https://www.pingdom.com/features/api/documentation/#MethodGet+Detailed+Check+Information
	 *
	 * @param $checkId
	 * @return int|mixed
	 */
	public function getCheckById($checkId) {
		$endpoint = '/checks/'.$checkId;

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Gets the average uptime value on a check for a period of time.
	 * For more info: https://www.pingdom.com/features/api/documentation/#ResourceSummary.hoursofday
	 *
	 * @param $checkId
	 * @param null $args
	 * @return int|mixed
	 */
	public function getCheckSummaryAverage($checkId, $args = null) {
		$endpoint = '/summary.average/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		//Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Grabs an hourly average response time for a period of time.
	 * For more info: https://www.pingdom.com/features/api/documentation/#ResourceSummary.hoursofday
	 *
	 * @param $checkId
	 * @param null $args
	 * @return int|mixed
	 */
	public function getCheckSummaryHourly($checkId, $args = null) {
		$endpoint = '/summary.hoursofday/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		//Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Grab a checks outage summary from a point in time.
	 * For more info: https://www.pingdom.com/features/api/documentation/#ResourceSummary.outage
	 *
	 * @param $checkId
	 * @param null $args
	 * @return int|mixed
	 */
	public function getCheckOutageSummary($checkId, $args = null) {
		$endpoint = '/summary.outage/'.$checkId;

		$url = $this->buildUrl($endpoint, $args);

		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Pass an array to break it out into arguments.
	 *
	 * @param $arguements
	 * @return array
	 */
	private function curlArguments($arguements){
		foreach($arguements as $key => $value){
			$args[] = $key.'='.$value;
		}
		return $args;
	}

	/**
	 * Pass a method and arguments to build the URL for cURL
	 *
	 * @param $task
	 * @param null $arguments
	 * @return mixed|string
	 */
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