<?php

class Table {

    private $table;
    private $listcolumn = array();
        
    public function setTable($table){
        $this->table = $table;
        return $this;
    }
    public function getTable(){
        return $this->table;
    }
    
    public function setListcolumn($listcolumn){
        $this->listcolumn = $listcolumn;
        return $this;
    }
    public function getListcolumn(){
        return $this->listcolumn;
    }
    
    public function addColumn($Column){
        array_push($this->listcolumn, $Column);
    }
    
    public function clear(){
        unset($this->listcolumn);
    }
}

?>
