<?php

/**
 * Simple product data view
 *
 * @category   ZetaPrints
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <???@zetaprints.com>
 */
class ZetaPrints_MvProductivityPack_Block_Product_View_Media
  extends Mage_Catalog_Block_Product_View_Media {

  public function getImageEditorHtml ($file, $width = null, $height = null) {
    if (!Mage::helper('MvProductivityPack')->isAdminLogged())
      return;

    $params = Zend_Json::encode(compact('file', 'width', 'height'));

    $template = 'catalog/product/view/media/mvproductivity_editor.phtml';

    return $this
             ->getLayout()
             ->createBlock('core/template')
             ->setTemplate($template)
             ->setImageParameters($params)
             ->toHtml();
  }
}
