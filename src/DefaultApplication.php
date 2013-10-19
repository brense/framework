<?php

class DefaultApplication {
    
    protected $_fileRoot;
    protected $_rootUrl;
    protected $_sources = array();
    protected $_routes = array();
    protected $_database;
    
    public function __construct(Array $options = array()){
        // save options
		foreach($options as $k => $v){
			if(property_exists($this, '_' . $k)){
				$this->{'_' . $k} = $v;
			}
		}
        
        // set application sources
        $includes = get_included_files();
        if(isset($includes[0], $includes[1]) && !isset($this->_sources['app'], $this->_sources['framework'])){
            $this->_sources['app'] = preg_replace('/[^\\' . DIRECTORY_SEPARATOR . ']*$/', '', $includes[0]);
            $this->_sources['app_lib'] = $this->_sources['app'] . 'lib' . DIRECTORY_SEPARATOR;
            $this->_sources['framework'] = preg_replace('/[^\\' . DIRECTORY_SEPARATOR . ']*$/', '', $includes[1]);
            $this->_sources['framework_lib'] = $this->_sources['framework'] . 'lib' . DIRECTORY_SEPARATOR;
        }
        
        // set the application rootUrl
        if(!isset($options['rootUrl'])){
            $docRoot = str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
            $parts1 = explode(DIRECTORY_SEPARATOR, str_replace($docRoot, '', $this->_sources['app']));
            $parts2 = explode(DIRECTORY_SEPARATOR, str_replace($docRoot, '', $this->_sources['framework']));
            
            $this->_fileRoot = $docRoot;
            $this->_rootUrl = 'http://' . $_SERVER['HTTP_HOST'];
            for($n = 0; $n < count($parts1); $n++){
                if(isset($parts2[$n]) && $parts1[$n] == $parts2[$n]){
                    $this->_rootUrl .= $parts1[$n] . '/';
                    $this->_fileRoot .= $parts1[$n] . DIRECTORY_SEPARATOR;
                }
                else
                    break;
            }
        }
        
		// set the exception handler
		if(!isset($options['exceptionHandler'])){
			set_exception_handler(array($this, 'exceptionHandler'));
		} else {
			set_exception_handler($options['exceptionHandler']);
		}
		
		// set the error handler
		if(!isset($options['errorHandler'])){
			set_error_handler(array($this, 'errorHandler'));
		} else {
			set_error_handler($options['errorHandler']);
		}
		
		// start a session
		if(!isset($options['startSession']) || $options['startSession'] === true){
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
        
        App::$_instance = $this;
    }
    
    public function run(){
        $this->resolveRoute();
    }
    
    public function addRoute($method, $path, $callback){
        $this->_routes[$method][$path] = $callback;
    }
    
    protected function get($property){
		if(property_exists($this, '_' . $property)){
			return $this->{'_' . $property};
		}
	}
    
    public static function logWrite($message, $file, $line, $trace){
		// TODO: implement
	}
    
    public function exceptionHandler(Exception $exception) {
		if(Config::$debug){
			echo '<pre>';print_r($exception);echo '</pre>';
		} else {
			echo 'something went wrong';
			self::logWrite(
                'Exception: ' . $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );
		}
		exit;
	}
	
	public function errorHandler($errno, $errstr, $error_file = null, $error_line = null, Array $error_context = null) {
		if(Config::$debug){
			$error = array(
                'no' => $errno,
                'error' => $errstr,
                'file' => $error_file,
                'line' => $error_line,
                'context' => $error_context
            );
			echo '<pre>';print_r($error);echo '</pre>';
		} else {
			self::logWrite(
                'Error: no: ' . $errno . ', error: ' . $errstr,
                $error_file,
                $error_line,
                json_encode($error_context)
            );
		}
	}
	
	private function autoload($class){
		// create a clean class path
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		// loop through class sources
		$found = false;
		foreach($this->sources as $source){
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
		$requestUri = str_replace($this->_rootUrl, '', 'http://' . $_SERVER['HTTP_HOST'] . $requestUri);
		
		$uri = explode('/', $requestUri);
		for($i = 0; $i < count($uri); $i++){
			if(strlen($uri[$i]) == 0){
				unset($uri[$i]);
			}
		}
		$selected = array();
		foreach($this->_routes[$_SERVER['REQUEST_METHOD']] as $route => $callback){
			$arr = explode('/', $route);
			$n = 0;
			foreach($arr as $value){
				// determine if the route part matches the requested url part
				if(substr($value, 0, 1) == ':' || (isset($uri[$n]) && $uri[$n] == $value)){
					$selected['callback'] = $callback;
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
			// break the loop if a suitable callback has been found
			if(isset($selected['callback'])){
				break;
			}
		}
		call_user_func_array($selected['callback'], $selected['parameters']);
	}
    
}