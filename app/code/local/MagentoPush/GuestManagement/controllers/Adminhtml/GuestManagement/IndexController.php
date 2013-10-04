<?php
/**
 *  Copyright 2013 MagentoPush
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @category    MagentoPush
 *  @package     MagentoPush_GuestManagement
 */

class MagentoPush_GuestManagement_Adminhtml_GuestManagement_IndexController extends Mage_Adminhtml_Controller_Action {

    const ERROR = "Cant find customer with this email address %s.";

    public function indexAction()
    {
        $this->_title($this->__('GuestManagement'))->_title($this->__('List'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function registerAction()
    {
        $email = $this->getRequest()->getParam('email');
        if($email){
            $email = preg_replace("#\"|'#", "", $email);
            $coll = Mage::getModel('sales/order_address')->getCollection();
            $select = $coll->getSelect();
            $select->where(
                'main_table.email = ?', $email
            );

            $select->where(
                'main_table.firstname <> ?', ''
            );

            $select->where(
                'main_table.lastname <> ?', ''
            );

            $select->where(
                'main_table.address_type = ?', 'billing'
            );

            $address = $coll->getFirstItem();
            if($address){
                Mage::getModel('guestmanagement/guest')->doRegister($address);
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('guestmanagement')
                        ->__(self::ERROR, $email)
                );
            }
        }
        $this->_redirect('*/*/');
    }




}