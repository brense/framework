<?php

namespace interfaces;

use \models\Saveable as Saveable;
use \database\Criteria as Criteria;

interface iDatabase {
    
    public function create(Saveable &$model);
    
    public function read(Criteria $criteria);
    
    public function update(Saveable $model);
    
    public function delete(Saveable $model);

}