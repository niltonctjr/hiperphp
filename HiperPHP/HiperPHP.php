<?php


require_once 'HiperPHP/Table.php';
require_once 'HiperPHP/Column.php';

class HiperPHP {
    
    protected function valorizaObj($metodoValor, &$Obj){
        $pattern = '/[a-zA-Z0-9]+[ ]{0,1}+';
        $pattern .= '|=[ ]{0,1}.+/';
        $arrMetodoValor = array();
        
        preg_match_all($pattern, $metodoValor, $arrMetodoValor);
        
        //echo '<pre>'.print_r($arrMetodoValor).'</pre>';
        
        $metodo = 'set'.str_replace(" ", "", str_replace("@", "", $arrMetodoValor[0][0]));        
        $valor = str_replace(" ", "", str_replace("=", "", str_replace(",", "", str_replace(")", "", $arrMetodoValor[0][1]))));        
        //echo $metodo." ";  
        //echo $valor." ";  
        $Obj->$metodo($valor);
    }
    
    protected function valorizaColumn($metodosValores, &$column){
        $pattern = '/[ ]{0,1}+[a-zA-Z0-9]+[ ]{0,1}+=[ ]{0,1}[^=]+[,]+';
        $pattern .= '|[ ]{0,1}+[a-zA-Z0-9]+[ ]{0,1}+=[ ]{0,1}[^=]+[)]+/';
        $arrMetodoValor = array();
        
        preg_match_all($pattern, $metodosValores, $arrMetodoValor);
        //echo '<pre>'.print_r($arrMetodoValor).'</pre>';
        foreach ($arrMetodoValor[0] as $metodoValor) {  
            $this->valorizaObj($metodoValor, $column);
        }
    }     
    
    protected function setBindParamColumn(&$stmt, $index, $column, $value, $action)
    {        
        if ($column->getLike() && strtoupper($action) == "SELECT" )
        {            
            $value = "%$value%";
        }
        switch (strtoupper($column->getType())) {
            case "DATE":
                $stmt->bindParam($index, $value, PDO::PARAM_STR);
                return;
            case "TIME":
                $stmt->bindParam($index, $value, PDO::PARAM_STR);
                return;
            case "DATETIME":
                $stmt->bindParam($index, $value, PDO::PARAM_STR);
                return;
            case "STRING":                                
                $stmt->bindParam($index, $value, PDO::PARAM_STR);
                return;
            case "INT":
                $stmt->bindParam($index, $value, PDO::PARAM_INT);
                return;
            case "NUMERIC":
                $stmt->bindParam($index, $value, PDO::PARAM_STR);
                return;
            default :
                $stmt->bindParam($index, $value);
                return;
        }
    }    

    

    protected function trataTiposColumns($column, $value, $acao){
        
        if ($column->getLike() && strtoupper($acao) == "SELECT" ){
            return " like '%$value%'";
        }
        $sinal = "";
        if (strtoupper($acao) == "SELECT"  || strtoupper($acao) == "UPDATE"){
            $sinal = " = ";
        }
        switch (strtoupper($column->getType())) {
            case "DATE":
                return $sinal."'$value'";
                break;
            case "TIME":
                return $sinal."'$value'";
                break;
            case "DATETIME":
                return $sinal."'$value'";
                break;
            case "STRING":
                return $sinal."'$value'";
                break;
            case "INT":
                return $sinal.$value;
                break;
            case "NUMERIC":
                if ($column->getShiftDecimal()){
                    $value = str_replace(",", ".", $value);
                }                
                return $sinal.$value;
                break;
            default :
                return $sinal.$value;
                break;
        }
    }
    
