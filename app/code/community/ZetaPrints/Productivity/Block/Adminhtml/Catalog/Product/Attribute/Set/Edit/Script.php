<?php

/**
 * Productivity
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  ZetaPrints
 * @package   ZetaPrints_Productivity
 * @copyright Copyright (c) 2014 ZetaPrints Ltd. (http://www.zetaprints.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Block Attribute Set edit template script
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   anemets1@gmail.com
 */

class ZetaPrints_Productivity_Block_Adminhtml_Catalog_Product_Attribute_Set_Edit_Script
  extends Mage_Core_Block_Template {

  public function getJson() {
    $a = array();
    $collection = Mage::getResourceModel('catalog/product_attribute_collection');
    foreach($collection as $attribute) {
      $a[$attribute->getData('attribute_code')] = $attribute->getData('attribute_id');;
    }

    return Zend_Json::encode($a);
  }

}