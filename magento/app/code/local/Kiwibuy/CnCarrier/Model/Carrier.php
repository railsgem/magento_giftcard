<?php
class Kiwibuy_AuCarrier_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'kiwibuy_aucarrier';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');

        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getStandardShippingRate($request));
        return $result;
    }

    protected function _getStandardShippingRate($request)
    {
        $price = $this->getConfigData('price');
        $itemQtythThreshold = $this->getConfigData('item_qty_threshold');
        $totalQtythThreshold = $this->getConfigData('total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getQty() > $itemQtythThreshold){
                $eligibleItemQty = false;
            }
            $totalQty = $totalQty + $item->getQty();
        }    
        if ($totalQty > $totalQtythThreshold) {
            $price = $price + ($totalQty - $totalQtythThreshold) * $addPricePerItem;
        }
        if ($eligibleItemQty) {
            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

            $rate->setCarrier($this->_code);
            /**
             * getConfigData(config_key) returns the configuration value for the
             * carriers/[carrier_code]/[config_key]
             */
            $rate->setCarrierTitle($this->getConfigData('title'));

            $rate->setMethod('standand');
            $rate->setMethodTitle('Standard');

            $rate->setPrice($price);
            $rate->setCost(0);
        }

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            'standard' => 'Standard',
        );
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}