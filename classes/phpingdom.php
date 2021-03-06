<?php
/**
 * PHPingdom
 * PHPingdom Class File
 *
 * @author	Telshin
 * @license	GNU GPLv3.0
 * @package	PHPingdom
 * @link	http://www.telshin.com/
 *
 */
class phpingdom {
	/**
	 * Pingdom appkey for API system.
	 *
	 * @var String $appkey Pingdom API key
	 */
	private $appkey;

	/**
	 * Pingdom username, this is usually an e-mail
	 *
	 * @var String $username Pingdom username
	 */
	private $username;

	/**
	 * Pingdom password
	 *
	 * @var String $password Pingdom password
	 */
	private $password;

	/**
	 * Base URL for the Pingdom API
	 *
	 * @var String $base Base URL
	 */
	private $base = 'https://api.pingdom.com/api/2.0';

	/**
	 * Constructor for PHPingdom
	 *
	 * @param String $appkey API key
	 * @param String $username Username
	 * @param String $password Password
	 */
	public function __construct($appkey, $username, $password){
		if (!empty($appkey) || !empty($username) || !empty($password)) {
			return false;
		}
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setAppkey($appkey);
	}

	/**
	 * Username getter method
	 * @return String
	 */
	private function getUsername() {
		return $this->username;
	}

	/**
	 * Username setter method
	 * @param String $username The username to set
	 */
	private function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Password getter method
	 * @return String Password
	 */
	private function getPassword() {
		return $this->password;
	}

	/**
	 * Password setter method
	 * @param String $password The password to set
	 */
	private function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * AppKey getter method
	 * @return String
	 */
	private function getAppKey() {
		return $this->appkey;
	}

	/**
	 * AppKey setter method
	 * @param String $appkey The API key
	 */
	private function setAppkey($appkey) {
		$this->appkey = htmlspecialchars($appkey);
	}

	/**
	 * Sends out a cURL request to the Pingdom API
	 *
	 * @param String $method The method to use (post or get)
	 * @param String $url The URL to post to
	 * @param Array $postData The data to post with
	 * @param Boolean $debug Debug
	 * @return Integer|Mixed
	 */
	private function curlPingdom($method, $url, $postData = null, $debug = false){
		// Let's setup CURL to get our information
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Follow Redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return Transfer as a string
		curl_setopt($ch, CURLOPT_USERPWD, $this->getUsername() . ':' . $this->getPassword());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('App-Key: ' . $this->getAppKey()));

		// Let's get some methods determined for CURL
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

		// Close CURL if there are no errors, if error, let's see it.
		if (curl_errno($ch)){
			return curl_errno($ch);
		} else {
			curl_close($ch);
		}

