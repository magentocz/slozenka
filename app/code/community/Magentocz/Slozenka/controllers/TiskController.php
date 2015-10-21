<?php

/** 
 * Magento CZ Module.
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
 */
/**
 * @category   Magentocz
 */
class Magentocz_Slozenka_TiskController extends Mage_Adminhtml_Controller_Action
{
    public function pdfSlozenkaAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            $flag = true;
            $pdf = Mage::getModel('magentocz_slozenka/order_pdf_slozenka')->getPdf($orderIds);
            if ($flag) {
                return $this->_prepareDownloadResponse('slozenka'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            } else {
                $this->_getSession()->addError($this->__('There are no printable remittances related to selected orders'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
}
