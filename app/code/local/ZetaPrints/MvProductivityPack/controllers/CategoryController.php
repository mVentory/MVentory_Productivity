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

  /**
   * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in
   * registry
   */
  public function preDispatch () {
    Mage::helper('MvProductivityPack')->saveAdminState();

    return parent::preDispatch();

    return $this;
  }

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

  public function viewAction()
  {
    parent::viewAction();

    if ($this->getRequest()->getParam($this->_rssGetVariable, 0) == 1)
    {
      /* Default image values */
      $thumbnailWidth = 215;
      $thumbnailHeight = 170;

      $fullImageWidth = 300;
      $fullImageHeight = 300;

      $thumbnailSize = $this->getRequest()->getParam($this->_thumbnailSizeGetVariable);
      $fullImageSize = $this->getRequest()->getParam($this->_fullImageSizeGetVariable);

      if (!is_null($thumbnailSize))
      {
        if (strcmp($thumbnailSize, "full") == 0)
        {
          $thumbnailWidth = null;
          $thumbnailHeight = null;
        }
        else
        {
          $wh = explode("x", $thumbnailSize);
          if (count($wh)==2) {
            $thumbnailWidth = strlen($wh[0])>0?$wh[0]:null;
            $thumbnailHeight = strlen($wh[1])>0?$wh[1]:null;
          }
        }
      }

      if (!is_null($fullImageSize))
      {
        if (strcmp($fullImageSize, "full") == 0)
        {
          $fullImageWidth = null;
          $fullImageHeight = null;
        }
        else
        {
          $wh = explode("x", $fullImageSize);
          if (count($wh)==2) {
            $fullImageWidth = strlen($wh[0])>0?$wh[0]:null;
            $fullImageHeight = strlen($wh[1])>0?$wh[1]:null;
          }
        }
      }

      $collection = $this->getLayout()->getBlock("product_list")->getLoadedProductCollection();

      $data = array(
        'title' => $this->getLayout()->getBlock("head")->getTitle(),
        'link' => $this->getNoRssUrl(),
        'image' => array(
          'width' => $fullImageWidth,
          'height' => $fullImageHeight
        ),
        'thumb' => array(
          'width' => $thumbnailWidth,
          'height' => $thumbnailHeight
        )
      );

      $feed = Mage::helper('MvProductivityPack/rss')
                ->setLayout($this->getLayout())
                ->generateFeedForProducts($collection, $data);

      $this->getResponse()->setBody($feed);

      $apiConfigCharset = Mage::getStoreConfig("api/config/charset");
      $this->getResponse()->setHeader('Content-Type','application/rss+xml; charset='.$apiConfigCharset);
    }
  }

}