		return json_decode($data);
	}

	/**
	 * Retrieve a list of actions for an account
	 * @see https://www.pingdom.com/features/api/documentation/#MethodGet+Actions+%28Alerts%29+List
	 *
	 * @param null $args
	 * @return int|Mixed
	 */
	public function getActions($args = null) {
		$endpoint = '/actions';

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Retrieve a full list of all checks by account
	 * @see https://www.pingdom.com/features/api/documentation/#MethodGet+Check+List
	 *
	 * @param Array $args The arguments to post
	 * @return Integer|Mixed
	 */
	public function getCheckList($args = null) {
		$endpoint = '/checks';

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Get check information by Id
	 * @see https://www.pingdom.com/features/api/documentation/#MethodGet+Detailed+Check+Information
	 *
	 * @param Integer|String $checkId The ID to check
	 * @return Integer|Mixed
	 */
	public function getCheckById($checkId) {
		$endpoint = '/checks/' . intval($checkId);

		$url = $this->buildUrl($endpoint);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Create a pingdom check
	 * @see https://www.pingdom.com/features/api/documentation/#MethodCreate+New+Check
	 *
	 * @param $args	Check data
	 * @return int|Mixed
	 */
	public function createCheck($args) {
		$errors = [];
		$mandatoryParameters = ['name', 'host', 'type'];
		$mandatoryTypeParameters = [
									'httpcustom'	=>	'url',
									'tcp'			=>	'port',
									'dns'			=>	['expectedip, nameserver'],
									'udp'			=>	['port', 'stringtosend', 'stringtoexpect']
									];

		if (!$args) {
			$errors[] = 'No arguements passed to createCheck()';
		}

		// We need to make sure all required parameters are included for the check.
		$mandatoryParameters = $this->validateParameters($args, $mandatoryParameters);
		if ($mandatoryParameters) {
			$errors[] = "Missing the following required parameters: ".implode(',', $mandatoryParameters);
		}

		$mandatoryTypeParameters = $this->validateParameters($args['type'], $mandatoryTypeParameters[$args['type']]);
		if ($mandatoryTypeParameters) {
			$errors[] = "Missing the following type paramters for ".$args['type'].":". implode(',', $mandatoryTypeParameters);
		}

		if (!$errors) {
			$endpoint = '/checks';

			$url = $this->buildUrl($endpoint);

			// Time to cURL
			$data = $this->curlPingdom('POST', $url, $args);
		} else {
			$data = $errors;
		}

		return $data;
	}

	/**
	 * Gets the average uptime value on a check for a period of time.
	 * @see https://www.pingdom.com/features/api/documentation/#ResourceSummary.hoursofday
	 *
	 * @param Integer|String $checkId The ID to check
	 * @param Array $args The arguments to post
	 * @return Integer|Mixed
	 */
	public function getCheckSummaryAverage($checkId, $args = null) {
		$endpoint = '/summary.average/' . intval($checkId);

		$url = $this->buildUrl($endpoint, $args);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Grabs an hourly average response time for a period of time.
	 * @see https://www.pingdom.com/features/api/documentation/#ResourceSummary.hoursofday
	 *
	 * @param Integer|String $checkId The ID to check
	 * @param Array $args The arguments to post
	 * @return Integer|Mixed
	 */
	public function getCheckSummaryHourly($checkId, $args = null) {
		$endpoint = '/summary.hoursofday/' . intval($checkId);

		$url = $this->buildUrl($endpoint, $args);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Grab a checks outage summary from a point in time.
	 * @see https://www.pingdom.com/features/api/documentation/#ResourceSummary.outage
	 *
	 * @param Integer|String $checkId The ID to check
	 * @param Array $args The arguments to post
	 * @return Integer|Mixed
	 */
	public function getCheckOutageSummary($checkId, $args = null) {
		$endpoint = '/summary.outage/' . intval($checkId);

		$url = $this->buildUrl($endpoint, $args);

		// Time to cURL
		$data = $this->curlPingdom('GET', $url);

		return $data;
	}

	/**
	 * Pass an array to break it out into arguments.
	 *
	 * @param Array $arguments The arguments to post
	 * @return Array
	 */
	private function curlArguments($arguments){
		$args = [];
		foreach ($arguments as $key => $value){
			$args[] = $key . '=' . $value;
		}
		return $args;
	}

	/**
	 * Pass a method and arguments to build the URL for cURL
	 *
	 * @param Integer|String $task The task to check
	 * @param Array $arguments The arguments to post
	 * @return Mixed|String
	 */
	private function buildUrl($task, $arguments = null) {
		$url = $this->base.$task;
		if ($arguments) {
			$args = $this->curlArguments($arguments);
			$url = $url . '/' . implode('&', $args);
			$url = str_replace('&amp;', '&', urldecode(trim($url)));
		}
		return $url;
	}

	private function validateParameters($args, $requiredParameters) {
		foreach($args as $key => $value) {
			if (in_array($key, $requiredParameters)) {
				unset($requiredParameters[$key]);
			}
		}

		return $requiredParameters;
	}
}
