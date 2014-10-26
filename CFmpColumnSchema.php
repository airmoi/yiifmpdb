<?php
/**
 * CFmpColumnSchema class file.
 *
 * @author Romain Dunand <airmoi@gmail.com>
 * @link ---
 * @copyright 2013-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFmpSchema is the class for retrieving metadata information from a FileMaker® ODBC database.
 *
 * @author Romain Dunand <airmoi@gmail.com>
 * @package ext.fmpdb
 */
class CFmpColumnSchema extends CDbColumnSchema
{
	/**
	 * Converts the input value to the type that this column is of.
	 * @param mixed $value input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		
		if( (  $value==='' || $value===null ) && $this->allowNull)
			return '';
		switch($this->dbType)
		{
			case 'varchar': return (string)"'$value'";
			case 'binary': return (string)"'$value'";
			case 'decimal': return $value;
			case 'time': return "{t '$value'}";
			case 'date': return "{d '$value'}";
			case 'timestamp': return "{ts '$value'}";
			default: return $value;
		}
	}
        
        /**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		$this->defaultValue='';
	}
}
