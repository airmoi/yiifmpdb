yiifmpdb
========

Support FileMaker ODBC databases in Yii Framework

Requirements
------------
* PHP module pdo_odbc
* FileMaker ODBC driver installed
* A running FileMaker Database with ODBC sharing on

Installation
------------
* Extract the release file under protected/extensions
* In your protected/config/main.php, add the following:
```php
<?php     
...     
'components' => array(     
...     
'db'=>array(     
			'connectionString' => 'odbc:odbc_source',     
			'emulatePrepare' => false,     
			'username' => 'username',      
			'password' => 'password',     
			'class' => 'ext.fmpdb.CFmpConnection',      
		),     
    ...     
  ),     
...
```

Gii Usage
------------
As FileMaker doesn't support foreign keys, your must respect this rules in order to allow gii to detect them
* Primary keys name must start with 'zkp'
* Foreign keys name must respect this pattern : (zkf|zkp)\_<?tablename:[^\_]>.* (Ex : zkf_CONTACT)
* Relation tables (many to many) must use 2 PK that are also FK so they must be named using 'zkp' (ex : zkp\_CONTACT, zkp\_ADRESS)
