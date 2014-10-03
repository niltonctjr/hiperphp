<?php

class Relationship {
    
    private $table;
    private $column;
    
    public function setTable($table){
        $this->table = $table;
        return $this;
    }
    public function getTable(){
        return $this->table;
    }
    
    public function setColumn($column){
        $this->column = $column;
        return $this;
    }
    public function getColumn(){
        return $this->column;
    }
    
    
}

?>
