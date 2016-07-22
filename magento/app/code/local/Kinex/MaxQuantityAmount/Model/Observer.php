<?php
class Kinex_MaxQuantityAmount_Model_Observer 
{
    private $_helper;
    
    public function __construct() 
    {
        $this->_helper = Mage::helper('kinex_maxquantityamount');
    }


    public function CheckQuantityAmount(Varien_Event_Observer $observer)
    {
        
         $configMsg=$this->_helper->getMaxQuantityMsg();

         if (!$this->_helper->isModuleEnabled()) {
            return;
        }  

         $item = $observer->getItem();
         $UserEnterQuantity=$item->getQty();
         
           
         $productObj = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
         
          
         $productMaxQuantityEnable = $productObj->getAttributeText('maximum_quantity_enable');


         $productMaxQuantity = (int)$productObj->getMaximumQuantity();
         $productMaxQuantityMsg = $productObj->getMaximumQuantityMessage();
         $Message=$productMaxQuantityMsg;

          if(!$productMaxQuantityMsg){
             $Message=$configMsg;                

          }

           if($productMaxQuantityEnable=='No'){
               return;    

           }
        

         if($UserEnterQuantity > $productMaxQuantity){
           
           Mage::getSingleton('checkout/session')->addError(
                $this->_helper->__($Message));

           Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            Mage::app()->getResponse()->sendResponse();
            exit;


         }  


           
    }

}