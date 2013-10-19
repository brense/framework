<?php

namespace database;

use \models\Saveable as Saveable;

class Criteria {
    
    private $_model;
    private $_limit;
    private $_sort = array();
    private $_where = array();
    private $_columns = array();
    private $_joins = array();
    
    public function __construct(Saveable $model, Array $where = array()){
        $this->_model = $model;
        $this->_where = $where;
    }
    
    public function limit($start, $limit){
        $this->_limit = array($start, $limit);
        
        return $this;
    }
    
    public function setColumns(Array $columns = array()){
        $this->_columns = $columns;
        
        return $this;
    }
    
    private function sortBy(Array $sortBy){
        $this->_sort[] = $sortBy;
    }
    
    public function join($property, $table){
        $this->_joins[$property] = $table;
        
        return $this;
    }
    
    public function __call($method, $properties){
        if(substr($method, 0, 7) == 'sort_by')
            $this->sortBy(explode('_', substr($method, 7)));
        
        return $this;
    }
    
    public function __get($property){
		if(substr($property, 0, 1) != '_'){
			$property = '_' . $property;
		}
		if(property_exists($this, $property)){
			return $this->$property;
		}
	}
    
}