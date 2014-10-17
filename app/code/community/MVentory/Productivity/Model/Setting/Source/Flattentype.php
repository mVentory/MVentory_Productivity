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
 * Source model for type of flatten category tree
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Model_Setting_Source_Flattentype {

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray () {
    $helper = Mage::helper('productivity');

    return array(
      array(
        'value' => MVentory_Productivity_Model_Config::FLATTEN_NO,
        'label' => $helper->__('No')
      ),
      array(
        'value' => MVentory_Productivity_Model_Config::FLATTEN_PATHS,
        'label' => $helper->__('Flatten single branch paths')
      ),
      array(
        'value' => MVentory_Productivity_Model_Config::FLATTEN_EXPAND,
        'label' => $helper->__('Show children of hidden parents')
      )
    );
  }
}

?>
