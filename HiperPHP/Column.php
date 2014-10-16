<?php

class Column {

    private $name;
    private $type;
    private $notNull;
    private $length;
    private $primaryKey;
    private $autoIncrement;
    private $relationship;
    private $like;
    private $shiftDecimal;
    private $methodGetEntity;
    private $methodSetEntity;
 
    public function setName($name){
        $this->name = $name;
        return $this;
    }
    public function getName(){
        return $this->name;
    }

    
    public function setType($type){
        $this->type = $type;
        return $this;
    }
    public function getType(){
        return $this->type;
    }
    
    public function setNotNull($notNull){
        $this->notNull = $notNull;
        return $this;
    }
    public function getNotNull(){
        return $this->notNull;
    }
    
    public function setLength($length){
        $this->length = $length;
        return $this;
    }
    public function getLength(){
        return $this->length;
    }
    
    public function setPrimaryKey($primaryKey){
        $this->primaryKey = $primaryKey;
        return $this;
    }
    public function getPrimaryKey(){
        return $this->primaryKey;
    }
    
    public function setAutoIncrement($autoIncrement){
        $this->autoIncrement = $autoIncrement;
        return $this;
    }
    public function getAutoIncrement(){
        return $this->autoIncrement;
    }
    
    public function setRelationship($relationship){
        $this->relationship = $relationship;
        return $this;
    }
    public function getRelationship(){
        return $this->relationship;
    }
    public function setLike($like){
        $this->like = $like;
        return $this;
    }
    public function getLike(){
        return $this->like;
    }
    public function setShiftDecimal($shiftDecimal){
        $this->shiftDecimal = $shiftDecimal;
        return $this;
    }
    public function getShiftDecimal(){
        return $this->shiftDecimal;
    }
    
    public function setMethodGetEntity($methodGetEntity){
        $this->methodGetEntity = $methodGetEntity;
        return $this;
    }
    public function getMethodGetEntity(){
        return $this->methodGetEntity;
    }
    public function setMethodSetEntity($methodSetEntity){
        $this->methodSetEntity = $methodSetEntity;
        return $this;
    }
    public function getMethodSetEntity(){
        return $this->methodSetEntity;
    }
}

?>
