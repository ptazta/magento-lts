<?php

/**
 * Redsys  Checkout Module
 */
class ZhenIT_Redsys_Model_Iupay extends ZhenIT_Redsys_Model_Standard
{
	protected $_code              = 'redsys_iupay';
	protected $_formBlockType     = 'redsys/iupay_form';

	public function getConfigData($field, $storeId = null) {
		if (null === $storeId) {
			$storeId = $this->getStore();
		}
		$path = 'payment/' . $this->getCode() . '/' . $field;
		$val  = Mage::getStoreConfig($path, $storeId);
		if (is_null($val)) {
			$path = 'payment/redsys_standard/' . $field;
			$val  = Mage::getStoreConfig($path, $storeId);
		}
		return $val;
	}
}
