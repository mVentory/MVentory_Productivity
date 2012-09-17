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

  public function getImageEditorHtml ($file) {
		if (!Mage::helper('MvProductivityPack')->isAdminLogged())
			return;

    return $this
		->getLayout()
		->createBlock('core/template')
		->setTemplate('catalog/product/view/media/mvproductivity_editor.phtml')
		->setImage($file)
		->toHtml();
  }
}
