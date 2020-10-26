<?php
class ZhenIT_Redsys_Block_Iupay_Form extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        $this->setTemplate('redsys/form_iupay.phtml');
        parent::_construct();
    }
}