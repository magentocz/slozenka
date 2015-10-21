<?php
/** 
* Magento CZ Module
* 
* NOTICE OF LICENSE 
* 
* This source file is subject to the Open Software License (OSL 3.0) 
* that is bundled with this package in the file LICENSE.txt. 
* It is also available through the world-wide-web at this URL: 
* http://opensource.org/licenses/osl-3.0.php 
* If you did of the license and are unable to 
* obtain it through the world-wide-web, please send an email 
* to magentocz@gmail.com so we can send you a copy immediately. 
* 
* @copyright Copyright (c) 2015 GetReady s.r.o. (https://getready.cz)
* 
*/
/**
 *
 * @category   Magentocz
 * @package    Magentocz_Slozenka
 */
class Magentocz_Slozenka_Model_Order_Pdf_Slozenka extends Mage_Sales_Model_Order_Pdf_Abstract
{
	
    public function getPdf($orderIds = array())
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $type = 'invoice';
		$node = Mage::getConfig()->getNode('global/pdf/'.$type);
        foreach ($node->children() as $renderer) {
            $this->_renderers[$renderer->getName()] = array(
                'model'     => (string)$renderer,
                'renderer'  => null
            );
        }
	
    	$pdf = new Zend_Pdf();
        
    	for ($a=0;$a<count($orderIds);$a++) 
    	{
    		
    		$order = Mage::getModel('sales/order')->load($orderIds[$a]);
    	    $orderStoreId = $order->getStore()->getId();
    	    
    		if ($orderStoreId) {
                Mage::app()->getLocale()->emulate($orderStoreId);
            }
    	    
    		//get payment method settings for current store
    	    $allowedPaymentMethods = explode(',', (string)Mage::getStoreConfig('sales_pdf/slozenka/allow_paymentmethods',$orderStoreId));
    		//check if order has right payment method and so if it is allowed for print remmitance
    	    if(in_array($order->getPayment()->getMethodInstance()->getCode(),$allowedPaymentMethods) == false)
    			continue;    		
    		
    		
    			
    		$page = new Zend_Pdf_Page(589.61, 286.3);
        	$style = new Zend_Pdf_Style();
        	$style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        	$pdf->pages[$a] = $page;
    

        	$page->setFont(Zend_Pdf_Font::fontWithPath(Mage::getBaseDir()."/lib/CourierFont/courier.ttf"), 12);
        	
        	
			/* delete template */
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
			$page->drawRectangle(0, 0, 589, 286, $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			
			
            /* Grand Total */
				
            $order_grandtotal = $order->getGrandTotal();
            list($og_before, $og_after) = explode(".", $order_grandtotal); //odstrani desetinou tecku
			
            if(Mage::getStoreConfig('sales_pdf/slozenka/print_whole_amount',$orderStoreId) == 0)
            	$og_After = "--"; //pouze dve desetinna mista
			else 
				$og_After = substr($og_after,0,2); //vezme prvni cisla 
				
            $og_Before = str_pad($og_before, 8, "-", STR_PAD_LEFT); //doplni "-" na zacatek
			$og = $og_Before . $og_After;
  	
	        $x = 350;
            
			//letter spacing
			for ($i=0;$i<strlen($og);$i++) {
	            $page->drawText($og[$i], $x, 246, 'UTF-8');
	            $x += 14;
            }

            $payee = Mage::getSingleton('magentocz_slozenka/payee');
            
            // FIXME
            /* Owner Bank Account Number */	            	
            $bank_account = Mage::getStoreConfig('sales_pdf/slozenka/merchant_account_number',$orderStoreId);
            $bank_account_prefix = Mage::getStoreConfig('sales_pdf/slozenka/merchant_account_number_prefix',$orderStoreId);
            $bank_Account = $bank_account_prefix . $bank_account;
		    $bank_code = Mage::getStoreConfig('sales_pdf/slozenka/merchant_account_bank_code',$orderStoreId); // spolecne s VS
		    $konst_symbol = "";
            
			//letter spacing
			$x = 350;
		    for ($i=0;$i<strlen($bank_Account);$i++) {
	            $page->drawText($bank_Account[$i], $x, 222, 'UTF-8');
	            $x += 14;
            }

			/* Order ID = Variable symbol + Bank Code */   		
		    $vs = (string)$order->getRealOrderId();
		    	    
		    $index = strpos($vs,'-');
		    if($index !== false)
		    {		    	
		    	$vs = substr($vs,0,$index);
		    }
		    
			$bc_vs = $bank_code . "  " . " " . $vs; // vlozeni mezer pro spravne odsazeni
		 
			//letter spacing
			$x = 350;
			for ($i=0;$i<strlen($bc_vs);$i++) {
	            $page->drawText($bc_vs[$i], $x, 198, 'UTF-8');
	            $x += 14;
            }  
		   
		    /* Konst Symbol */
		    $customer_id = $order->getCustomerId();
		    $customer_id = str_pad($customer_id, 10, " ", STR_PAD_LEFT);
		    
		    $ci_ks = $konst_symbol . "  " . $customer_id; // vlozeni mezer pro spravne odsazeni
		    
			//letter spacing
			$x = 350;
			for ($i=0;$i<strlen($ci_ks);$i++) {
	            //$page->drawText($ci_ks[$i], $x, 175, 'UTF-8');
	            $x += 14;
            }

			/* Billing Address */
            $billing = $order->getShippingAddress();
			$first = $billing->getFirstname();
			$last = $billing->getLastname();
			$street = $billing->getStreet(1);
			$city = $billing->getCity();
			$postcode = $billing->getPostcode();
		
		    //letter spacing
		    $x = 350;
        	$chars = preg_split('//u', $first, -1, PREG_SPLIT_NO_EMPTY);
		    for ($i=0;$i<count($chars);$i++) {
	            $page->drawText($chars[$i], $x, 137, 'UTF-8');
	            $x += 14;
        	}
        	$x = 350;
        	$chars = preg_split('//u', $last, -1, PREG_SPLIT_NO_EMPTY);
		    for ($i=0;$i<count($chars);$i++) {
	            $page->drawText($chars[$i], $x, 116, 'UTF-8');
	            $x += 14;
        	}
        
        $page->setFont(Zend_Pdf_Font::fontWithPath(Mage::getBaseDir()."/lib/CourierFont/courier.ttf"), 10);
        
        	$x = 350;
        	$chars = preg_split('//u', $street, -1, PREG_SPLIT_NO_EMPTY);
		    for ($i=0;$i<count($chars);$i++) {
	            $page->drawText($chars[$i], $x, 96, 'UTF-8');
	            $x += 10;
        	}
        	$x = 350;
        	$chars = preg_split('//u', $city, -1, PREG_SPLIT_NO_EMPTY);
		    for ($i=0;$i<count($chars);$i++)
		     {
	            $page->drawText($chars[$i], $x, 77, 'UTF-8');
	            $x += 10;
        	}
        	
        $page->setFont(Zend_Pdf_Font::fontWithPath(Mage::getBaseDir()."/lib/CourierFont/courier.ttf"), 12);
        
        	$x = 350;
        	$chars = preg_split('//u', $postcode, -1, PREG_SPLIT_NO_EMPTY);
		    for ($i=0;$i<count($chars);$i++) 
		    {
	            $page->drawText($chars[$i], $x, 57, 'UTF-8');
	            $x += 14;
        	}

			/* Owner Adress */
			$page->setFont(Zend_Pdf_Font::fontWithPath(Mage::getBaseDir()."/lib/CourierFont/courier.ttf"), 8);

			// FIXME
		  	$owner["name"] = Mage::getStoreConfig('sales_pdf/slozenka/merchant_name',$orderStoreId);
	    	$owner["street"] = Mage::getStoreConfig('sales_pdf/slozenka/merchant_street',$orderStoreId);
	    	$owner["city"] =  Mage::getStoreConfig('sales_pdf/slozenka/merchant_city',$orderStoreId);
		
        	$page->drawText($owner["name"], 185, 170, 'UTF-8');
        	$page->drawText($owner["street"], 185, 155, 'UTF-8');
        	$page->drawText($owner["city"], 185, 139, 'UTF-8');

			/* Left Part of Remittance */
        	
        	$page->drawText("--" . $og_before . "--", 25, 173, 'UTF-8');
        	$page->drawText($og_After, 150, 173, 'UTF-8');
        	
        			/* Grand Total In Words */	
                    $bas = Array("", Mage::helper('slozenka')->__('jedna'),Mage::helper('slozenka')->__('dva'),
                    		Mage::helper('slozenka')->__('tři'), Mage::helper('slozenka')->__('čtyři'), Mage::helper('slozenka')->__('pět'),
                    		Mage::helper('slozenka')->__('šest'),Mage::helper('slozenka')->__('sedm'),Mage::helper('slozenka')->__('osm'),
                    		Mage::helper('slozenka')->__('devět'),Mage::helper('slozenka')->__('deset'),Mage::helper('slozenka')->__('jedenáct'),
                    		Mage::helper('slozenka')->__('dvanáct'),Mage::helper('slozenka')->__('třináct'),Mage::helper('slozenka')->__('čtrnáct'),
                    		Mage::helper('slozenka')->__('patnáct'),Mage::helper('slozenka')->__('šestnáct'),
                    		Mage::helper('slozenka')->__('sedmnáct'),Mage::helper('slozenka')->__('osmnáct'),
                    		Mage::helper('slozenka')->__('devatenáct'));
					$des = Array("", "", Mage::helper('slozenka')->__('dvacet'),Mage::helper('slozenka')->__('třicet'),
							Mage::helper('slozenka')->__('čtyřicet'),Mage::helper('slozenka')->__('padesát'),
							Mage::helper('slozenka')->__('šedesát'),Mage::helper('slozenka')->__('sedmdesát'),
							Mage::helper('slozenka')->__('osmdesát'),Mage::helper('slozenka')->__('devadesát'));
					$sta = Array("",Mage::helper('slozenka')->__('sto'),Mage::helper('slozenka')->__('dvěstě'),
							Mage::helper('slozenka')->__('třista'),Mage::helper('slozenka')->__('čtyřista'),
							Mage::helper('slozenka')->__('pětset'),Mage::helper('slozenka')->__('šestset'),
							Mage::helper('slozenka')->__('sedmset'),Mage::helper('slozenka')->__('osmset'),
							Mage::helper('slozenka')->__('devětset'));
					$rad = Array(Mage::helper('slozenka')->__('milion'),Mage::helper('slozenka')->__('tisíc'), "",
							Mage::helper('slozenka')->__('miliony'),Mage::helper('slozenka')->__('tisíce'),
							"",Mage::helper('slozenka')->__('miliony'),Mage::helper('slozenka')->__('tisíc'), "");
        	
        	
					/*$bas = Array("", "jedna", "dva", "ti", "čtyři", "pět", "šest", "sedm", "osm", "devět", "deset", "jedenáct", "dvanáct", "třináct", "čtrnáct", "patnáct", "šestnáct", "sedmnáct", "osmnáct", "devatenáct");
					$des = Array("", "", "dvacet", "třicet", "čtyřicet", "padesát", "šedesát", "sedmdesát", "osmdesát", "devadesát");
					$sta = Array("", "sto", "dvěstě", "třista", "čtyřista", "pětset", "šestset", "sedmset", "osmset", "devětset");
					$rad = Array("milion", "tisíc", "", "miliony", "tisíce", "", "miliony", "tisíc", "");*/

					$ns = "000000000".strval($og_before);
					$nsf = str_split(substr($ns, -9), 3);
	
					for($i = 0; $i < 3; $i++){
						$bas[1] = $i < 2 ? Mage::helper('slozenka')->__("jeden") : Mage::helper('slozenka')->__("jedna");
						$ix = intval($nsf[$i]) >= 2 && intval($nsf[$i]) <= 4 ? $i + 3 : (intval($nsf[$i]) > 4 ? $i + 6 : $i);
						$tx[$i] = $bas[intval(substr($nsf[$i], -1))];
						$tx[$i] = intval(substr($nsf[$i], -2, 1)) == 1 ? $bas[intval(substr($nsf[$i], -2))] : $des[intval(substr($nsf[$i], -2, 1))].$tx[$i];
						$tx[$i] = $sta[intval(substr($nsf[$i], -3, 1))].$tx[$i];
						if($tx[$i]){ $tx[$i] .= $rad[$ix]; }
					}
	 
	 				$page->drawText("--" . $tx[0].$tx[1].$tx[2] . "--", 25, 156, 'UTF-8');
	 				$page->drawText($og_After, 150, 143, 'UTF-8');
	 				
	 				/* Owner Adress amd Bank Account */
	 				$page->drawText($owner["name"], 25, 122, 'UTF-8');
        			$page->drawText($owner["street"], 25, 108, 'UTF-8');
        			$page->drawText($owner["city"], 25, 94, 'UTF-8');
        			$page->drawText($bank_account_prefix."-".$bank_account . "/" . $bank_code, 43, 80, 'UTF-8');
        			$page->drawText($vs, 43, 65, 'UTF-8');
        	
        	/* Billing Adress */
        	$page->drawText($first . " " . $last, 25, 35, 'UTF-8');
        	$page->drawText($street, 25, 22, 'UTF-8');
        	$page->drawText($city . " " . $postcode, 25, 9, 'UTF-8');
        	
    		if ($orderStoreId)
    		{
                Mage::app()->getLocale()->revert();
            }

    }


        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    

        return $pdf;
    }
}