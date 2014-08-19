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
 * Source model for product fields to copy
 *
 * @package MVentory/Productivity
 * @author Mihail Kozlovsky <kozloffsky@gmail.com>
 */
class MVentory_Productivity_Model_Setting_Source_Copyfields {

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray () {
    $helper = Mage::helper('productivity');

    return array(
        array(
            'value' => MVentory_Productivity_Model_Config::PRODUCT_FIELD_NAME,
            'label' => $helper->__('Name')
        ),
        array(
            'value' => MVentory_Productivity_Model_Config::PRODUCT_FIELD_DESCRIPTION,
            'label' => $helper->__('Description')
        )
    );
  }
}

