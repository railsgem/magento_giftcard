<?php

$installer = $this;

$installer->startSetup();

 $enableOption=array(
'group' => 'Maximum Quantity Amount',
'type' => 'int',
'label' => 'Enable Maximum Quantity Amount',
'input' => 'boolean',
'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
'visible' => true,
'source'  => 'eav/entity_attribute_source_boolean',
'required' => false, 
'searchable' => false,
'filterable' => false,
'comparable' => false,
'visible_on_front' =>true,
'unique' => false,
'apply_to' => '',


);

 $installer->addAttribute('catalog_product','maximum_quantity_enable',$enableOption);

$quantityOption=array(
'group' => 'Maximum Quantity Amount',
'type' => 'int',
'label' => 'Enter Maximum Quantity',
'input' => 'text',
'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
'visible' => true,
'required' => false, 
'searchable' => false,
'filterable' => false,
'comparable' => false,
'visible_on_front' =>true,
'unique' => false,
'apply_to' => '',


);

 $installer->addAttribute('catalog_product','maximum_quantity',$quantityOption);





$messageOption=array(
'group' => 'Maximum Quantity Amount',
'type' => 'text',
'label' => 'Message',
'input' => 'text',
'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
'visible' => true,
'required' => false, 
'searchable' => false,
'filterable' => false,
'comparable' => false,
'visible_on_front' =>true,
'unique' => false,
'apply_to' => '',


);

 $installer->addAttribute('catalog_product','maximum_quantity_message',$messageOption);







$installer->endSetup();