<?php
class Sitewards_B2BProfessional_Model_Resource_Quote extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
		$this->_init('b2bprofessional/quote', 'id');
	}

	/**
	 * @param int $iQuoteId
	 * @param string $sKey
	 */
	public function deleteByQuote($iQuoteId, $sKey){
		$sTable = $this->getMainTable();
		$sWhere = $this->_getWriteAdapter()->quoteInto('quote_id = ? AND ', $iQuoteId)
			. $this->_getWriteAdapter()->quoteInto('`key` = ?', $sKey);
		$this->_getWriteAdapter()->delete($sTable, $sWhere);
	}

	/**
	 * @param int $iQuoteId
	 * @param string $sKey
	 * @return array
	 */
	public function getByQuote($iQuoteId, $sKey = ''){
		$sTable = $this->getMainTable();
		$sWhere = $this->_getReadAdapter()->quoteInto('quote_id = ?', $iQuoteId);
		if (!empty($sKey)){
			$sWhere .= $this->_getReadAdapter()->quoteInto(' AND `key` = ? ', $sKey);
		}
		$sSql = $this->_getReadAdapter()->select()->from($sTable)->where($sWhere);
		$aRows = $this->_getReadAdapter()->fetchAll($sSql);
		$aReturn = array();
		foreach($aRows as $row){
			$aReturn[$row['key']] = $row['value'];
		}
		return $aReturn;
	}
}