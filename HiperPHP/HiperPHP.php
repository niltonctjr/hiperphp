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
    
    static function Select($BE, $limit="25"){
        $Reflection = new ReflectionClass(get_class($BE));
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
            $Property = new ReflectionProperty(get_class($BE), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            $sqlColumn .= $column->getName().", ";
            if (!is_null($BE->$get())){
                $sqlFiltro .= $column->getName().$hiper->trataTiposColumns($column, $BE->$get(),"SELECT")." and ";
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
    
    static function Insert($BE){
        $Reflection = new ReflectionClass(get_class($BE));
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
            $Property = new ReflectionProperty(get_class($BE), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            if(!$column->getAutoIncrement()){
                $sqlColumn .= $column->getName().", ";
                if (!is_null($BE->$get())){
                    $sqlValores .= $hiper->trataTiposColumns($column, $BE->$get(), "INSERT").", ";
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
    
    static function Update($BE){
        $Reflection = new ReflectionClass(get_class($BE));
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
            $Property = new ReflectionProperty(get_class($BE), $ObjectProperties->getName());
            $column = new Column();            
            $hiper->valorizaColumn($Property->getDocComment(), $column);
            if($column->getPrimaryKey()){
                $sqlFiltro .= $column->getName().$hiper->trataTiposColumns($column, $BE->$get(),"UPDATE");
            }
            $sqlValores .= $column->getName().$hiper->trataTiposColumns($column, $BE->$get(),"UPDATE")." , ";        
            $table->addColumn($column);
            
        }

        $sql = " Update ".$table->getTable()." set ".substr($sqlValores, 0, -2)." Where ".$sqlFiltro.";";
        return $sql;
    }
}

?>
