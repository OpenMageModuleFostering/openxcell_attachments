<?php

class Openxcell_Adminneworderemail_Model_Observer
{
    const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'sales_email/order/admin_email_template';
    
    public function onepageCheckoutSuccess($observer){
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        //$orderId = $observer->getData('order')->getId();
        $this->sendAdminOrderNotification($orderId);
    }
    
    public function sendAdminOrderNotification($orderId) {
        //DebugBreak();
        $type = Mage::getStoreConfig('sales_email/order/admin_email_type');
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        $mailTemplate = Mage::getModel('core/email_template');        
        $template = Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_TEMPLATE, $this->getStoreId());
        $adminEmail = '';
        $adminName = '';
        $oid = Mage::getModel("sales/order")->getCollection()->getLastItem()->getId();
        $_order = Mage::getModel('sales/order')->load($oid);
        $billing_data = $_order->getBillingAddress()->getData();
        $shipping_data = $_order->getShippingAddress()->getData();
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
        $billing_address1 = str_replace(",",";",$street[0]);
        $billing_address2 = str_replace(",",";",$street[1]);
        $shipping_address1 = str_replace(",",";",$streetShipping[0]);
        $shipping_address2 = str_replace(",",";",$street[1]);
        $num_date1 = str_replace("-","",$date);
        $num_date = str_replace(":","",$num_date1);
        if($type == "xml"){     //Condition for XML file
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <!DOCTYPE OrderList SYSTEM "http://foo.com/">
                <OrderList StoreAccountName="magento">
                    <Order currency="USD" id="'. $invoiceId .'">
                        <Time>'. $date .'</Time>
                        <NumericTime>'. $num_date .'</NumericTime>
                        <AddressInfo type="ship">
                            <Name>
                                <First>'.$shipping_data["firstname"].'</First>
                                <Last>'.$shipping_data["lastname"].'</Last>
                                <Full>'.$shipping_data["firstname"]." ".$shipping_data["lastname"].'</Full>
                           </Name>
                           <Company>'.$shipping_data["company"].'</Company>
                           <Address1>'.$shipping_address1.'</Address1>
                           <Address2>'.$shipping_address2.'</Address2>
                           <City>'.$shipping_data["city"].'</City>
                           <State>'.$shipping_data["region"].'</State>
                           <Country>'.$shipping_data["country_id"].'</Country>
                           <Zip>'.$shipping_data['postcode'].'</Zip>
                           <Phone>'.$shipping_data['telephone'].'</Phone>
                           <Email>'.$shipping_data['email'].'</Email>
                        </AddressInfo>
                        <AddressInfo type="bill">
                            <Name>
                                <First>'.$billing_data["firstname"].'</First>
                                <Last>'.$billing_data["lastname"].'</Last>
                                <Full>'.$billing_data["firstname"]." ".$billing_data["lastname"].'</Full>
                            </Name>
                            <Company>'.$billing_data["company"].'</Company>
                            <Address1>'.$billing_address1.'</Address1>
                            <Address2>'.$billing_address2.'</Address2>
                            <City>'.$billing_data["city"].'</City>
                            <State>'.$billing_data["region"].'</State>
                            <Country>'.$billing_data["country_id"].'</Country>
                            <Zip>'.$billing_data['postcode'].'</Zip>
                            <Phone>'.$billing_data['telephone'].'</Phone>
                            <Email>'.$billing_data['email'].'</Email>
                        </AddressInfo>
                        <IPAddress>'.$ip.'</IPAddress>
                        <Shipping>'.$shippingMethod.'</Shipping>';
        
        
        $i=0;
        foreach($_order->getAllItems() as $itemId => $item){
            //$item = Mage::getModel('catalog/product')->load($itemId);
            $itemPrice = $item->getPrice();
            if($item->getData('tax_amount') > 0){
                $tax = 'YES';
            }
            else{
                $tax = 'NO';
            }
               
            $string.='<Item num="'.$i.'">
                            <Code>'.$item->getSku().'</Code>
                            <Quantity>'.$item->getQtyToInvoice().'</Quantity>
                            <Unit-Price>'.$itemPrice.'</Unit-Price>
                            <Taxable>'.$tax.'</Taxable>
                            
                        </Item>';
                        $i++;

        }
        $string.='<Total>
                           <Line type="Subtotal" name="Subtotal">'.$subtotal.'</Line>
                           <Line type="Shipping" name="Shipping">'.$shippingAmount.'</Line>
                           <Line type="Tax" name="Tax">'.$_order->getShippingTaxAmount().'</Line>
                           <Line type="Total" name="Total">'.$grand.'</Line>
                        </Total>
                        <Space-Id></Space-Id>
                </Order>
            </OrderList>';
        } else{   //For CSV
            $string = "InvoiceID,IP Address,Date,Firstname,Lastname,Company Name,Address,City,State,PostCode,Country,Shipping Name,Shipping Address,Shipping City,Shipping State,Shipping PostCode,Shipping Country,Shipping Method,Transaction ID,Card Type,Sku Number,Items,Quantity,Price,Item Total,Sub Total,Freight,Tax,Discount,Total\n";
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
                /*if($taxClassName == 'GST'){
                    $taxRate = 10;
                }
                else{
                    $taxRate = 0;
                }*/
            
            $string.= $invoiceId . ',' . $ip . ',' . $date . ',' . $billing_data['firstname'] .  ',' . $billing_data['lastname'] .  ',' .$billing_data['company'] . ',' . $billing_address . ',' . $billing_data['city'] . ',' . $billing_data['region'] .',' . $billing_data['postcode'] . ','. $billing_data['country_id'] . ',' . $shipping_data['firstname'] .',' . $shipping_address . ',' . $shipping_data['city'] .',' .$shipping_data['region'] .',' . $shipping_data['postcode'] . ','. $shipping_data['country_id'] . ',' . $shippingMethod . ','. "" .','. "" . ',' .$item->getSku() . ',' . $item->getName() . ','. $item->getQtyOrdered() . ',' . $itemPrice . ',' . $itemPrice*$item->getQtyOrdered() . ',' . $subtotal . ',' . $shippingAmount . ',' . Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount') . ',' . $discount . ',' . $grand ."\n";
        }
        }
        $paymentBlock = Mage::helper('payment')->getInfoBlock($_order->getPayment())->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($_order->getStore()->getId());
        
        $adminEmailString = Mage::getStoreConfig('sales_email/order/admin_email_notify');
        $adminEmailArray = explode(',', $adminEmailString);
        
        foreach ($adminEmailArray as $adminEmail){
            if($type == "xml"){
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))->getMail()->createAttachment($string,'text/UTF-8')->filename = 'order.xml'; } else {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))->getMail()->createAttachment($string,'text/UTF-8')->filename = 'order.csv'; }
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
