<?php
/**
 * CFmpColumnSchema class file.
 *
 * @author Romain Dunand <romain_pro@dunand.me>
 * @copyright 2014 YiiFmpDb
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 */

/**
 * CFmpColumnSchema class describes the column meta data of a database table.
 *
 * @author Romain Dunand <romain_pro@dunand.m>
 * @package extensions.yiifmpdb
 * @since 1.1.14
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
