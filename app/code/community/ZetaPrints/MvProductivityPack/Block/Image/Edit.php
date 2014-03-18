<?php

/**
 * Block for frontend top panel
 *
 * @category ZetaPrints_MvProductivityPack_Block_Panel
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */
class ZetaPrints_MvProductivityPack_Block_Image_Edit
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
    if (Mage::helper('MvProductivityPack')->isReviewerLogged())
      return parent::_toHtml();
  }
}
