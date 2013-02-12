<?php

namespace models;

class AbstractModel {
	
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