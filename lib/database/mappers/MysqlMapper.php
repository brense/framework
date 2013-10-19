<?php

namespace database\mappers;

use \models\Saveable as Saveable;
use \database\Criteria as Criteria;
use \interfaces\iMapper as iMapper;

class MysqlMapper implements iMapper {
    
    private static $_instance;
    private $_db;
    
    private function __construct(){
        $this->_db = App::getDatabase();
    }
    
    public static function instance(){
        if(empty(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }
    
    // creates a single model, returns the new models id on success and false on failure
    public function create(Saveable &$model, Boolean $relations = false){
        $this->_db->setTable(str_replace('\models\\', '', get_class($model)));
        return $this->_db->create($this->toArray($model), $relations);
    }
    
    // returns an array of models based on the given criteria
    public function read(Criteria $criteria, Boolean $populate = false){
        $this->_db->setTable(str_replace('\models\\', '', get_class($criteria->model)));
        
        // determine joins
        foreach($criteria->model->getProperties() as $property => $value){
            if($criteria->model->$property instanceof Saveable)
                $criteria->join($property, str_replace('\models\\', '', get_class($criteria->model->$property)));
        }
        
        $results = $this->_db->read($criteria->where, $criteria->columns, $criteria->joins, $criteria->sort, $criteria->limit);
        
        $models = array();
        foreach($results as $result){
            $model = $this->toObj($result, $criteria->model);
            if($populate)
                $model->populate();
            $models[] = $model;
        }
        return $models;
    }
    
    // updates a single model, returns true on success and false on failure
    public function update(Saveable $model, Boolean $relations = false){
        $this->_db->setTable(str_replace('\models\\', '', get_class($model)));
        return $this->_db->update($this->toArray($model), $relations), array('id' => $model->id));
    }
    
    // deletes a single model, returns true on success and false on failure
    public function delete(Saveable $model){
        $this->_db->setTable(str_replace('\models\\', '', get_class($model)));
        return $this->_db->delete(array('id' => $model->id));
    }
    
    public function __call($method, Array $properties = array()){
        if($method == 'map' && isset($properties[0]) && $properties[0] instanceof Saveable)
            return $this->toArray($properties[0]);
        else if($method == 'map' && isset($properties[1]) && $properties[1] instanceof Saveable)
            return $this->toObj($properties[0], $properties[1]);
    }
    
    public function toArray(Saveable $model, Boolean $relations = false){
        $properties = $model->getProperties();
        foreach($properties as $k => &$prop){
            if($prop instanceof Saveable){
                $prop = $prop->id;
                if($relations){
                    $prop->save(false);
                }
            } else if(is_array($prop) && isset($prop[0]) && $prop[0] instanceof Saveable){
                unset($properties[$k]);
                if($relations){
                    foreach($prop as $model){
                        $prop->save(false);
                    }
                }
            } else if(is_array($prop)){
                $prop = json_encode($prop);
            }
        }
        return $properties;
    }
    
    public function toObj(Array $arr, Saveable $obj){
        $joins = array();
        foreach($arr as $k => $v){
            if(property_exists($obj, $k)){
                if(is_array($obj->$k) || is_object($obj->$k))
                    $v = json_decode($v);
                $obj->$k = $v;
            } else if(strpos($k, '.') !== false){
                $j = explode('.', $k);
                if(!isset($obj->$j[0])){
                    $class = $j[1];
                    $obj->$j[0] = new $class();
                    $obj->$j[0]->$class->$j[2] = $v;
                } else {
                    $obj->$j[0]->$class->$j[2] = $v;
                }
            }
        }
        return $obj;
    }
    
}