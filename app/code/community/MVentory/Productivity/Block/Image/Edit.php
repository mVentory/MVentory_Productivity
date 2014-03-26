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
 * Block image editing panel
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Image_Edit
  extends Mage_Core_Block_Template {

  public function setImageSize ($width = 0, $height = 0) {
    $width = ($width = (int) $width) ? $width : null;
    $height = ($height = (int) $height) ? $height : null;

    return $this->setData('image_size', compact('width', 'height'));
  }

  public function setThumbSize ($width = 0, $height = 0) {
    $width = ($width = (int) $width) ? $width : null;
    $height = ($height = (int) $height) ? $height : null;

    return $this->setData('thumb_size', compact('width', 'height'));
  }

  protected function _toHtml () {
    if (Mage::helper('productivity')->isReviewerLogged())
      return parent::_toHtml();
  }
}
