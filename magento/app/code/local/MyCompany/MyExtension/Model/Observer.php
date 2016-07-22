<?php
 
class MyCompany_MyExtension_Model_Observer
{
    private static $_handleCustomerFirstOrderCounter = 1;
 
	public function handleCustomerFirstOrder($observer)
	{
        $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addFieldToSelect('increment_id')
                    ->addFieldToFilter('customer_id', array('eq' => $observer->getEvent()->getOrder()->getCustomerId()));
 
        //$orders->getSelect()->limit(2);
 
        //if ($orders->count() == 1) {
        if ($orders->getSize() == 1) {
            if (self::$_handleCustomerFirstOrderCounter > 1) {
                return $this;
            }
 
            self::$_handleCustomerFirstOrderCounter++;
 
			Mage::dispatchEvent('customer_first_order', array('order' => $observer->getEvent()->getOrder()));
		}		
 
		return $this;
	}
}