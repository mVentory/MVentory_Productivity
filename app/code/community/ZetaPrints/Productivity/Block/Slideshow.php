<?php
/**
 * Universal slideshow block
 *
 * @category ZetaPrints_Productivity_Block_Slideshow
 * @package  ZetaPrints_Productivity
 * @author ZetaPrints
 */
class ZetaPrints_Productivity_Block_Slideshow
  extends Mage_Catalog_Block_Product_Abstract
  implements Mage_Widget_Block_Interface {

  protected function _construct () {
    parent::_construct();

    $this
      ->addData(array(
        'cache_lifetime' => 86400,
        'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG),
      ));
  }

  public function getCacheKeyInfo () {
    return array(
      'CATALOG_PRODUCT_LATEST',
      Mage::app()->getStore()->getId(),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      'template' => $this->getData('item_template'),
      'image_size' => $this->getData('image_size'),
      $this->getProductsCount()
    );
  }

  public function getProductCollection () {
    $collection = $this->getData('product_collection');

    if ($collection)
      return $collection;

    $visibility = Mage::getSingleton('catalog/product_visibility')
                    ->getVisibleInCatalogIds();

    $imageFilter = array('nin' => array('no_selection', ''));

    $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(array(
                       'name',
                       'special_price',
                       'special_from_date',
                       'special_to_date'
                      ))
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
    $coreHelper = Mage::helper('core');

    $search = array('%name%', '%price%', '%price-block%', '%url%', '%img%');

    $store = Mage::app()->getStore();
    $locale = Mage::app()->getLocale();

    $statements = array(
      'sale' => function ($body, $product) use ($locale, $store) {
        $specialPrice = $product->getSpecialPrice();
        $hasSpecial = $specialPrice !== null
                      && $specialPrice !== false
                      && $locale->isStoreDateInInterval(
                           $store,
                           $product->getSpecialFromDate(),
                           $product->getSpecialToDate()
                         );

        return $hasSpecial ? $body : '';
      }
    );

    $html = '';

    foreach ($this->getProductCollection() as $product) {
      $replace = array(
        $product->getName(),
        $coreHelper->currency($product->getPrice(), true, false),
        $this->getPriceHtml($product),
        $product->getProductUrl(),
        (string) $helper->init($product, 'small_image')->resize($width, $height)
      );

      $html .= preg_replace_callback(
        '/%if:(?<statement>.+)%(?<body>.+)%end:\k<statement>%/is',
        function ($matches) use ($statements, $product) {
          $statement = trim($matches['statement']);

          if (!isset($statements[$statement]))
            return '';

          $func = $statements[$statement];

          return $func($matches['body'], $product);
        },
        str_replace($search, $replace, $template)
      );
    }

    return $html;
  }
}
