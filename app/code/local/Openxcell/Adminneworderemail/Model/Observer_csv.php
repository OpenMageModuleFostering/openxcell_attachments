<?php

class Openxcell_Adminneworderemail_Model_Observer
{
    const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'sales_email/order/admin_email_template';
    
    public function onepageCheckoutSuccess($observer){
        // $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $orderId = $observer->getData('order')->getId();
        $this->sendAdminOrderNotification($orderId);
    }
    
    public function sendAdminOrderNotification($orderId) {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        $mailTemplate = Mage::getModel('core/email_template');        
        $template = Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_TEMPLATE, $this->getStoreId());
        $adminEmail = '';
        $adminName = '';
        //DebugBreak();
        /*$_order = Mage::getModel('sales/order')->load($orderId);
        $billing_data = $_order->getBillingAddress()->getData();
        $shipping_data = $_order->getShippingAddress()->getData();
        $string = "InvoiceID,IP Address,Date,Firstname,Lastname,Company Name,Address,City,State,PostCode,Country,Shipping Name,Shipping Address,Shipping City,Shipping State,Shipping PostCode,Shipping Country,Shipping Method,Transaction ID,Card Type,Sku Number,Items,Quantity,Price,Item Total,Tax Rate,Sub Total,Freight,Tax,Discount,Total\n";
        foreach($_order->getAllItems() as $itemId => $item){
            $_order = Mage::getModel('sales/order')->load($orderId);
            $invoiceId = $_order->getData('increment_id');
            $ip = $_order->getData('remote_ip');
            $date = $_order->getData('created_at');
            $shippingMethod = $_order->getData('shipping_method');
            $subtotal = $_order->getGrandTotal() - 11;
            $shippingAmount = 11;
            $discount = $_order->getData('discount_amount');
            $grand = $_order->getGrandTotal();
            $street = explode("\n", $billing_data['street']);
            $streetShipping = explode("\n", $shipping_data['street']);
            $billing_address = str_replace(",",";",$street[0]);
            $shipping_address = str_replace(",",";",$streetShipping[0]);
            $itemPrice = $item->getPrice() + $item->getData('tax_amount');
            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());
            $taxClassId = $_product->getTaxClassId();
            $taxClass = Mage::getModel('tax/class')->load($taxClassId);
            $taxClassName = $taxClass->getClassName();
            if($taxClassName == 'GST'){
                $taxRate = 10;
            }
            else{
                $taxRate = 0;
            }
            
            $string.= $invoiceId . ',' . $ip . ',' . $date . ',' . $billing_data['firstname'] .  ',' . $billing_data['lastname'] .  ',' .$billing_data['company'] . ',' . $billing_address . ',' . $billing_data['city'] . ',' . $billing_data['region'] .',' . $billing_data['postcode'] . ','. $billing_data['country_id'] . ',' . $shipping_data['firstname'] .',' . $shipping_address . ',' . $shipping_data['city'] .',' .$shipping_data['region'] .',' . $shipping_data['postcode'] . ','. $shipping_data['country_id'] . ',' . $shippingMethod . ','. "" .','. "" . ',' .$item->getSku() . ',' . $item->getName() . ','. $item->getQtyOrdered() . ',' . $itemPrice . ',' . $itemPrice*$item->getQtyToInvoice() . ',' . $taxRate . ',' . $subtotal . ',' . $shippingAmount . ',' . Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount') . ',' . $discount . ',' . $grand ."\n";
        }*/

	$_order = Mage::getModel('sales/order')->load($orderId);
        $billing_data = $_order->getBillingAddress()->getData();
        $shipping_data = $_order->getShippingAddress()->getData();
        $string = "InvoiceID,IP Address,Date,Firstname,Lastname,Company Name,Address,City,State,PostCode,Country,Shipping Name,Shipping Address,Shipping City,Shipping State,Shipping PostCode,Shipping Country,Shipping Method,Transaction ID,Card Type,Sku Number,Items,Quantity,Price,Item Total,Tax Rate,Sub Total,Freight,Tax,Discount,Total\n";
        foreach($_order->getAllItems() as $itemId => $item){
            $_order = Mage::getModel('sales/order')->load($orderId);
            $invoiceId = $_order->getData('increment_id');
            $ip = $_order->getData('remote_ip');
            $date = $_order->getData('created_at');
            $shippingMethod = $_order->getData('shipping_method');
            $subtotal = $_order->getGrandTotal() - 11;
            $shippingAmount = 11;
            $discount = $_order->getData('discount_amount');
            $grand = $_order->getGrandTotal();
            //$street = explode("\n", $billing_data['street']);
            $street = str_replace("\n","|", $billing_data['street']);
            $streetShipping = str_replace("\n","|", $shipping_data['street']);
            $billing_address = str_replace(",",";",$street);
            $shipping_address = str_replace(",",";",$streetShipping);
            $itemPrice = $item->getPrice() + $item->getData('tax_amount');
            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());
            $taxClassId = $_product->getTaxClassId();
            $taxClass = Mage::getModel('tax/class')->load($taxClassId);
            $taxClassName = $taxClass->getClassName();
            if($taxClassName == 'GST'){
                $taxRate = 10;
            }
            else{
                $taxRate = 0;
            }
            
            $string.= $invoiceId . ',' . $ip . ',' . $date . ',' . $billing_data['firstname'] .  ',' . $billing_data['lastname'] .  ',' .$billing_data['company'] . ',' . $billing_address . ',' . $billing_data['city'] . ',' . $billing_data['region'] .',' . $billing_data['postcode'] . ','. $billing_data['country_id'] . ',' . $shipping_data['firstname'] .',' . $shipping_address . ',' . $shipping_data['city'] .',' .$shipping_data['region'] .',' . $shipping_data['postcode'] . ','. $shipping_data['country_id'] . ',' . $shippingMethod . ','. "" .','. "" . ',' .$item->getSku() . ',' . $item->getName() . ','. $item->getQtyOrdered() . ',' . $itemPrice . ',' . $itemPrice*$item->getQtyOrdered() . ',' . $taxRate . ',' . $subtotal . ',' . $shippingAmount . ',' . Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount') . ',' . $discount . ',' . $grand ."\n";
        }


        $paymentBlock = Mage::helper('payment')->getInfoBlock($_order->getPayment())->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($_order->getStore()->getId());
        
        $adminEmailString = Mage::getStoreConfig('sales_email/order/admin_email_notify');
        $adminEmailArray = explode(',', $adminEmailString);
        
        foreach ($adminEmailArray as $adminEmail){
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))->getMail()->createAttachment($string,'text/UTF-8')->filename = 'order.csv';
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional($template,
                    Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $this->getStoreId()),
                    $adminEmail,
                    $adminName,
                    array(
                        'order'            => $_order,
                        'payment_html'    => $paymentBlock->toHtml(),
                    )
                );
    }
        
        $translate->setTranslateInline(true);
    }
    
    public function getStoreId(){
        return Mage::app()->getStore()->getId();
    }
}