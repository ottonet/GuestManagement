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

class MagentoPush_GuestManagement_Model_Guest extends Mage_Core_Model_Abstract {

    const ERROR = "An error has occured while process customer data.";
    const SUCCESS = "A new customer has been registered successfully.";
    const EMAIL_SENT = "The customer has been emailed about registration.";

    public function doRegister($address = null){
        if(!is_null($address)){
            if($address instanceof Mage_Sales_Model_Order_Address && $address->getParentId()){
                $order = Mage::getModel('sales/order')
                    ->load($address->getParentId());
                $websiteId = Mage::getModel('core/store')
                    ->load($order->getStoreId())->getWebsiteId();
            } else {
                $websiteId = Mage::app()->getStore()->getWebsiteId();
            }

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($address->getEmail());
            if(!$customer->getId()){
                // create a new customer
                $customer->setEmail($address->getEmail());
                $customer->setFirstname($address->getFirstname());
                $customer->setLastname($address->getLastname());
                // website_id has been cleaned up when loadByEmail failed
                $customer->setWebsiteId($websiteId);

                if((Mage::getStoreConfig('customer/guestmanagement/generate_pass') == 0
                        || Mage::getStoreConfig('customer/guestmanagement/generate_pass') == null)
                    && Mage::getStoreConfig('customer/guestmanagement/default_pass') != null){
                    $password = Mage::getStoreConfig('customer/guestmanagement/default_pass');

                } else {
                    $password = Mage::helper('guestmanagement')->generatePassword();
                }

                $customer->setPassword($password);

                // setConfirmation to null because we
                // do not want to check if the password
                // was entered two times and matched
                $customer->setConfirmation(null);
                try {
                    $customer->save();
                    $this->saveBilling($customer, $address);

                    if(Mage::getStoreConfig('customer/guestmanagement/confirmation') == 1){
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('guestmanagement')
                                ->__(self::EMAIL_SENT)
                        );
                        $customer->sendNewAccountEmail();
                    }

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('guestmanagement')
                            ->__(self::SUCCESS)
                    );
                }
                catch (Exception $ex) {
                    Mage::log("Exception in  ".__CLASS__."::".__METHOD__."() ".$ex->getMessage(),
                        null, 'speroteck.log');
                }

            }
        }
    }

    private function saveBilling($customer, $address){

        $street = $address->getStreet();
        $addressData = array (
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => array (
                '0' => $street[0],
                '1' => (isset($street[1])) ? $street[1] : '',
            ),
            'city' => $address->getCity(),
            'region_id' => '',
            'region' => $address->getRegion(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId(),
            'telephone' => $address->getTelephone(),
        );
        $customAddress = Mage::getModel('customer/address');
        $customAddress->setData($addressData)
            ->setCustomerId($customer->getId())
            ->setIsDefaultBilling('1')
            ->setSaveInAddressBook('1');
        try {
            $customAddress->save();
        }
        catch (Exception $ex) {
            Mage::log("Exception in  ".__CLASS__."::".__METHOD__."() ".$ex->getMessage(),
                null, 'speroteck.log');
        }
    }

}
?>