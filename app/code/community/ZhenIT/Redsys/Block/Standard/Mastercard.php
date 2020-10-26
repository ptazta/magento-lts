<?php
class ZhenIT_Redsys_Block_Standard_Mastercard extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('redsys/mastercard.phtml');
    }
}