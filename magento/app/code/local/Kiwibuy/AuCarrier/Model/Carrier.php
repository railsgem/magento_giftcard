<?php
class Kiwibuy_AuCarrier_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'kiwibuy_aucarrier';

    public $_special_cata_price = 9;
    public $_special_category_id = 20;
    public $max_item_times = 1;
    public $max_exceed_qty = 0;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_special_cata_price = $this->getConfigData('special_cata_price');
        $this->_special_category_id = $this->getConfigData('special_category_id');
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getStandardShippingRate($request));
        return $result;
    }

    protected function _getStandardShippingRate($request)
    {
        $special_cata_price = $this->_special_cata_price;
        $special_category_id = $this->_special_category_id;
        $config_price = $this->getConfigData('price');
        $price = 0;
        $itemQtythThreshold = $this->getConfigData('item_qty_threshold');
        $totalQtythThreshold = $this->getConfigData('total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;
        $special_cata_total_price = 0;
        $special_cata_total_qty = 0;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId()); 
            $categoryIds = $product->getCategoryIds();
            if ($item->getQty() > $itemQtythThreshold){
                $eligibleItemQty = false;
                if (in_array($special_category_id, $categoryIds)){
                } else {
                    $qty_exceed_part = $qty_exceed_part + ($item->getQty() - $itemQtythThreshold);
                }
                if ($max_item_times < ceil($item->getQty() / $itemQtythThreshold)) {
                    $max_item_times = ceil($item->getQty() / $itemQtythThreshold);
                }
            }
            if (in_array($special_category_id, $categoryIds)){
                $special_cata_total_qty = $special_cata_total_qty + $item->getQty();
            } else {
                $totalQty = $totalQty + $item->getQty(); //except special catagory
            }
        }    
        //special catagory
        $special_cata_total_price = $special_cata_total_qty * $special_cata_price;

        $allItems = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId()); 
            $categoryIds = $product->getCategoryIds();

            if (in_array($special_category_id, $categoryIds)){
                continue;
            } else {
                $allItems[] = (array('product_id' => $item->getProductId(),
                                    'product_qty' => $item->getQty()));
            }
        }    
        if ($totalQty) {
            while ($max_item_times > 0) {
                // var_dump($max_item_times);
                $i = 0;
                $totalQty = 0;
                foreach ($allItems as $item) {
                    // var_dump($allItems[$i]['product_qty']);
                    if ($allItems[$i]['product_qty']>= $itemQtythThreshold){
                        $allItems[$i]['product_qty'] = $allItems[$i]['product_qty'] - $itemQtythThreshold;
                        $totalQty = $totalQty + $itemQtythThreshold;
                    } else {
                        $totalQty = $totalQty + $allItems[$i]['product_qty'];
                        $allItems[$i]['product_qty'] = 0;
                    }
                    $i++;
                }
                if ($totalQty > $totalQtythThreshold) {
                    $price = $price + $config_price + ($totalQty - $totalQtythThreshold) * $addPricePerItem;
                } else {
                    $price = $price + $config_price;
                }
                $max_item_times--;
            }
        }

        $price = $price + $special_cata_total_price;
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