<?php

namespace interfaces;

interface iDatabase {
    
    public function query($query, Array $params = array(), $return = null);
    
    public function create(Array $values);
    
    public function read(Array $criteria = array(), Array $columns = array(), $sort = null, $limit = null);
    
    public function update(Array $values, Array $criteria);
    
    public function delete(Array $criteria);
    
    private function execute($query, Array $params = array(), $return = null);

}