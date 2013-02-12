<?php

class OauthRequest extends Request {
	
	protected $_consumer_key;
	protected $_consumer_secret;
	protected $_callback;
	protected $_access_token;
	protected $_access_token_secret;
	protected $_version = '1.0';
	protected $_signature;
	
	public function __construct($method, $url, Array $oauth, Array $parameters = array()){
		parent::__construct($method, $url, $parameters);
		if(isset($oauth['consumer_key']) && isset($oauth['consumer_secret'])){
			$this->_consumer_key = $oauth['consumer_key'];
			$this->_consumer_secret = $oauth['consumer_secret'];
			if(isset($oauth['callback'])){
				$this->_callback = $oauth['callback'];
			}
			if(isset($oauth['access_token'])){
				$this->_access_token = $oauth['access_token'];
			}
			if(isset($oauth['access_token_secret'])){
				$this->_access_token_secret = $oauth['access_token_secret'];
			}
			if(isset($oauth['version'])){
				$this->_version = $oauth['version'];
			}
		} else {
			throw \Exception('oauth parameters are not valid');
		}
	}
	
	public function execute(){
		$oauth = $this->signRequest();
		
		// TODO: set correct parameters
		
		parent::execute();
	}
	
	private function signRequest(){
		// include oauth parameters
		$this->_parameters['oauth_consumer_key'] = $this->_consumer_key;
		$this->_parameters['oauth_nonce'] = md5(time());
		$this->_parameters['oauth_signature_method'] = 'HMAC-SHA1';
		$this->_parameters['oauth_timestamp'] = time();
		$this->_parameters['oauth_version'] = $this->_version;
		if(isset($this->_access_token) && strlen($this->_access_token) > 0){
			$this->_parameters['oauth_token'] = $this->_access_token;
		}
		
		$this->buildParameterString();
		
		$this->generateOauthSignature();
		
		$this->buildAuthorizationHeader();
	}
	
	private function buildParameterString(){
		$encoded_params = array();
		foreach($this->_parameters as $k => $v){
			$encoded_params[rawurlencode($k)] = rawurlencode($k) . '=' . rawurlencode($v);
		}
		ksort($encoded_params);
		$this->_parameter_string = implode('&', $encoded_params);
		$this->_base_string = strtoupper($request->method) . '&' . rawurlencode($request->url) . '&' . rawurlencode($parameter_string);
	}
	
	private function generateOauthSignature(){
		$signing_key = rawurlencode($this->_consumer_secret) . '&';
		if(isset($this->_access_token_secret) && strlen($this->_access_token_secret) > 0){
			$signing_key .= rawurlencode($this->_access_token_secret);
		}
		$this->_signature = base64_encode(hash_hmac('sha1', $this->_base_string, $signing_key, true));
	}
	
	private function buildAuthorizationHeader(){
		// TODO: no headers for oauth 2.0 request?
		
		$r = 'Authorization: OAuth ';
		$values = array();
		foreach($this->_parameters as $key => $value){
			if(substr($k, 0, 6) == 'oauth_'){
				$values[] = $key . '="' . rawurlencode($value) . '"';
			}
		}
		$values[] = 'oauth_signature="' . rawurlencode($this->_signature) . '"';
		$r .= implode(', ', $values);
		$this->_headers = array($r, 'Expect:');
	}
	
}