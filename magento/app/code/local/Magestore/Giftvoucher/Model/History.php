<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Giftvoucher History Model
 * 
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */

class Magestore_Giftvoucher_Model_History extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('giftvoucher/history');
    }

    /**
     * Filter Gift Card history
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @param int $action
     * @return Magestore_Giftvoucher_Model_History
     */
    public function getCollectionByOrderAction($giftVoucher, $order, $action)
    {
        return $this->getCollection()
                ->addFieldToFilter('giftvoucher_id', $giftVoucher->getId())
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('order_increment_id', $order->getIncrementId());
    }

    /**
     * Get the total amount of Gift Card spent in order
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getTotalSpent($giftVoucher, $order)
    {
        $total = 0;
        foreach ($this->getCollectionByOrderAction($giftVoucher, $order, 
            Magestore_Giftvoucher_Model_Actions::ACTIONS_SPEND_ORDER) as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }

    /**
     * Get the total amount of Gift Card refunded in order
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getTotalRefund($giftVoucher, $order)
    {
        $total = 0;
        foreach ($this->getCollectionByOrderAction($giftVoucher, $order, 
            Magestore_Giftvoucher_Model_Actions::ACTIONS_REFUND) as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }
    /**
     * Add Gift Card data to customer account
     *
     * @param 
     * @return Mage_Sales_Model_Order
     */
    public function _addGiftVoucherForCollection($order)
    {
        $router = Mage::app()->getRequest()->getRouteName();
        if (Mage::app()->getStore()->isAdmin()) {
            $store = Mage::getSingleton('adminhtml/session_quote')->getStore();
        } else {
            $store = Mage::app()->getStore();
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() != 'giftvoucher') {
                continue;
            }

            $options = $item->getProductOptions();

            $buyRequest = $options['info_buyRequest'];

            $quoteItemOptions = Mage::getModel('sales/quote_item_option')
                ->getCollection()->addFieldToFilter('item_id', array('eq' => $item->getQuoteItemId()));
            if (isset($buyRequest['amount']) && $quoteItemOptions) {
                foreach ($quoteItemOptions as $quoteItemOption) {
                    if ($quoteItemOption->getCode() == 'amount') {
                        $buyRequest['amount'] = $store->roundPrice($quoteItemOption->getValue());
                        $options['info_buyRequest'] = $buyRequest;
                        $item->setProductOptions($options);
                    }
                }
            }
            $giftVouchers = Mage::getModel('giftvoucher/giftvoucher')->getCollection()->addItemFilter($item->getId());

            $time = time();
            for ($i = 0; $i < $item->getQtyOrdered() - $giftVouchers->getSize(); $i++) {
                $giftVoucher = Mage::getModel('giftvoucher/giftvoucher');

                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                if (isset($buyRequest['amount'])) {
                    $amount = $buyRequest['amount'];
                } else {
                    $amount = $item->getPrice();
                }

                $giftVoucher->setBalance($amount)->setAmount($amount);
                $giftVoucher->setOrderAmount($item->getBasePrice());

                $giftProduct = Mage::getModel('giftvoucher/product')->loadByProduct($product);
                $giftVoucher->setDescription($giftProduct->getGiftcardDescription());
                if ($giftProduct->getId()) {
                    $conditionsArr = unserialize($giftProduct->getConditionsSerialized());
                    $actionsArr = unserialize($giftProduct->getActionsSerialized());
                    if (!empty($conditionsArr) && is_array($conditionsArr)) {
                        $giftVoucher->getConditions()->loadArray($conditionsArr);
                    }
                    if (!empty($actionsArr) && is_array($actionsArr)) {
                        $giftVoucher->getActions()->loadArray($actionsArr);
                    }
                }
                //Hai.Tran
                if (isset($buyRequest['customer_name'])) {
                    $giftVoucher->setCustomerName($buyRequest['customer_name']);
                }
                if (isset($buyRequest['giftcard_template_id']) && $buyRequest['giftcard_template_id']) {
                    $giftVoucher->setGiftcardTemplateId($buyRequest['giftcard_template_id']);
                }
                if (isset($buyRequest['recipient_name'])) {
                    $giftVoucher->setRecipientName($buyRequest['recipient_name']);
                }
                if (isset($buyRequest['recipient_email'])) {
                    $giftVoucher->setRecipientEmail($buyRequest['recipient_email']);
                }
                if (isset($buyRequest['message'])) {
                    $giftVoucher->setMessage($buyRequest['message']);
                }
                if (isset($buyRequest['notify_success'])) {
                    $giftVoucher->setNotifySuccess($buyRequest['notify_success']);
                }
                if (isset($buyRequest['day_to_send']) && $buyRequest['day_to_send']) {
                    $giftVoucher->setDayToSend(date('Y-m-d', strtotime($buyRequest['day_to_send'])));
                }

                //time zone 
                if (isset($buyRequest['timezone_to_send']) && $buyRequest['timezone_to_send']) {
                    $giftVoucher->setTimezoneToSend($buyRequest['timezone_to_send']);
                    $customerZone = new DateTimeZone($giftVoucher->getTimezoneToSend());
                    $date = new DateTime($giftVoucher->getDayToSend(), $customerZone);
                    $serverTimezone = Mage::app()->getStore()->getConfig('general/locale/timezone');
                    $date->setTimezone(new DateTimeZone($serverTimezone));
                    $giftVoucher->setDayStore($date->format('Y-m-d'));
                }
                //end timezone

                if (isset($buyRequest['giftcard_template_image']) && $buyRequest['giftcard_template_image']) {
                    if (isset($buyRequest['giftcard_use_custom_image']) && $buyRequest['giftcard_use_custom_image']) {
                        $dir = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'giftvoucher' . DS . 'images' . 
                            DS . $buyRequest['giftcard_template_image'];
                        if (file_exists($dir)) {
                            $imageObj = new Varien_Image($dir);
                            $imagePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 
                                'giftvoucher/template/images/';
                            $customerUploadImage = $time . $buyRequest['giftcard_template_image'];
                            $dirCustomerUpload = Mage::getBaseDir() . 
                                str_replace("/", DS, strstr($imagePath, '/media')) . $customerUploadImage;
                            if (!file_exists($dirCustomerUpload)) {
                                $imageObj->save($dirCustomerUpload);
                                Mage::helper('giftvoucher')
                                    ->customResizeImage($imagePath, $customerUploadImage, 'images');
                            }
                            $giftVoucher->setGiftcardCustomImage(true);
                            $giftVoucher->setGiftcardTemplateImage($customerUploadImage);
                            // unlink($dir);
                        } else {
                            $giftVoucher->setGiftcardTemplateImage('default.png');
                        }
                    } else {
                        $giftVoucher->setGiftcardTemplateImage($buyRequest['giftcard_template_image']);
                    }
                }

                if (isset($buyRequest['recipient_ship']) && $buyRequest['recipient_ship'] != null 
                    && $address = $order->getShippingAddress()) {
                    $giftVoucher->setRecipientAddress($address->getFormated());
                }

                $giftVoucher->setCurrency($store->getCurrentCurrencyCode());

                if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $giftVoucher->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE);
                } else {
                    $giftVoucher->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_PENDING);
                }

                if ($timeLife = Mage::helper('giftvoucher')->getGeneralConfig('expire', $order->getStoreId())) {
                    $orderTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
                    $expire = date("Y-m-d H:i:s", strtotime($orderTime . ' +' . $timeLife . ' days'));
                    $giftVoucher->setExpiredAt($expire);
                }

                $giftVoucher->setCustomerId($order->getCustomerId())
                    ->setCustomerEmail($order->getCustomerEmail())
                    ->setStoreId($order->getStoreId());

                if (!$giftVoucher->getCustomerName()) {
                    $giftVoucher->setCustomerName($order->getData('customer_firstname') . ' ' . 
                        $order->getData('customer_lastname'));
                }

                $giftVoucher->setAction(Magestore_Giftvoucher_Model_Actions::ACTIONS_CREATE)
                    ->setComments(Mage::helper('giftvoucher')->__('Created for order %s', $order->getIncrementId()))
                    ->setOrderIncrementId($order->getIncrementId())
                    ->setOrderItemId($item->getId())
                    ->setProductId($item->getProductId())
                    ->setExtraContent(Mage::helper('giftvoucher')->__('Created by customer %s %s', 
                        $order->getData('customer_firstname'), $order->getData('customer_lastname')))
                    ->setIncludeHistory(true);
                try {
                    if ($giftVoucher->getDayToSend() && strtotime($giftVoucher->getDayToSend()) > time()
                    ) {
                        $giftVoucher->setData('dont_send_email_to_recipient', 1);
                    }
                    if (!empty($buyRequest['recipient_ship'])) {
                        $giftVoucher->setData('is_sent', 2);
                        if (!Mage::helper('giftvoucher')->getEmailConfig('send_with_ship', $order->getStoreId())) {
                            $giftVoucher->setData('dont_send_email_to_recipient', 1);
                        }
                    }
var_dump($giftVoucher);
exit;
                    //  die(now(true));
                    $giftVoucher->save();
                    if ($order->getCustomerId()) {
                        $timeSite = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
                        Mage::getModel('giftvoucher/customervoucher')
                            ->setCustomerId($order->getCustomerId())
                            ->setVoucherId($giftVoucher->getId())
                            ->setAddedDate($timeSite)
                            ->save();
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
        return $this;
    }

}
