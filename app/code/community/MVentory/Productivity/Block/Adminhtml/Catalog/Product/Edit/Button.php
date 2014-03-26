<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Block for product Frontshop Preview button
 *
 * @package MVentory/Productivity
 * @author <anemets1@gmail.com>
 */
class MVentory_Productivity_Block_Adminhtml_Catalog_Product_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get product frontend url
   */
  public function getProductUrl()
  {
    return Mage::registry('product')->getProductUrl();
  }
}
