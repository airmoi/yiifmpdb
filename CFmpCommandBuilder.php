<?php
/**
 * CFmpCommandBuilder class file.
 *
 * @author Romain Dunand <romain_pro@dunand.me>
 * @copyright 2014 YiiFmpDb
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 */

/**
 * CFmpCommandBuilder provides basic methods to create query commands for tables.
 * 
 * @property string $query
 * @property CDbTableSchema $table
 * 
 * @author Romain Dunand <romain_pro@dunand.m>
 * @package extensions.yiifmpdb
 * @since 1.1.14
 */
class CFmpCommandBuilder extends CDbCommandBuilder
{
    private $query;
    private $table;
	/**
	 * Generates the expression for selecting rows with specified composite key values.
	 * This method is overridden because SQLite does not support the default
	 * IN expression with composite columns.
	 * @param CDbTableSchema $table the table schema
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot)
	 * @return string the expression for selection
	 */
	protected function createCompositeInCondition($table,$values,$prefix)
	{
		$keyNames=array();
		foreach(array_keys($values[0]) as $name)
			$keyNames[]=$prefix.$table->columns[$name]->rawName;
		$vs=array();
		foreach($values as $value)
			$vs[]=implode("||','||",$value);
		return implode("||','||",$keyNames).' IN ('.implode(', ',$vs).')';
	}

	/**
	 * Creates a multiple INSERT command.
	 * This method could be used to achieve better performance during insertion of the large
	 * amount of data into the database tables.
	 * Note that SQLite does not keep original order of the inserted rows.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array[] $data list data to be inserted, each value should be an array in format (column name=>column value).
	 * If a key is not a valid column name, the corresponding value will be ignored.
	 * @return CDbCommand multiple insert command
	 * @since 1.1.14
	 */
	public function createMultipleInsertCommand($table,array $data)
	{
		$templates=array(
			'main'=>'INSERT INTO {{tableName}} ({{columnInsertNames}}) {{rowInsertValues}}',
			'columnInsertValue'=>'{{value}} AS {{column}}',
			'columnInsertValueGlue'=>', ',
			'rowInsertValue'=>'SELECT {{columnInsertValues}}',
			'rowInsertValueGlue'=>' UNION ',
			'columnInsertNameGlue'=>', ',
		);
		return $this->composeMultipleInsertCommand($table,$data,$templates);
	}
        
        /**
	 * Alters the SQL to apply LIMIT and OFFSET.
	 * As LIMIT and OFFSET are not supported, this is disactivated
	 * @param string $sql SQL query string without LIMIT and OFFSET.
	 * @param integer $limit maximum number of rows, -1 to ignore limit.
	 * @param integer $offset row offset, -1 to ignore offset.
	 * @return string SQL without LIMIT and OFFSET
	 */
	public function applyLimit($sql,$limit,$offset)
	{
		
		if($offset>0)
			$sql.=' OFFSET '.(int)$offset . ' ROWS';
                
                if($limit>=0)
			$sql.=' FETCH FIRST  '.(int)$limit . ' ROWS ONLY';
		return $sql;
	}
        
