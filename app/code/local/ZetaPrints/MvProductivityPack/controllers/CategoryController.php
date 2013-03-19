<?php
/**
 * Rewrite the CategoryController to check the admin login on store front
 *
 * @category   Mage_Catalog_CategoryController
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */
require_once("Mage/Catalog/controllers/CategoryController.php");
class ZetaPrints_MvProductivityPack_CategoryController extends Mage_Catalog_CategoryController
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

  public function viewAction () {
    parent::viewAction();

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

}

