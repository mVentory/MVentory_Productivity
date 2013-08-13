<?php
/**
 * Universal slideshow block
 *
 * @category ZetaPrints_MvProductivityPack_Block_Slideshow
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */
class ZetaPrints_MvProductivityPack_Block_Slideshow
  extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

  //protected function _prepareLayout() {
  //  $paths = $this->getData('frontend_js');

  //  $baseUrl = Mage::getBaseUrl('js');

  //  $start = '<script type="text/javascript" src="' . Mage::getBaseUrl('js');
  //  $end = '"></script>';

  //  $data = '';

  //  foreach (explode(';', $paths) as $path)
  //    $data .= $start . $path . $end;

  //  $layout = $this->getLayout();

  //  $layout
  //    ->getBlock('before_body_end')
  //    ->append($layout->createBlock('core/text')->setText($data));

  //  return $this;
  //}

  public function getProductCollection () {
    $collection = $this->getData('product_collection');

    if ($collection)
      return $collection;

    $visibility = Mage::getSingleton('catalog/product_visibility')
                    ->getVisibleInCatalogIds();

    $imageFilter = array('nin' => array('no_selection', ''));

    $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addPriceData()
                    ->addUrlRewrite()
                    ->addAttributeToFilter('small_image', $imageFilter)
                    ->setVisibility($visibility)
                    ->addStoreFilter()
                    ->setPageSize($this->getProductsCount())
                    ->setCurPage(1);
    $collection
      ->getSelect()
      ->order(new Zend_Db_Expr('RAND()'));

    $this->setData('product_collection', $collection);

    return $collection;
  }

  protected function _toHtml () {
    $template = $this->getData('item_template');
    list($width, $height) = explode('x', $this->getData('image_size'));

    $helper = Mage::helper('catalog/image');

    $search = array('%name%', '%price%', '%url%', '%img%');

    $html = '<ul>';

    foreach ($this->getProductCollection() as $product) {
      $replace = array(
        $product->getName(),
        $product->getPrice(),
        $product->getProductUrl(),
        (string) $helper->init($product, 'small_image')->resize($width, $height)
      );

      $html .= str_replace($search, $replace, $template);
    }

    return $html . '</ul>';
  }
}
