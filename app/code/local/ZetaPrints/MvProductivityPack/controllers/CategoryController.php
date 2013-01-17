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

	/**
	 * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in 
	 * registry 
	 */
	public function preDispatch () {
		Mage::helper('MvProductivityPack')->saveAdminState();

		return parent::preDispatch();

		return $this;
	}
	
	/* Get a url pointing to the normal version of the products list page (not the rss one) */
	private function getNoRssUrl()
	{
		$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		
		$result = str_replace("&rss=1", "", $currentUrl);
		$result = str_replace("rss=1&", "", $result);
		$result = str_replace("?rss=1", "", $result);
		
		return $result;
	}

	private function getRssDescriptionHtml($product, $thumbnailUrl, $largeImageUrl)
	{
		return  
		'<div>'."\n".
			'<a class="mv-rss-title" href="'.$product->getProductUrl().'" title="View the product">'.$product->getName().'</a>' ."\n".
			'<a class="mv-rss-preview" rel="'.$largeImageUrl.'" href="'.$product->getProductUrl().'" title="'.$product->getName().'">' ."\n".
				'<img src="'.$thumbnailUrl.'" alt="'.$product->getName().'" title="'.$product->getName().'"/>' ."\n".
			'</a>'."\n".
			'<div class="mv-rss-price">'.Mage::helper('core')->currency($product->getPrice(),true,false).'</div>'."\n".
		'</div>';
	}
	
	public function viewAction()
	{
		parent::viewAction();
		
		if ($this->getRequest()->getParam("rss", 0) == 1)
		{
			$collection = $this->getLayout()->getBlock("product_list")->getLoadedProductCollection();

			$rssXml = new SimpleXMLElement('<rss xmlns:media="' . $this->_mediaNamespace . '"/>');
			$rssXml->registerXPathNamespace('media', $this->_mediaNamespace); 
			$rssXml->addAttribute('version', '2.0');
			
			$pubDate = date("D, d M o G:i:s T",time());
			
			$channel = $rssXml->addChild('channel');
			$channel->title = $this->getLayout()->getBlock("head")->getTitle();
			$channel->link = $this->getNoRssUrl();
			$channel->addChild("description");
			$channel->pubDate = $pubDate;
			$channel->lastBuildDate = $pubDate;
			$channel->generator = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			
			foreach($collection as $product)
			{
				//TODO: hardcode image sizes for now
				$thumbnailUrl = Mage::helper('catalog/image')->init($product, 'small_image')->resize(215, 170);
				$largeImageUrl = Mage::helper('catalog/image')->init($product, 'small_image')->resize(300, 300);
				
				$item = $channel->addChild('item');
				$item->title = $product->getName();
				$item->link = $product->getProductUrl();
				$item->description = $this->getRssDescriptionHtml($product, $thumbnailUrl, $largeImageUrl);
				$item->addChild("pubDate");
				$item->addChild("author");
				$item->addChild("guid");
				$mediaContent = $item->addChild("content", "", $this->_mediaNamespace);
				$item->addChild("title", htmlspecialchars($product->getName()), $this->_mediaNamespace);
				$mediaThumbnail = $item->addChild("thumbnail", "", $this->_mediaNamespace);

				$mediaContent->addAttribute("url", $largeImageUrl);
				$mediaThumbnail->addAttribute("url", $thumbnailUrl);

				//TODO: hardcode image sizes for now
				$mediaContent->addAttribute("width", 300);
				$mediaContent->addAttribute("height", 300);

				$mediaThumbnail->addAttribute("width", 215);
				$mediaThumbnail->addAttribute("height", 170);
			}

			//Format the response			
			$dom = new DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($rssXml->asXML());

			$this->getResponse()->setBody($dom->saveXML());

			$apiConfigCharset = Mage::getStoreConfig("api/config/charset");
			$this->getResponse()->setHeader('Content-Type','application/rss+xml; charset='.$apiConfigCharset);
		}
	}
	
}

