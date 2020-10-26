<?php
class ZhenIT_Redsys_Block_Iupay_Redirect extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $iupay = Mage::getModel('redsys/iupay');
        $form = new Varien_Data_Form();
        $form->setAction($iupay->getRedsysUrl())
            ->setId('redsys_standard_checkout')
            ->setName('Iupay!')
            ->setMethod('POST')
            ->setUseContainer(true);

        foreach($iupay->getStandardCheckoutFormFields() as $field => $value)
        {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $this->setFormRedirect($form->toHtml());
        $this->setWindowstate($iupay->getConfigData('windowstate'));
    }
}