<?php
/**
 * CFmpConnection class file.
 *
 * @author Romain Dunand <romain_pro@dunand.me>
 * @copyright 2014 YiiFmpDb
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 */

/**
 * CFmpConnection represents a connection to a FileMaker database.
 * As PDO ODBC is buggy with FileMaker ODBC Driver, we have to use a PDO Emulation classe (pdoODBCEmulator)
 * 
 * @author Romain Dunand <romain_pro@dunand.m>
 * @package extensions.yiifmpdb
 * @since 1.1.14
 */
class CFmpConnection extends CDbConnection {

    protected function initConnection($pdo) {
        parent::initConnection($pdo);
        
    }

    /**
    * Creates a command for execution.
    * @param mixed $query the DB query to be executed. This can be either a string representing a SQL statement,
    * or an array representing different fragments of a SQL statement. Please refer to {@link CDbCommand::__construct}
    * for more details about how to pass an array as the query. If this parameter is not given,
    * you will have to call query builder methods of {@link CDbCommand} to build the DB query.
    * @return CDbCommand the DB command
    */
   public function createCommand($query=null)
   {
           $this->setActive(true);
           return new CFmpCommand($this,$query);
   }
   
    public $driverMap = array(
        'odbc' => 'CFmpSchema', // FileMaker® ODBC driver, As PDO
    );
    
    public function getPdoType($type) {
        if ($type == 'NULL') {
            return PDO::PARAM_STR;
        } else {
            return parent::getPdoType($type);
        }
    }

}

$dir = dirname(__FILE__);
$alias = md5($dir);
Yii::setPathOfAlias($alias, $dir);
Yii::import($alias . '.*');