<?php

require_once('models' . DIRECTORY_SEPARATOR . 'Config.php');

use \models\Config as Config;

class Application {
	
	private $_routes = array();
	private $_methods = array('get', 'post', 'put');
	
	public function __construct(Array $options = array()){
		// set default file_root, root_url and source path
		$bootstrap = @array_pop(@explode('/', $_SERVER['SCRIPT_NAME']));
		if(!isset($options['file_root'])){
			$options['file_root'] = str_replace('/', DIRECTORY_SEPARATOR, str_replace($bootstrap, '', $_SERVER['SCRIPT_FILENAME']));
		}
		if(!isset($options['root_url'])){
			$options['root_url'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace($bootstrap, '', $_SERVER['SCRIPT_NAME']);
		}
		$src_path = str_replace('Application.php', '', __FILE__);
		
		// save options
		foreach($options as $k => $v){
			if(property_exists('\models\Config', $k)){
				Config::$$k = $v;
			}
		}
		
		// set the exception handler
		if(!isset($options['exception_handler'])){
			set_exception_handler(array($this, 'exceptionHandler'));
		} else {
			set_exception_handler($options['exception_handler']);
		}
		
		// set the error handler
		if(!isset($options['error_handler'])){
			set_error_handler(array($this, 'errorHandler'));
		} else {
			set_error_handler($options['error_handler']);
		}
		
		// set default class sources
		if(count(Config::$sources) == 0){
			Config::$sources = array(
				'src' => $src_path,
				'lib' => $src_path . 'lib' . DIRECTORY_SEPARATOR,
				'custom' => Config::$file_root . 'custom' . DIRECTORY_SEPARATOR
			);
		}
		
		// start a session
		if(!isset($options['start_session']) || $options['start_session'] === true){
			session_start();
		}
		
		// set the autoloader
		if(!isset($options['autoloader'])){
			spl_autoload_register(array($this, 'autoload'));
		} else if(isset($options['autoloader']['class'], $options['autoloader']['function'])){
			spl_autoload_register(array($options['autoloader']['class'], $options['autoloader']['function']));
		} else if(is_callable($options['autoloader'])){
			spl_autoload_register($options['autoloader']);
		} else {
			throw new Exception('invalid autoloader');
		}
	}
	
	public function addRoute($method, $route, $callback){
		if((in_array(strtolower($method), $this->_methods) || strtolower($method) == 'all') && is_callable($callback)){
			if(strtolower($method) == 'all'){
				$methods = $this->_methods;
			} else {
				$methods = array($method);
			}
			foreach($methods as $method){
				$this->_routes[strtoupper($method)][] = array('route' => $route, 'callback' => $callback);
			}
		} else {
			throw new Exception('invalid route');
		}
	}
	
	public function start(){
		$this->resolveRoute();
	}
	
	public static function init(){
		echo json_encode(array('test' => 'bla'));
	}
	
	public static function logWrite($message, $file, $line, $trace){
		if(!file_exists(Config::$log_path)){
			mkdir(Config::$log_path);
		}
		$handle = fopen(Config::$log_path . date('Y-m-d') . '.log', 'a');
		fwrite($handle, time() . ';' . $message . ';' . str_replace("\n", '', $trace) . ';' . $file . '[' . $line . ']' . "\n");
		fclose($handle);
	}
	
	public function exceptionHandler(Exception $exception) {
		if(Config::$debug){
			echo '<pre>';print_r($exception);echo '</pre>';
		} else {
			echo 'something went wrong';
			self::logWrite('Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
		}
		exit;
	}
	
	public function errorHandler($errno, $errstr, $error_file = null, $error_line = null, Array $error_context = null) {
		if(Config::$debug){
			$error = array('no' => $errno, 'error' => $errstr, 'file' => $error_file, 'line' => $error_line, 'context' => $error_context);
			echo '<pre>';print_r($error);echo '</pre>';
		} else {
			self::logWrite('Error: no: ' . $errno . ', error: ' . $errstr, $error_file, $error_line, json_encode($error_context));
		}
	}
	
	private function autoload($class){
		// create a clean class path
		$path = str_replace('\\', '/', $class);
		// loop through class sources
		$found = false;
		foreach(Config::$sources as $source){
			if(file_exists($source . $path . '.php')){
				require_once($source . $path . '.php');
				spl_autoload($class);
				$found = true;
				break;
			}
		}
		// throw exception if class cannot be found
		if(!$found){
			throw new Exception('class ' . $class . ' not found');
		}
	}
	
	private function resolveRoute(){
		$requestUri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		$requestUri = str_replace(Config::$root_url, '', 'http://' . $_SERVER['HTTP_HOST'] . $requestUri);
		
		$uri = explode('/', $requestUri);
		for($i = 0; $i < count($uri); $i++){
			if(strlen($uri[$i]) == 0){
				unset($uri[$i]);
			}
		}
		$selected = array();
		foreach($this->_routes[$_SERVER['REQUEST_METHOD']] as $route){
			$arr = explode('/', $route['route']);
			$n = 0;
			foreach($arr as $value){
				// determine if the route part matches the requested url part
				if(substr($value, 0, 1) == ':' || (isset($uri[$n]) && $uri[$n] == $value)){
					$selected['callback'] = $route['callback'];
					if(substr($value, 0, 1) == ':'){
						if(isset($uri[$n])){
							$selected['parameters'][substr($value, 1)] = $uri[$n];
						} else {
							$selected['parameters'][substr($value, 1)] = implode($uri);
						}
					}
				} else {
					$selected = array();
					break;
				}
				// handle remaining parts of the requested uri
				if(!isset($arr[$n+1]) && isset($selected['callback']) && isset($uri[$n+1])){
					if(isset($selected['parameters'][substr($value, 1)])){
						for($i = $n+1; $i < count($uri); $i++){
							$selected['parameters'][substr($value, 1)] .= '/' . $uri[$i];
						}
					}
					break 2;
				}
				$n++;
			}
			// break the loop is a suitable callback has been found
			if(isset($selected['callback'])){
				break;
			}
		}
		call_user_func_array($selected['callback'], $selected['parameters']);
	}
	
}