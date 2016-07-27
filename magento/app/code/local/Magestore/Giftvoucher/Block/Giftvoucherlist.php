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
 * Giftvoucher Giftvoucherlist block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Magestore_Giftvoucher_Block_Giftvoucherlist extends Mage_Core_Block_Template
{
    
    protected function _construct()
    {
        parent::_construct();
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $timezone = ((Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS)) / 3600);
        $collection = Mage::getModel('giftvoucher/customervoucher')->getCollection()
            ->addFieldToFilter('main_table.customer_id', $customerId);
        $voucherTable = Mage::getModel('core/resource')->getTableName('giftvoucher');
        $collection->getSelect()
            ->joinleft(
                array('voucher_table' => $voucherTable), 'main_table.voucher_id = voucher_table.giftvoucher_id', array(
                'recipient_name',
                'gift_code',
                'product_id' => 'voucher_table.product_id',
                'balance',
                'currency',
                'status',
                'expired_at',
                'customer_check_id' => 'voucher_table.customer_id',
                'recipient_email',
                'customer_email'
            ))
            ->where('voucher_table.status <> ?', Magestore_Giftvoucher_Model_Status::STATUS_DELETED);
        $collection->getSelect()
            ->columns(array(
                'added_date' => new Zend_Db_Expr("SUBDATE(added_date,INTERVAL " . $timezone . " HOUR)"),
        ));
        $collection->getSelect()
            ->columns(array(
                'expired_at' => new Zend_Db_Expr("SUBDATE(expired_at,INTERVAL " . $timezone . " HOUR)"),
        ));
        $collection->setOrder('customer_voucher_id', 'DESC');
        $this->setCollection($collection);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'giftvoucher_pager')
            ->setTemplate('page/html/pager.phtml')
            ->setCollection($this->getCollection());
        $this->setChild('giftvoucher_pager', $pager);

        $grid = $this->getLayout()->createBlock('giftvoucher/grid', 'giftvoucher_grid');
        // prepare column

        // $grid->addColumn('gift_code', array(
        //     'header' => $this->__('Gift Card Code'),
        //     'index' => 'gift_code',
        //     'format' => 'medium',
        //     'align' => 'left',
        //     'width' => '80px',
        //     'render' => 'getCodeTxt',
        //     'searchable' => true,
        // ));

        $grid->addColumn('product_name', array(
            'header' => $this->__('优惠券名称'),
            'index' => 'product_name',
            'format' => 'medium',
            'align' => 'left',
            'width' => '160px',
            'render' => 'getGiftProductName',
            'searchable' => true,
        ));

        $grid->addColumn('product_short_description', array(
            'header' => $this->__('活动范围'),
            'index' => 'product_short_description',
            'format' => 'medium',
            'align' => 'left',
            'width' => '160px',
            'render' => 'getGiftProductShortDescription',
            'searchable' => true,
        ));

        $grid->addColumn('product_description', array(
            'header' => $this->__('活动规则'),
            'index' => 'product_description',
            'format' => 'medium',
            'align' => 'left',
            'width' => '160px',
            'render' => 'getGiftProductDescription',
            'searchable' => true,
        ));

        $grid->addColumn('balance', array(
            'header' => $this->__('金额'),
            'align' => 'left',
            'type' => 'price',
            'index' => 'balance',
            'render' => 'getBalanceFormat',
            'searchable' => true,
        ));

        $statuses = Mage::getSingleton('giftvoucher/status')->getOptionArray();
        $grid->addColumn('status', array(
            'header' => $this->__('状态'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => $statuses,
            'width' => '50px',
            'searchable' => true,
        ));

        $grid->addColumn('added_date', array(
            'header' => $this->__('领取时间'),
            'index' => 'added_date',
            'type' => 'date',
            'format' => 'medium',
            'align' => 'left',
            'searchable' => true,
        ));
        $grid->addColumn('expired_at', array(
            'header' => $this->__('失效时间'),
            'index' => 'expired_at',
            'type' => 'date',
            'format' => 'medium',
            'align' => 'left',
            'searchable' => true,
        ));

        $grid->addColumn('action', array(
            'header' => $this->__('操作'),
            'align' => 'left',
            'type' => 'action',
            'width' => '300px',
            'render' => 'getGiftProductUrl',
        ));

        $this->setChild('giftvoucher_grid', $grid);
        return $this;
    }

    /**
     * Get Gift Product Name by Product Id
     *
     * @param mixed $row
     * @return string
     * @author Juno Chen
     */
    public function getGiftProductName($row)
    {
        $productId = $row->getProductId();
        $productName = Mage::helper('giftvoucher')->getGiftProductNameById($productId);
        return $productName;
    }

    /**
     * Get Gift Product Url by Product Id
     *
     * @param mixed $row
     * @return string
     * @author Juno Chen
     */
    public function getGiftProductUrl($row)
    {
        $productId = $row->getProductId();
        $url = Mage::helper('giftvoucher')->getGiftProductUrlById($productId);

        $action = '<a href="' . $url . ' " target="_blank" >' . 
            $this->__('去使用') . '</a>';

        return $action;
    }

    /**
     * Get Gift Product ShortDescription by Product Id
     *
     * @param mixed $row
     * @return string
     * @author Juno Chen
     */
    public function getGiftProductShortDescription($row)
    {
        $productId = $row->getProductId();
        $shortDescription = Mage::helper('giftvoucher')->getGiftProductShortDescriptionById($productId);
        return $shortDescription;
    }
    
    /**
     * Get Gift Product Description by Product Id
     *
     * @param mixed $row
     * @return string
     * @author Juno Chen
     */
    public function getGiftProductDescription($row)
    {
        $productId = $row->getProductId();
        $description = Mage::helper('giftvoucher')->getGiftProductDescriptionById($productId);
        return $description;
    }
    
    /**
     * Get row number
     *
     * @param mixed $row
     * @return string
     */
    public function getNoNumber($row)
    {
        return sprintf('#%d', $row->getId());
    }

    /**
     * Returns the HTML codes of the gift code's column
     *
     * @param mixed $row
     * @return string
     */
    public function getCodeTxt($row)
    {
        $input = '<input style="width:auto;" id="input-gift-code' . $row->getId() . '" readonly type="text" class="input-text" value="' . 
            $row->getGiftCode() . '" onblur="hiddencode' . $row->getId() . '(this);">';
        $aelement = '<a href="javascript:void(0);" onclick="viewgiftcode' . $row->getId() . '()">' . 
            Mage::helper('giftvoucher')->getHiddenCode($row->getGiftCode()) . '</a>';
        $html = '<div id="inputboxgiftvoucher' . $row->getId() . '" >' . $aelement . '</div>
                <script type="text/javascript">
                    //<![CDATA[
                        function viewgiftcode' . $row->getId() . '(){
                            $(\'inputboxgiftvoucher' . $row->getId() . '\').innerHTML=\'' . $input . '\';
                            $(\'input-gift-code' . $row->getId() . '\').focus();
                        }
                        function hiddencode' . $row->getId() . '(el) {
                            $(\'inputboxgiftvoucher' . $row->getId() . '\').innerHTML=\'' . $aelement . '\';
                        }
                    //]]>
                </script>';
        return $html;
    }

    /**
     * Returns the formatted blance
     * 
     * @param mixed $row
     * @return string
     */
    public function getBalanceFormat($row)
    {
        $currency = Mage::getModel('directory/currency')->load($row->getCurrency());
        return $currency->format($row->getBalance());
    }

    /**
     * Returns the HTML codes of the action's column
     * 
     * @param mixed $row
     * @return string
     */
    public function getActions($row)
    {
        $confirmText = Mage::helper('giftvoucher')->__('Are you sure?');
        $removeurl = $this->getUrl('giftvoucher/index/remove', array('id' => $row->getId()));
        $redeemurl = $this->getUrl('giftvoucher/index/redeem', array('giftvouchercode' => $row->getGiftCode()));

        $action = '<a href="' . $this->getUrl('*/*/view', array('id' => $row->getId())) . '">' . 
            $this->__('View') . '</a>';
        // can print gift voucher when status is not used
        if ($row->getStatus() < Magestore_Giftvoucher_Model_Status::STATUS_DISABLED) {
            //Hai.Tran
            $action .= ' | <a href="javascript:void(0);" onclick="window.open(\'' . 
                $this->getUrl('*/*/print', array('id' => $row->getId())) . 
                '\',\'newWindow\', \'width=1000,height=700,resizable=yes,scrollbars=yes\')" >' . 
                $this->__('Print') . '</a>';
            if ($row->getRecipientName() && $row->getRecipientEmail() && ($row->getCustomerId() 
                == Mage::getSingleton('customer/session')->getCustomerId() || $row->getCustomerEmail() 
                == Mage::getSingleton('customer/session')->getCustomer()->getEmail())
            ) {
                $action .= ' | <a href="' . $this->getUrl('*/*/email', array('id' => $row->getId())) . '">' . 
                    $this->__('Email') . '</a>';
            }
        }
        // 
        $avaiable = Mage::helper('giftvoucher')
            ->canUseCode(Mage::getModel('giftvoucher/giftvoucher')->load($row->getVoucherId()));
        if (Mage::helper('giftvoucher')->getGeneralConfig('enablecredit') && $avaiable) {
            if ($row->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE 
                || ($row->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_USED 
                && $row->getBalance() > 0)) {
                $action .=' | <a href="javascript:void(0);" onclick="redeem' . $row->getId() . '()">' . 
                    $this->__('Redeem') . '</a>';
                $action .='<script type="text/javascript">
                    //<![CDATA[
                        function redeem' . $row->getId() . '(){
                            if (confirm(\'' . $confirmText . '\')){
                                setLocation(\'' . $redeemurl . '\');
                            }
                        }
                    //]]>
                </script>';
            }
        }
        $action .=' | <a href="javascript:void(0);" onclick="remove' . $row->getId() . '()">' . 
            $this->__('Remove') . '</a>';
        $action .='<script type="text/javascript">
                    //<![CDATA[
                        function remove' . $row->getId() . '(){
                            if (confirm(\'' . $confirmText . '\')){
                                setLocation(\'' . $removeurl . '\');
                            }
                        }
                    //]]>
                </script>';
        return $action;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('giftvoucher_pager');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('giftvoucher_grid');
    }

    protected function _toHtml()
    {
        $this->getChild('giftvoucher_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

    public function getBalanceAccount()
    {
        $store = Mage::app()->getStore();
        $creadit = Mage::getModel('giftvoucher/credit')->getCreditAccountLogin();
        $currency = Mage::app()->getStore()->getCurrentCurrency();

        return $currency->format($store->convertPrice($creadit->getBalance()));
    }

}
