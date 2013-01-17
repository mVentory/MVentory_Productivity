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
				$image = Mage::helper('catalog/image')->init($product, 'small_image')->resize($thumbnailWidth, $thumbnailHeight);
				$thumbnailUrl = "" . $image;
				
				$image = Mage::helper('catalog/image')->init($product, 'small_image')->resize($fullImageWidth, $fullImageHeight);
				$largeImageUrl = "" . $image;
				
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

				if (!is_null($fullImageWidth)) {
					$mediaContent->addAttribute("width", $fullImageWidth);
				}
				if (!is_null($fullImageHeight)) {
					$mediaContent->addAttribute("height", $fullImageHeight);
				}

				if (!is_null($thumbnailWidth)) {
					$mediaThumbnail->addAttribute("width", $thumbnailWidth);
				}
				if (!is_null($thumbnailHeight)) {
					$mediaThumbnail->addAttribute("height", $thumbnailHeight);
				}
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

