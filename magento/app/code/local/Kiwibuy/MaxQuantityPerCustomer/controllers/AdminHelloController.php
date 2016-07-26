<?php
class Kiwibuy_MaxQuantityPerCustomer_AdminhelloController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
    	echo "string";
    	$helper = Mage::helper('kiwibuy_maxquantitypercustomer');
    	$configMsg=$helper->isModuleEnabled();
    	var_dump($configMsg);

    	$observer = new Kiwibuy_MaxQuantityPerCustomer_Model_Observer;
    	$observer->CheckQuantityAmount();
        $this->loadLayout();
        $this->renderLayout();
    }
} 