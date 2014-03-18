<?php
/**
 * Rewrite the CategoryController to check the admin login on store front
 *
 * @category   Mage_Catalog_CategoryController
 * @package    ZetaPrints_Productivity
 * @author     ZetaPrints <anemets1@gmail.com>
 */
require_once("Mage/Catalog/controllers/CategoryController.php");
class ZetaPrints_Productivity_CategoryController extends Mage_Catalog_CategoryController
{
  protected $_mediaNamespace = 'http://search.yahoo.com/mrss/';
  protected $_rssGetVariable = 'rss';
  protected $_thumbnailSizeGetVariable = 'thumbnail_size';
  protected $_fullImageSizeGetVariable = 'fullimage_size';

  private function removeqsvar($url, $varname) {
    list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
    parse_str($qspart, $qsvars);
    unset($qsvars[$varname]);
    $newqs = http_build_query($qsvars);
    return $urlpart . (strlen($newqs)>0?('?'.$newqs):"");
  }

  /* Get a url pointing to the normal version of the products list page (not the rss one) */
  private function getNoRssUrl()
  {
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();

    $result = $this->removeqsvar($currentUrl, $this->_rssGetVariable);
    $result = $this->removeqsvar($result, $this->_thumbnailSizeGetVariable);
    $result = $this->removeqsvar($result, $this->_fullImageSizeGetVariable);

    return $result;
  }

  public function topAction()
  {
    $categoryModel = Mage::getModel('catalog/category');
    $list = Mage::helper('catalog/category')->getStoreCategories();

    $rssXml = new SimpleXMLElement('<rss/>');
    $rssXml->addAttribute('version', '2.0');

    $pubDate = date("D, d M o G:i:s T",time());

    $channel = $rssXml->addChild('channel');
    $channel->title = "Top categories per store";
    $channel->addChild("description");
    $channel->pubDate = $pubDate;
    $channel->lastBuildDate = $pubDate;
    $channel->generator = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

    foreach ($list as $cat)
    {
      $url = $categoryModel->setData($cat->getData())->getUrl();

      $item = $channel->addChild('item');
      $item->title = $cat->getName();
      $item->link = $url;
    }

    $helper = Mage::helper('MvProductivityPack/rss');

    $this->getResponse()->setBody($helper->formatXml($rssXml->asXML()));

    $apiConfigCharset = Mage::getStoreConfig("api/config/charset");
    $this->getResponse()->setHeader('Content-Type','application/rss+xml; charset='.$apiConfigCharset);
  }

  public function viewAction ($categoryId = null) {
    $this->_viewAction($categoryId);

    $request = $this->getRequest();

    if ($request->getParam($this->_rssGetVariable, 0) != 1)
      return;

    $layout = $this->getLayout();

    $data = array(
      'title' => $layout
                   ->getBlock('head')
                   ->getTitle(),
      'link' => $this->getNoRssUrl(),
      'images' => array()
    );

    $map = array(
      'main' => 'fullimage_size',
      'thumb' => 'thumbnail_size'
    );

    foreach ($map as $key => $param) {
      if (!$request->has($param))
        continue;

      $size = $request->getParam($param);

      if ($size == 'full') {
        $data['images'][$key] = array();

        continue;
      }

      $wh = explode('x', $size);

      if (count($wh) == 2) {
        $data['images'][$key] = array(
          'width' => is_numeric($wh[0]) ? (int) $wh[0] : null,
          'height' => is_numeric($wh[1]) ? (int) $wh[1] : null
        );

        continue;
      }
    }

    $collection = $layout
                    ->getBlock("product_list")
                    ->getLoadedProductCollection();

    $feed = Mage::helper('MvProductivityPack/rss')
              ->setLayout($layout)
              ->generateFeedForProducts($collection, $data);

    $response = $this
                  ->getResponse()
                  ->setBody($feed);

    $charset = Mage::getStoreConfig("api/config/charset");

    $response
      ->setHeader('Content-Type','application/rss+xml; charset=' . $charset);
  }

  public function allAction () {
    return $this->viewAction(Mage::app()->getStore()->getRootCategoryId());
  }

  /**
   * Category view action
   *
   * The method is redefined to allow passing category ID
   * via function parameters
   */
  private function _viewAction ($categoryId = null) {
    $categoryId = (int) $categoryId;

    if ($categoryId)
      $this->getRequest()->setParam('id', $categoryId);

    if (!$category = $this->_initCatagory()) {
      if (!$this->getResponse()->isRedirect())
        $this->_forward('noRoute');

      return;
    }

    $categoryId = $category->getId();

    $design = Mage::getSingleton('catalog/design');
    $settings = $design->getDesignSettings($category);

    //Apply custom design
    if ($customDesign = $settings->getCustomDesign())
      $design->applyCustomDesign($customDesign);

    Mage::getSingleton('catalog/session')->setLastViewedCategoryId($categoryId);

    $layout = $this->getLayout();

    $update = $layout->getUpdate();
    $update->addHandle('default');

    if (!$category->hasChildren())
      $update->addHandle('catalog_category_layered_nochildren');

    $this->addActionLayoutHandles();
    $update->addHandle($category->getLayoutUpdateHandle());
    $update->addHandle('CATEGORY_' . $categoryId);

    $this->loadLayoutUpdates();

    //Apply custom layout update once layout is loaded
    $layoutUpdates = $settings->getLayoutUpdates();

    if ($layoutUpdates && is_array($layoutUpdates))
        foreach($layoutUpdates as $layoutUpdate)
          $update->addUpdate($layoutUpdate);

    $this->generateLayoutXml()->generateLayoutBlocks();

    //Apply custom layout (page) template once the blocks are generated
    if ($pageLayout = $settings->getPageLayout()) {
      $layout->helper('page/layout')->applyTemplate($pageLayout);
    }

    if ($root = $layout->getBlock('root'))
      $root
        ->addBodyClass('categorypath-' . $category->getUrlPath())
        ->addBodyClass('category-' . $category->getUrlKey());

    $this->_initLayoutMessages('catalog/session');
    $this->_initLayoutMessages('checkout/session');

    $this->renderLayout();
  }
}

