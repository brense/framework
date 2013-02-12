<?php

class Request {
	
	protected $_method;
	protected $_schema;
	protected $_protocol;
	protected $_host;
	protected $_path;
	protected $_parameters = array();
	protected $_port;
	protected $_headers = array();
	protected $_error;
	
	protected $_use_socket = false;
	protected $_keep_alive = false;
	
	public function __construct($method, $url, Array $parameters = array()){
		$arr = parse_url($url);
		
		if(isset($arr['protocol']) && isset($arr['host']) && isset($arr['path'])){
			$this->_method = strtoupper($method);
			$this->_schema = '?';
			$this->_protocol = $arr['protocol'];
			$this->_host = $arr['host'];
			$this->_path = $arr['path'];
			$this->_parameters = $parameters;
		} else {
			throw new \Exception('url is not valid');
		}
	}
	
	public function execute(){
		// TOOD: test internet connection
		
		// TODO: implement caching
		
		// open a socket connection
		if($this->_use_socket){
			$fp = $this->socketConnect();
			if(!$this->_keep_alive){
				// TODO: read contents of stream
				fclose($fp);
			} else {
				return $fp;
			}
		}
		// make a curl request
		else {
			$response = $this->curlRequest();
			if($respose){
				return $response;
			} else {
				return $this->_error;
			}
		}
	}
	
	private function socketConnect(){
		$fp = '';
		return $fp;
	}
	
	private function curlRequest(){
		// init curl
		$ch = curl_init();
		
		// set curl options based on request method
		switch($this->_method){
			case 'GET':
				
				break;
			case 'POST':
				
				break;
			case 'PUT':
				
				break;
			case 'DELETE':
				
				break;
		}
		
		$this->_response = curl_exec($ch);
		$this->_info = curl_getinfo($ch);
		
		unset($ch);
		
		if(isset($fh) && isset($file)){
			fclose($fh);
			unlink($file);
		}
	}
	
	public function __callStatic($method, $parameters){
		$req = new self($method, $parameters[0], $parameters[1]);
		return $req->execute();
	}
	
}