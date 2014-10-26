<?php
/**
 * CActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveRecord is the base class for classes representing relational data.
 *
 * It implements the active record design pattern, a popular Object-Relational Mapping (ORM) technique.
 * Please check {@link http://www.yiiframework.com/doc/guide/database.ar the Guide} for more details
 * about this class.
 *
 * @property CDbCriteria $dbCriteria The query criteria that is associated with this model.
 * This criteria is mainly used by {@link scopes named scope} feature to accumulate
 * different criteria specifications.
 * @property CActiveRecordMetaData $metaData The meta for this AR class.
 * @property CDbConnection $dbConnection The database connection used by active record.
 * @property CDbTableSchema $tableSchema The metadata of the table that this AR belongs to.
 * @property CDbCommandBuilder $commandBuilder The command builder used by this AR.
 * @property array $attributes Attribute values indexed by attribute names.
 * @property boolean $isNewRecord Whether the record is new and should be inserted when calling {@link save}.
 * This property is automatically set in constructor and {@link populateRecord}.
 * Defaults to false, but it will be set to true if the instance is created using
 * the new operator.
 * @property mixed $primaryKey The primary key value. An array (column name=>column value) is returned if the primary key is composite.
 * If primary key is not defined, null will be returned.
 * @property mixed $oldPrimaryKey The old primary key value. An array (column name=>column value) is returned if the primary key is composite.
 * If primary key is not defined, null will be returned.
 * @property string $tableAlias The default table alias.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.ar
 * @since 1.0
 */
abstract class CFmpActiveRecord extends CActiveRecord
{
	/**
	 * Given 'with' options returns a new active finder instance.
	 *
	 * @param mixed $with the relation names to be actively looked for
	 * @return CActiveFinder active finder for the operation
	 *
	 * @since 1.1.14
	 */
	public function getActiveFinder($with)
	{
		return new CFmpActiveFinder($this,$with);
	}
}
