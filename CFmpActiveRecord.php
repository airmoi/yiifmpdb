<?php
/**
 * CFmpActiveRecord class file.
 *
 * @author Romain Dunand <romain_pro@dunand.me>
 * @copyright 2014 YiiFmpDb
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 */

/**
 * CFmpActiveRecord class extends CActiveRecord.
 * Overwrites default ActiveFinder
 *
 * @author Romain Dunand <romain_pro@dunand.m>
 * @package extensions.yiifmpdb
 * @since 1.1.14
 */
abstract class CFmpActiveRecord extends CActiveRecord
{
	/**
	 * Given 'with' options returns a new active finder instance.
	 *
	 * @param mixed $with the relation names to be actively looked for
	 * @return CFmpActiveFinder active finder for the operation
	 *
	 * @since 1.1.14
	 */
	public function getActiveFinder($with)
	{
		return new CFmpActiveFinder($this,$with);
	}
}
