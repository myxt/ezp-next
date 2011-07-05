<?php
/**
 * File containing the StorageInterface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\fields;

/**
 * @package ezp.persistence.fields
 */
interface StorageInterface
{

	/**
	 * @return int
	 */
	public function typeHint();

	/**
	 * @param array data
	 * @param ezp.persistence.content.values.ContentField field
	 */
	public function setValue(array $data, \ezp\persistence\content\values\ContentField $field);

	/**
	 * @param int filedId
	 * @param value
	 * @return boolean
	 * 
	 */
	public function storeFieldData($filedId, $value);

	/**
	 * @param int fieldId
	 */
	public function getFieldData($fieldId);

	/**
	 * @param array fieldId
	 * @return boolean
	 */
	public function deleteFieldData(array $fieldId);

	/**
	 * @return bool
	 */
	public function hasFieldData();

	/**
	 * @param int fieldId
	 */
	public function copyFieldData($fieldId);

	/**
	 * @param int fieldId
	 */
	public function getIndexData($fieldId);
}
?>