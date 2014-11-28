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


class MagentoPush_GuestManagement_Block_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('guestCustomerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare grid collection object
     *
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_address_collection');

        $collection->getSelect()
            ->reset()
            ->from(array("main_table" => $collection->getMainTable()), array())
            ->columns('email')
            ->columns('firstname')
            ->columns('lastname')
            ->columns('telephone')
            ->columns('postcode')
            ->columns('country_id')
            ->columns('region')
            ->distinct(true);

        $collection->getSelect()->where(
            'main_table.email <> ?', ''
        );

        $collection->getSelect()->where(
            'main_table.firstname <> ?', ''
        );

        $collection->getSelect()->where(
            'main_table.lastname <> ?', ''
        );

        $collection->getSelect()->where(
            'main_table.address_type = ?', 'billing'
        );
        
        // we should get only users which are not registered yet
        $entity = Mage::getModel('customer/customer')->getResource();
        $attribute = Mage::getSingleton('eav/config')
            ->getCollectionAttribute($entity->getType(), 'email');

        $collection->getSelect()->where(
            'main_table.email NOT IN (?)',
            new Zend_Db_Expr("SELECT email FROM `".$attribute->getBackendTable()."`")
        );

        // echo  $collection->getSelect()->__toString();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('guestmanagement')->__('First Name'),
            'width'     => '140',
            'index'     => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('guestmanagement')->__('Last Name'),
            'width'     => '140',
            'index'     => 'lastname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('guestmanagement')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('guestmanagement')->__('Telephone'),
            'width'     => '100',
            'index'     => 'telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('guestmanagement')->__('ZIP'),
            'width'     => '90',
            'index'     => 'postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('guestmanagement')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('guestmanagement')->__('State/Province'),
            'width'     => '100',
            'index'     => 'region',
        ));


        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('guestmanagement')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getEmail',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('guestmanagement')->__('Register'),
                        'url'       => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/guestmanagement/register'),
                        'field'     => 'email'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }
}
