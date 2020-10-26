<?php
/*
 * @category   ZhenIT
 * @package    ZhenIT_Redsys
 * @copyright  Copyright (c) 2014 ZhenIT Software (http://ZhenIT.com)
 */
$installer = $this;

$installer->startSetup();
$installer->_conn->addColumn($installer->getTable('sales_flat_order'), 'ds_order', 'varchar(32)');
$installer->_conn->addColumn($installer->getTable('sales_flat_order'), 'ds_authorisationcode', 'varchar(32)');
if(Mage::getModel('sales/order_status')){
    $statuses = Mage::getModel('sales/order_status')->getCollection()->addFieldToFilter('status', 'redsys_authorised');
    if (0 == $statuses->count()) {
        $data = array(
            array('redsys_authorised', 'Redsýs: pagado'),
            array('redsys_preauthorised', 'Redsýs: preauorizado'),
            array('redsys_authenticated', 'Redsýs: autenticado'),
            array('redsys_rejected', 'Redsýs: denegado'),
            array('redsys_refunded', 'Redsýs: reembolsado')
        );
        $connection = $installer->getConnection()->insertArray(
            $installer->getTable('sales/order_status'),
            array('status', 'label'),
            $data
        );
    }
    Mage::getModel('sales/order_status')
        ->load('redsys_authorised')
        ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING, '0');
    Mage::getModel('sales/order_status')
        ->load('redsys_preauthorised')
        ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING, '0');
    Mage::getModel('sales/order_status')
        ->load('redsys_authenticated')
        ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING, '0');
    Mage::getModel('sales/order_status')
        ->load('redsys_rejected')
        ->assignState(Mage_Sales_Model_Order::STATE_CANCELED, '0');
}
$installer->endSetup();

