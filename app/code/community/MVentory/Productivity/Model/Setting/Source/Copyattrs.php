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
 * Source model for 'Copy from configurable to simple products' option
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Model_Setting_Source_Copyattrs {

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray () {
    $helper = Mage::helper('productivity');

    return array(
      array(
        'value' => 'name',
        'label' => $helper->__('Name')
      ),
      array(
        'value' => 'description',
        'label' => $helper->__('Description')
      ),
      array(
        'value' => 'short_description',
        'label' => $helper->__('Short Description')
      )
    );
  }
}