        /**
	 * Returns the last insertion ID for the specified table.
         * Warning, this may not be the real last inserted ID
         * 
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @return mixed last insertion id. Null is returned if no sequence name.
	 */
	public function getLastInsertID($table)
	{
		$this->ensureTable($table);
		if($table->sequenceName!==null) {
			$q = $this->getDbConnection()->getPdoInstance()->query("SELECT MAX(zkp) FROM {$table->rawName}");
                        $id = $q->fetch(PDO::FETCH_COLUMN);
                        return @$id;
                }
		else
			return null;
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
	public function bindValuesCom( $values)
	{
                $this->query = $command->getText();
		//$this->prepare();
		foreach($values as $name=>$value)
		{
			$this->bindValue($name,$value,$this->_connection->getPdoType(gettype($value)));
			//$this->_paramLog[$name]=$value;
		}
		
	}
        
    /**
	 * Creates an UPDATE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data list of columns to be updated (name=>value)
	 * @param CDbCriteria $criteria the query criteria
	 * @throws CDbException if no columns are being updated for the given table
	 * @return CDbCommand update command.
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
             if (is_string($table))
                $this->table = $this->getSchema ()->getTable ($table);
            else
                $this->table = $table;
            
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$bindByPosition=isset($criteria->params[0]);
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null 
                                && $value != null
                                && !$column->isCalculated )
			{
				if($value instanceof CDbExpression)
				{
					$fields[]=$column->rawName.'='.$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}         
				else
				{
					$fields[]=$column->rawName.'='.$column->typecast($value);
				}
			}
		}
		if($fields===array())
			throw new CDbException(Yii::t('yii','No columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
		$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);

		$command=$this->getDbConnection()->createCommand($sql);
		$this->bindValues($command,array_merge($values,$criteria->params));

		return $command;
	}
        
        /**
	 * Generates the expression for selecting rows of specified primary key values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param mixed $columnName the column name(s). It can be either a string indicating a single column
	 * or an array of column names. If the latter, it stands for a composite key.
	 * @param array $values list of key values to be selected within
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @throws CDbException if specified column is not found in given table
	 * @return string the expression for selection
	 */
	public function createInCondition($table,$columnName,$values,$prefix=null)
	{
		if(($n=count($values))<1)
			return '0=1';

		$this->ensureTable($table);

		if($prefix===null)
			$prefix=$table->rawName.'.';

		$db=$this->getDbConnection();

		if(is_array($columnName) && count($columnName)===1)
			$columnName=reset($columnName);

		if(is_string($columnName)) // simple key
		{
			if(!isset($table->columns[$columnName]))
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
				array('{table}'=>$table->name, '{column}'=>$columnName)));
			$column=$table->columns[$columnName];

			$values=array_values($values);
			foreach($values as &$value)
			{
				$value=$column->typecast($value);
				/*if(is_string($value))
					$value=$db->quoteValue($value);*/
			}
			if($n===1)
				return $prefix.$column->rawName.($values[0]===null?' IS NULL':'='.$values[0]);
			else
				return $prefix.$column->rawName.' IN ('.implode(', ',$values).')';
		}
		elseif(is_array($columnName)) // composite key: $values=array(array('pk1'=>'v1','pk2'=>'v2'),array(...))
		{
			foreach($columnName as $name)
			{
				if(!isset($table->columns[$name]))
					throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name, '{column}'=>$name)));

				for($i=0;$i<$n;++$i)
				{
					if(isset($values[$i][$name]))
					{
						$value=$table->columns[$name]->typecast($values[$i][$name]);
						/*if(is_string($value))
							$values[$i][$name]=$db->quoteValue($value);
						else*/
							$values[$i][$name]=$value;
					}
					else
						throw new CDbException(Yii::t('yii','The value for the column "{column}" is not supplied when querying the table "{table}".',
							array('{table}'=>$table->name,'{column}'=>$name)));
				}
			}
			if(count($values)===1)
			{
				$entries=array();
				foreach($values[0] as $name=>$value)
					$entries[]=$prefix.$table->columns[$name]->rawName.($value===null?' IS NULL':'='.$value);
				return implode(' AND ',$entries);
			}

			return $this->createCompositeInCondition($table,$values,$prefix);
		}
		else
			throw new CDbException(Yii::t('yii','Column name must be either a string or an array.'));
	}

        /**
	 * Creates a SELECT command for a single table.
	 * @param CDbTableSchema $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createFindCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
                
                if (is_string($table))
                   $this->table = $this->getSchema()->getTable ($table);
               else
                   $this->table = $table;
               
                /* if table include containers trying to get them as binary (if query * )*/
                if ($criteria->select === '*') {
                    $criteria->select = array();
                    foreach($table->getColumnNames() as $name) {
                        if($table->getColumn($name)->dbType == "binary") {
                            $criteria->select[] = "CAST($name AS VARCHAR(255))";
                        }
                        else
                            $criteria->select[] = $name;
                    }
                }
		$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->getSchema()->quoteTableName($alias);

		// issue 1432: need to expand * when SQL has JOIN
		if($select==='*' && !empty($criteria->join))
		{
			$prefix=$alias.'.';
			$select=array();
			foreach($table->getColumnNames() as $name)
				$select[]=$prefix.$this->getSchema()->quoteColumnName($name);
			$select=implode(', ',$select);
		}

		$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->getDbConnection()->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}
        
        /**
	 * Creates an INSERT command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data data to be inserted (column name=>column value). If a key is not a valid column name, the corresponding value will be ignored.
	 * @return CDbCommand insert command
	 */
	public function createInsertCommand($table,$data)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$placeholders=array();
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null 
                                && !$column->isCalculated 
                                && $value != null
                                && ($value!==null || $column->allowNull))
			{
				$fields[]=$column->rawName;
				if($value instanceof CDbExpression)
				{
					$placeholders[]=$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else
				{
					$placeholders[]=$column->typecast($value);
					//$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					//$i++;
				}
			}
		}
		if($fields===array())
		{
			$pks=is_array($table->primaryKey) ? $table->primaryKey : array($table->primaryKey);
			foreach($pks as $pk)
			{
				$fields[]=$table->getColumn($pk)->rawName;
				$placeholders[]=$this->getIntegerPrimaryKeyDefaultValue();
			}
		}
		$sql="INSERT INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
		$command=$this->getDbConnection()->createCommand($sql);

		foreach($values as $name=>$value)
			$command->bindValue($name,$value);

		return $command;
	}
}
