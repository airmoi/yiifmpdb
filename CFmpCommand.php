<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CFmpCommand extends CDbCommand {
    
    /**
	 * Binds a value to a parameter.
	 * @param mixed $name Parameter identifier. For a prepared statement
	 * using named placeholders, this will be a parameter name of
	 * the form :name. For a prepared statement using question mark
	 * placeholders, this will be the 1-indexed position of the parameter.
	 * @param mixed $value The value to bind to the parameter
	 * @param integer $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
	 * @return CDbCommand the current command being executed
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
	 */
	public function bindValue($name, $value, $dataType=null)
	{
            $from = $this->getFrom();
		$query = $this->getText();
		Yii::trace("bindValue $name (".$dataType===null ? gettype($value):$dataType.") query : $query",'system.db.CFmpCommand');
		/*if($dataType===null)
			$this->_statement->bindValue($name,$value,$this->_connection->getPdoType(gettype($value)));
		else
			$this->_statement->bindValue($name,$value,$dataType);
		$this->_paramLog[$name]=$value;*/
                $p = strpos($query, '?');
                        $query =  str_replace($name, $value == '' ? "''" : "'$value'", $query);
			//$this->_paramLog[$name]=$value;
                $this->reset();
                $this->setText($query);
                Yii::trace("new query : $query",'system.db.CFmpCommand');

		return $this;
	}

	/**
	 * Binds a list of values to the corresponding parameters.
	 * This is similar to {@link bindValue} except that it binds multiple values.
	 * Note that the SQL data type of each value is determined by its PHP type.
	 * @param array $values the values to be bound. This must be given in terms of an associative
	 * array with array keys being the parameter names, and array values the corresponding parameter values.
	 * For example, <code>array(':name'=>'John', ':age'=>25)</code>.
	 * @return CDbCommand the current command being executed
	 * @since 1.1.5
	 */
	public function bindValues($values)
	{
            $query = $this->getText();
            
		Yii::trace("bindValues query : $query",'system.db.CFmpCommand');
		foreach($values as $name=>$value)
		{
			$p = strpos($query, '?');
                        $query =  str_replace($name, $value == '' ? 'NULL' : "'$value'", $query);
			//$this->_paramLog[$name]=$value;
		}
                $this->reset();
                $this->setText($query);
                Yii::trace("new query : $query",'system.db.CFmpCommand');
		$this->prepare();
		return $this;
	}
}
