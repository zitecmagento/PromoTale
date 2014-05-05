<?php

class Zitec_Promotale_Adminhtml_PromotaleController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Customers list action
     */
    public function dashboardAction()
    {
        $this->_title($this->__('Promotale Dashboard'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('adminhtml/promotale/dashboard');

//        /**
//         * Append customers block to content
//         */
//        $this->_addContent(
//            $this->getLayout()->createBlock('adminhtml/customer', 'customer')
//        );

        /**
         * Add breadcrumb item
         */
//        $this->_addBreadcrumb(Mage::helper('zitec_branding')->__('Customers'), Mage::helper('zitec_branding')->__('Branding'));
//        $this->_addBreadcrumb(Mage::helper('zitec_branding')->__('Manage Customers'), Mage::helper('zitec_branding')->__('Manage Branding'));

        $this->renderLayout();
    }

}
