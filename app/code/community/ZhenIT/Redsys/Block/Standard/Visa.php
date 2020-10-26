<?php
class ZhenIT_Redsys_Block_Standard_Visa extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('redsys/visa.phtml');
    }
}