<?php

namespace models;

use \database\Criteria as Criteria;

abstract class Saveable {
    
    public function create(Boolean $relations = false){
        return App::getMapperInstance()->create($this, $relations);
    }
    
    public static function read(Criteria $criteria){
        return App::getMapperInstance()->read($criteria);
    }
    
    public function update(Boolean $relations = false){
        return App::getMapperInstance()->update($this, $relations);
    }
    
    public function delete(){
        return App::getMapperInstance()->delete($this);
    }
    
    public static function criteria(Array $criteria = array()){
        $class = get_called_class();
        return new Criteria(new $class(), $criteria);
    }
    
    public function save(Boolean $relations = false){
        if(isset($this->id) && $this->id > 0)
            $this->update($relations);
        else
            $this->create($relations);
    }
    
    public static function __callStatic($method, $parameters){ // alias methods to "find"
		if(substr($method, 0, 8) == 'find_by_' && isset($parameters[0])){
			$criteria = array(substr($method, 8) => $parameters[0]);
			return self::find($criteria);
		}
	}
    
    public static function find(Criteria $criteria){
        return self::read($criteria);
    }
    
    public function populate(){
        return true;
    }
    
    public function getProperties(){
        $arr = array();
		foreach($this as $k => $v){
            $arr[substr($k, 1)] = $v;
		}
		return $arr;
    }
    
    public function __get($property){
		if(substr($property, 0, 1) != '_'){
			$property = '_' . $property;
		}
		if(property_exists($this, $property)){
			return $this->$property;
		}
	}
	
	public function __set($property, $value){
		if(substr($property, 0, 1) != '_'){
			$property = '_' . $property;
		}
		if(property_exists($this, $property)){
			$this->$property = $value;
		}
	}
	
    public function __isset($property){
		if(substr($property, 0, 1) != '_'){
			$property = '_' . $property;
		}
		if(property_exists($this, $property)){
			return true;
		}
		return false;
	}
    
}