    static function Select($entity, $limit="25"){
        $Reflection = new ReflectionClass(get_class($entity));
        $arrayObjectProperties = $Reflection->getProperties(ReflectionProperty::IS_PRIVATE);    
        //echo '<pre>'.print_r($arrayObjectProperties).'</pre>';
        $hiper = new HiperPHP();
        $table = new Table();    
        
        $sqlColumn= null;        
        $sqlFiltro = null;
        
        $hiper->valorizaObj($Reflection->getDocComment(), $table);                
        //echo $table->getTable();
        //return;
        foreach ($arrayObjectProperties as $ObjectProperties) {
            $get = "get".$ObjectProperties->getName();
            $Property = new ReflectionProperty(get_class($entity), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            $sqlColumn .= $column->getName().", ";
            if (!is_null($entity->$get())){
                $sqlFiltro .= $column->getName().$hiper->trataTiposColumns($column, $entity->$get(),"SELECT")." and ";
            }
            $table->addColumn($column);
            
        }
        if (!is_null($sqlFiltro)){
            $sqlFiltro = " where ".substr($sqlFiltro, 0, -4);
        }
        if (!is_null($limit)){
            $limit = " limit ".$limit;
        }
        //print_r($table->getListcolumn());
        $sql = " select ".substr($sqlColumn, 0, -2)." from ".$table->getTable().$sqlFiltro.$limit.";";
        return $sql;
    }
    
    static function Insert($entity){
        $Reflection = new ReflectionClass(get_class($entity));
        $arrayObjectProperties = $Reflection->getProperties(ReflectionProperty::IS_PRIVATE);    
        //echo '<pre>'.print_r($arrayObjectProperties).'</pre>';
        $hiper = new HiperPHP();
        $table = new Table();    
        
        $sqlColumn= null;        
        $sqlValores = null;
        
        $hiper->valorizaObj($Reflection->getDocComment(), $table);                
        //echo $table->getTable();
        //return;
        foreach ($arrayObjectProperties as $ObjectProperties) {
            $get = "get".$ObjectProperties->getName();
            $Property = new ReflectionProperty(get_class($entity), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            if(!$column->getAutoIncrement()){
                $sqlColumn .= $column->getName().", ";
                if (!is_null($entity->$get())){
                    $sqlValores .= $hiper->trataTiposColumns($column, $entity->$get(), "INSERT").", ";
                }else{
                    $sqlValores .= "null ,";
                }            
            }
            $table->addColumn($column);
            
        }
       
        //print_r($table->getListcolumn());
        $sql = " Insert Into ".$table->getTable()." ( ".substr($sqlColumn, 0, -2)." ) Values ( ".substr($sqlValores, 0, -2)." );";
        return $sql;
    }
    
    static function Update($entity){
        $Reflection = new ReflectionClass(get_class($entity));
        $arrayObjectProperties = $Reflection->getProperties(ReflectionProperty::IS_PRIVATE);    
        //echo '<pre>'.print_r($arrayObjectProperties).'</pre>';
        $hiper = new HiperPHP();
        $table = new Table();    
        
        $sqlValores = null;
        $sqlFiltro = null;
        
        $hiper->valorizaObj($Reflection->getDocComment(), $table);              
        //echo $table->getTable();
        //return;
        foreach ($arrayObjectProperties as $ObjectProperties) {
            $get = "get".$ObjectProperties->getName();
            $Property = new ReflectionProperty(get_class($entity), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            if($column->getPrimaryKey()){
                $sqlFiltro .= $column->getName().$hiper->trataTiposColumns($column, $entity->$get(),"UPDATE");
            }
            $sqlValores .= $column->getName().$hiper->trataTiposColumns($column, $entity->$get(),"UPDATE")." , ";        
            $table->addColumn($column);
            
        }

        $sql = " Update ".$table->getTable()." set ".substr($sqlValores, 0, -2)." Where ".$sqlFiltro.";";
        return $sql;
    }
    
    protected function setTable($entity){
        $Reflection = new ReflectionClass(get_class($entity));
        $arrayObjectProperties = $Reflection->getProperties(ReflectionProperty::IS_PRIVATE); 
               
        $table = new Table();
        
        $this->valorizaObj($Reflection->getDocComment(), $table);
        
        foreach ($arrayObjectProperties as $ObjectProperties) {
            $get = "get".$ObjectProperties->getName();
            $set = "set".$ObjectProperties->getName();
            $Property = new ReflectionProperty(get_class($entity), $ObjectProperties->getName());
            $column = new Column(); 
            $this->valorizaColumn($Property->getDocComment(), $column);
            $column->setMethodGetEntity($get);
            $column->setMethodSetEntity($set);
            $table->addColumn($column);
        }
        return $table;
    }
    
    static function SelectNew($db, $entity, $limit=25){
        try{
            $typeEntity = get_class($entity);
            
            $hiper = new HiperPHP();            
            $table = new Table();            
            $stmt = new PDOStatement();
            $sColumn= null;        
            $sWhere = null;
            $slimit = null;
            
            $table = $hiper->setTable($entity);

            foreach ($table->getListcolumn() as $column) 
            {
                $sColumn.= $column->getName().", ";
                $get = $column->getMethodGetEntity();
                
                if(!is_null($entity->$get()))
                {                    
                   if ($column->getLike())
                   {
                       $sWhere .= $column->getName()." like ? and ";                       
                   }
                   else
                   {
                       $sWhere .= $column->getName()." = ? and ";
                   }                   
                }                             
            }          
            $sColumn = substr($sColumn, 0, -2);
            if(!is_null($sWhere)){
                 $sWhere = " where ".substr($sWhere, 0, -4);
            }
            if (!is_null($limit)){
                $slimit = " limit ".$limit;
            }
            
            $stmt = $db->prepare("select ".$sColumn." from ".$table->getTable().$sWhere.$slimit);
            //return $stmt;
            $index = 1;
            foreach ($table->getListcolumn() as $column) {
                $get = $column->getMethodGetEntity();
                if(!is_null($entity->$get())){                    
                   $hiper->setBindParamColumn($stmt, $index, $column, $entity->$get(), "SELECT");                   
                   $index = $index + 1;
                }
            }
            $stmt->execute();
            $arrayEntity = array();
            $entity = new $typeEntity();
            while($result = $stmt->fetch( PDO::FETCH_ASSOC )){                 
                foreach ($table->getListcolumn() as $column) {   
                    $set = $column->getMethodSetEntity();
                    $entity->$set($result[$column->getName()]);                    
                }
                if($limit > 1)
                {
                    array_push($arrayEntity, $entity);             
                    $entity = new $typeEntity();
                }
            }            
            if($limit > 1)
            {
                return $arrayEntity;
            }
            return $entity;
       
            
       //$stmt->queryString = "select ".$sColumn." from ".$table->getTable() ;
       }
       catch (PDOException $e)
       {
            return $e->getMessage();
       }
       
    }
    
}

?>
