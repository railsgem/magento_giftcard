<?php

$installer = $this;

$installer->startSetup();

 $enableOption=array(
'group' => 'Maximum Quantity Per Customer',
'type' => 'int',
'label' => 'Enable Maximum Quantity Per Customer',
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

 $installer->addAttribute('catalog_product','maximum_qty_per_cst_enable',$enableOption);

$quantityOption=array(
'group' => 'Maximum Quantity Per Customer',
'type' => 'int',
'label' => 'Enter Maximum Quantity Per Customer',
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

 $installer->addAttribute('catalog_product','maximum_qty_per_cst',$quantityOption);





$messageOption=array(
'group' => 'Maximum Quantity Per Customer',
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

 $installer->addAttribute('catalog_product','maximum_qty_per_cst_message',$messageOption);







$installer->endSetup();