<?php
class Sitewards_B2BProfessional_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct () {
		$this->_init('b2bprofessional/order', 'id');
	}

	/**
	 * @param int $iOrderId
	 * @param string $sKey
	 */
	public function deleteByOrder ($iOrderId, $sKey) {
		$sTable = $this->getMainTable();
		$sWhere = $this->_getWriteAdapter()
			->quoteInto('order_id = ? AND ', $iOrderId)
			. $this->_getWriteAdapter()
				->quoteInto('`key` = ?', $sKey);
		$this->_getWriteAdapter()
			->delete($sTable, $sWhere);
	}

	/**
	 * @param int $iOrderId
	 * @param string $sKey
	 * @return array
	 */
	public function getByOrder ($iOrderId, $sKey = '') {
		$sTable = $this->getMainTable();
		$sWhere = $this->_getReadAdapter()
			->quoteInto('order_id = ?', $iOrderId);
		if (!empty($sKey)) {
			$sWhere .= $this->_getReadAdapter()
				->quoteInto(' AND `key` = ? ', $sKey);
		}
		$sSql = $this->_getReadAdapter()
			->select()
			->from($sTable)
			->where($sWhere);
		$aRows = $this->_getReadAdapter()
			->fetchAll($sSql);
		$aReturn = array();
		foreach ($aRows as $row) {
			$aReturn[$row['key']] = $row['value'];
		}
		return $aReturn;
	}
}