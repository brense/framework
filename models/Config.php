<?php

namespace models;

class Config {
	
	public static $file_root;
	public static $root_url;
	public static $sources = array();
	public static $debug;
	public static $log_path;
	public static $main_view;
	public static $app_name;
	
	public static function properties(){
		$class = new \ReflectionClass('\models\Config');
		$props = $class->getProperties();
		$properties = array();
		foreach($props as $prop){
			$name = $prop->name;
			$properties[$name] = self::$$name;
		}
		return $properties;
	}
	
}