<?php
/**
 * @author Luca Gioppo
 * @created 28/11/2013
 */

include_once('easy.curl.class.php');

class Wso2API{
	private $curl;
	private $curl_options;
	private $api_server;
	private $api_user;
	private $api_password;
	private $isLoggedIn = false;
	
	
	private $login_path = '/publisher/site/blocks/user/login/ajax/login.jag';
	private $create_api_path = '/publisher/site/blocks/item-add/ajax/add.jag';
	private $status_api_path = '/publisher/site/blocks/life-cycles/ajax/life-cycles.jag';

	public $error_message = '';

	function __construct($api_server, $user = 'admin', $password = 'admin'){
		$headers[] = "Accept: */*";
		$headers[] = "Connection: Keep-Alive";
		$agent            = "Nokia-Communicator-WWW-Browser/2.0 (Geos 3.0 Nokia-9000i)";
		$cookie_file_path = "cookie.txt";
		$this->curl_options = array(CURLOPT_HTTPHEADER => $headers,
								   CURLOPT_SSL_VERIFYHOST=>0,
								   CURLOPT_SSL_VERIFYPEER=>false,
								   CURLOPT_COOKIEFILE=>$cookie_file_path,
								   CURLOPT_COOKIEJAR=>$cookie_file_path,
								   CURLOPT_FOLLOWLOCATION=>1,
								   CURLOPT_RETURNTRANSFER=>1,
								   CURLOPT_USERAGENT=>$agent,
								   CURLOPT_HEADER=>0);
		$this->api_server = $api_server;
		$this->api_user = $user;
		$this->api_password = $password;
		$this->curl =  new cURL($api_server);
		$this->curl->options($this->curl_options);
		echo ('construct');
	}
	
	public function login($user = '', $password = ''){
		if(!empty($user)) $this->api_user = $user;
		if(!empty($password)) $this->api_password = $password;
		$login_url = $this->api_server . $this->login_path;
		$login_ret = $this->curl->post($login_url, 
										array('action'=>'login',
											  'username'=>$this->api_user,
											  'password'=>$this->api_password),
										$this->curl_options);
		// two possible errors cURL and API manager
		if ($this->curl->error_code){
			$this->error_message = 'Login: '.$this->curl->error_string . ' - ' . $login_url . ' - ' . print_r(array('action'=>'login',
											  'username'=>$this->api_user,
											  'password'=>$this->api_password),true);
			echo print_r($this->curl->info,true);
			echo print_r($this->curl->error_code,true);
		}else{
			// have to interpret the return code to understand if the API returned error
			$this->isLoggedIn = true;
		}
		return $this->curl->error_code;
	}
	
	public function create_api($params, $resources, $autopublish = false){
		// if not logged in log with the standard data
		if(!$this->isLoggedIn){
			$login_result = $this->login();
			if ($login_result > 0){
				return $login_result;
			}
		}
		if (!is_array($resources)){
			$this->error_message = 'You have to define API resources';
			return false;
		}
		
		$create_api_url = $this->api_server . $this->create_api_path;
		
		$create_api_post = array('action'=>'addAPI',
							  'name'=>$params['name'],
							  'visibility'=>$params['visibility'],
							  'version'=>$params['version'],
							  'description'=>$params['description'],
							  'endpointType'=>$params['endpointType'],
							  'http_checked'=>($params['http']?'http':''),
							  'https_checked'=>($params['https']?'https':''),
							  'endpoint'=>$params['endpoint'],
							  'wsdl'=>$params['wsdl'],
							  'wadl'=>$params['wadl'],
							  'tags'=>$params['tags'],
							  'tier'=>$params['tier'],
							  'bizOwner'=>$params['bizOwner'],
							  'thumbUrl'=>$params['thumbUrl'],
							  'context'=>$params['context'],
							  'tiersCollection'=>$params['tiersCollection']);
		$create_api_post['resourceCount'] = count($resources) - 1;
		foreach ($resources as $i => $value) {
			$create_api_post['resourceMethod-'.$i] = $value['resourceMethod'];
			$create_api_post['resourceMethodAuthType-'.$i] = $value['resourceMethodAuthType'];
			$create_api_post['resourceMethodThrottlingTier-'.$i] = $value['resourceMethodThrottlingTier'];
			$create_api_post['uriTemplate-'.$i] = $value['uriTemplate'];
		}
						  /*
							  'resourceCount'=>'0',
							  'resourceMethod-0'=>'GET',
							  'resourceMethodAuthType-0'=>'Application',
							  'resourceMethodThrottlingTier-0'=>'Unlimited',
							  'uriTemplate-0'=>'/*');
						  */
		$create_api_ret = $this->curl->post($create_api_url, 
											$create_api_post,
											$this->curl_options);

		// two possible errors cURL and API manager
		if ($this->curl->error_code){
			$this->error_message = 'Create API: '.$this->curl->error_string;
		}else{
			// have to interpret the return code to understand if the API returned error
			$this->isLoggedIn = true;
		}
		
		// manage the autopublish option
		if ($autopublish) {
			$status_api_ret = $this->change_api_status('PUBLISHED', $params['provider'], $params['name'], $params['version'], true);
			// maybe only return $status....
			if (!$status_api_ret){
				return $status_api_ret;
			}
		}
		return true;
	}
	
	public function delete_api(){
	}
	
	public function change_api_status($status, $provider, $name, $version, $publishtogataway){
		// if not logged in log with the standard data
		if(!$this->isLoggedIn){
			$login_result = $this->login();
			if ($login_result > 0){
				return $login_result;
			}
		}
		
		if (!is_bool($publishtogataway)){
			$this->error_message = 'publishtogateway has to be boolean';
			return false;
		}
		$status_api_url = $this->api_server . $this->status_api_path;
		
		$status_api_post = array('action'=>'updateStatus',
								'name'=>$name,
								'version'=>$version,
								'provider'=>$provider,
								'status'=>$status,
								'publishToGateway'=>$publishtogataway);
		$publish_api_ret = $this->curl->post($status_api_url, 
											 $status_api_post,
											 $this->curl_options);

		// two possible errors cURL and API manager
		if ($this->curl->error_code){
			$this->error_message = 'Status change: '.$this->curl->error_string;
		}else{
			// have to interpret the return code to understand if the API returned error
			$this->isLoggedIn = true;
		}
	}
	
	
	

}

?>