<?php
class ZhenIT_Redsys_Block_Standard_Form extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        $this->setTemplate('redsys/form.phtml');
        parent::_construct();
    }
}