<?php

namespace database;

use \interfaces\iDatabase as iDatabase;

class MysqlDatabase implements iDatabase {
    
    private $_handle;
    private $_table;
    
    public function __construct(){
        $dsn = 'mysql:host=' . App::config()->database->host . ';dbname=' . App::config()->database->name;
        $this->_handle = new \PDO($dsn, App::config()->database->user, App::config()->database->pswd);
    }
    
    public function query($query, Array $params = array(), $return = null){
        return $this->execute($query, $params, $return);
    }
    
    public function create(Array $values){
        if(count($values) > 0){
			$params = array();		
			$cols = array();
			foreach($values as $key => $value){
				$params[':' . $key] = $value;
				$cols[] = '`' . $key . '`';
				$vals[] = ':' . $key;
			}
			$query = 'INSERT INTO `' . $this->_table . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
			return $this->query($query, $params, 'lastInsertId');
		} else {
			return false;
		}
    }
    
    public function read(Array $criteria = array(), Array $columns = array(), Array $joins = array(), Array $sorting = array(), Array $limiting = array()){
        if(count($columns) == 0)
			$select = '*';
		else
			$select = '`' . implode('`, `', $columns) . '`';
		
		$params = array();
		if(count($criteria) == 0){
			$where = '';
		} else {
			foreach($criteria as $key => $value){
				$params[':' . $key] = $value;
				$crits[] = '`' . $key . '` = :' . $key;
			}
			$where = ' WHERE ' . implode(' AND ', $crits);
		}
        
        // TODO: rewrite this code that was copied from mysqlmapper
        if(count($joins) > 0){
            $joins = array();
            $columns = $criteria->$columns;
            foreach($criteria->joins as $column => $table){
                $columns[] = $table . '.*';
                $joins[] = 'JOIN ' . $table . ' ON ' . $this->_db->getTable . '.' . $column . ' = ' . $table . '.id';
            }
            
            $where = '';
            if(count($criteria->where) > 0){
                $w = array();
                foreach($criteria->where as $column => $value){
                    $w[] = '`' . $column . '` = "' . $value . '"';
                }
                $where = 'WHERE ' . implode(' AND ', $w);
            }
            
            $sortBy = '';
            if(count($criteria->sortBy) > 0){
                // TODO
            }
            
            $limit = '';
            if(count($criteria->limit) > 0){
                // TODO
            }
            
            $results = $this->_db->query(
                'SELECT ' . implode(', ' , $columns) .
                ' FROM ' . $this->_db->getTable .
                implode(' ', $joins) .
                $where . $orderBy . $limit
            );
        }
		
        $sort = '';
        if(count($sorting) > 0){
            $arr = array();
            foreach($sorting as $s){
                $arr[] = $s[0] . ' ' . strtoupper($s[1]);
            }
            $sort = ' ORDER BY ' . implode(', ', $arr);
        }
		
        $limit = '';
		if(count($limiting) == 2){
            $limit = ' LIMIT ' . $limiting[0] . ', ' . $limiting[1];
        }
        
		$query = 'SELECT ' . $select . ' FROM `' . $this->_table . '`' . $where . $sort . $limit;
		return $this->query($query, $params, 'fetchAll');
    }
    
    public function update(Array $values, Array $criteria){
        if(count($values) > 0){
			$params = array();
			if(count($criteria) == 0){
				$where = '';
			} else {
				foreach($criteria as $key => $value){
					$params[':' . $key] = $value;
					$crits[] = '`' . $key . '` = :' . $key;
				}
				$where = ' WHERE ' . implode(' AND ', $crits);
			}
			$cols = array();
			foreach($values as $key => $value){
				$params[':' . $key] = $value;
				$cols[] = '`' . $key . '` = :' . $key;
			}
			$query = 'UPDATE `' . $this->_table . '` SET ' . implode(', ', $cols) . $where;
			return $this->query($query, $params);
		} else {
			return false;
		}
    }
    
    public function delete(Array $criteria){
        $params = array();
		if(count($criteria) == 0){
			$where = '';
		} else {
			foreach($criteria as $key => $value){
				$params[':' . $key] = $value;
				$crits[] = '`' . $key . '` = :' . $key;
			}
			$where = ' WHERE ' . implode(' AND ', $crits);
		}
		$query = 'DELETE FROM `' . $this->_table . '`' . $where;
		return $this->query($query, $params);
    }
    
    private function execute($query, Array $params = array(), $return = null){
        $statement = $this->_handle->prepare($query);
		$statement->execute($params);
		switch($return){
            case 'fetchClass':
                return $statement->fetchAll(\PDO::FETCH_CLASS, $this->_table);
			case 'fetchAll':
				return $statement->fetchAll(\PDO::FETCH_ASSOC);
				break;
			case 'lastInsertId':
				return $this->_handle->lastInsertId();
				break;
			default:
				return true;
				break;
		}
    }
    
    public function setTable($table){
        $this->_table = $table;
    }
    
    public function getTable(){
        return $this->_table;
    }
    
}