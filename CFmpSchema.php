<?php
/**
 * CFmpSchema class file.
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
class CFmpSchema extends CDbSchema
{
	/**
	 * @var array the abstract column types mapped to physical column types.
	 */
    public $columnTypes=array(
        'pk' => 'text',
        'string' => 'text',
        'text' => 'text',
        'integer' => 'decimal',
        'float' => 'decimal',
        'decimal' => 'decimal',
        'datetime' => 'timestamp',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
        'boolean' => 'decimal',
	'money' => 'decimal',
	);

	/**
	 * Returns all source table names in the database.
	 * @param string $schema the schema of the tables. This is not used for fmp database.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='')
	{
		$sql="SELECT DISTINCT(BaseTableName) FROM FileMaker_Tables";
		return $this->getDbConnection()->createCommand($sql)->queryColumn();
	}

	/**
	 * Creates a command builder for the database.
	 * @return CSqliteCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CFmpCommandBuilder($this);
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CDbTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CDbTableSchema;
		$table->name=$name;
		$table->rawName=$this->quoteTableName($name);

		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Collects the table column metadata.
	 * @param CDbTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql="SELECT * FROM FileMaker_Fields WHERE TableName = '".$table->name."'";
		$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		if(empty($columns))
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				elseif(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
			}
		}
		if(is_string($table->primaryKey))
		{
			$table->sequenceName='';
			$table->columns[$table->primaryKey]->autoIncrement=false;
		}

		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CDbTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$foreignKeys=array();
                foreach ( $table->columns as $name => $c) {
                    /* @var $c CFmpColumnSchema */
                    if ( $c->isForeignKey )
                        $foreignKeys[$name] = array(preg_replace('/(zkf|zkp)_([^_]*).*/', "$2", $name), 'zkp');
                }
		
		$table->foreignKeys=$foreignKeys;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CFmpColumnSchema;
		$c->name=$column['FieldName'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=true;
		$c->isPrimaryKey=substr($c->name, 0, 3)=="zkp"; //Primary key name must start with "zkp"
		$c->isForeignKey=substr($c->name, 0, 3)=="zkf" || substr($c->name, 0, 4)=="zkp_"; //Foreign keys must respect this pattern (zkf|zkp)_<?tablename:[^_]>.*
		$c->comment=null; // Filemaker ODBC does not provide column comments at all

		$c->init(strtolower($column['FieldType']),'');
		return $c;
	}

	/**
	 * Unavailable.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 */
	public function renameTable($table, $newName)
	{
		throw new CDbException(Yii::t('yii', 'ALTER table is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return string the SQL statement for truncating a DB table.
	 * @since 1.1.6
	 */
	public function truncateTable($table)
	{
		return "DELETE FROM ".$this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for dropping a DB column.
	 * Because Filemaker does not support dropping a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB column.
	 * @since 1.1.6
	 */
	public function dropColumn($table, $column)
	{
		throw new CDbException(Yii::t('yii', 'Dropping DB column is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * Because Filemaker does not support renaming a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		throw new CDbException(Yii::t('yii', 'Renaming a DB column is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * Because Filemaker does not support adding foreign key to an existing table, calling this method will throw an exception.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return string the SQL statement for adding a foreign key constraint to an existing table.
	 * @since 1.1.6
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete=null, $update=null)
	{
		throw new CDbException(Yii::t('yii', 'Adding a foreign key constraint to an existing table is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * Because FileMaker does not support dropping a foreign key constraint, calling this method will throw an exception.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @since 1.1.6
	 */
	public function dropForeignKey($name, $table)
	{
		throw new CDbException(Yii::t('yii', 'Dropping a foreign key constraint is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * Because FileMaker ODBC driver does not support altering a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 * @since 1.1.6
	 */
	public function alterColumn($table, $column, $type)
	{
		throw new CDbException(Yii::t('yii', 'Altering a DB column is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 * Because FileMaker ODBC driver does not support dropping an Index, calling this method will throw an exception.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 * @since 1.1.6
	 */
	public function dropIndex($name, $table)
	{
		throw new CDbException(Yii::t('yii', 'Dropping an index is not supported by FileMaker ODBC driver.'));
	}

	/**
	 * Builds a SQL statement for adding a primary key constraint to an existing table.
	 * Because FileMaker ODBC driver does not support adding a primary key on an existing table this method will throw an exception.
	 * @param string $name the name of the primary key constraint.
	 * @param string $table the table that the primary key constraint will be added to.
	 * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
	 * @return string the SQL statement for adding a primary key constraint to an existing table.
	 * @since 1.1.13
	 */
	public function addPrimaryKey($name,$table,$columns)
	{
		throw new CDbException(Yii::t('yii', 'Adding a primary key after table has been created is not supported by FileMaker ODBC driver.'));
	}


	/**
	 * Builds a SQL statement for removing a primary key constraint to an existing table.
	 * Because FileMaker ODBC driver does not support dropping a primary key from an existing table this method will throw an exception
	 * @param string $name the name of the primary key constraint to be removed.
	 * @param string $table the table that the primary key constraint will be removed from.
	 * @return string the SQL statement for removing a primary key constraint from an existing table.
	 * @since 1.1.13
	 */
	public function dropPrimaryKey($name,$table)
	{
		throw new CDbException(Yii::t('yii', 'Removing a primary key after table has been created is not supported by FileMaker ODBC driver.'));

	}

	/**
	 * Quotes a simple table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 * @since 1.1.6
	 */
	public function quoteSimpleTableName($name)
	{
		return '"'.$name.'"';
	}

	/**
	 * Quotes a simple column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @since 1.1.6
	 */
	public function quoteSimpleColumnName($name)
	{
		return '"'.$name.'"';
	}
}
