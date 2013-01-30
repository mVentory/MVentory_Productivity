<?php

class ZetaPrints_MvProductivityPack_Helper_Rss
  extends Mage_Core_Helper_Abstract {

  const MEDIA_NS = 'http://search.yahoo.com/mrss/';

  public function generateFeedForProducts ($products, $data) {
    $thumbWidth = isset($data['thumb']['width'])
                    ? $data['thumb']['width'] : null;
    $thumbHeight = isset($data['thumb']['height'])
                     ? $data['thumb']['height'] : null;
    $imageWidth = isset($data['image']['width'])
                    ? $data['image']['width'] : null;
    $imageHeight = isset($data['image']['height'])
                     ? $data['image']['height'] : null;

    $xml = new SimpleXMLElement('<rss xmlns:media="' . self::MEDIA_NS . '"/>');

    $xml->registerXPathNamespace('media', self::MEDIA_NS);
    $xml->addAttribute('version', '2.0');

    $date = date('D, d M o G:i:s T',time());

    $channel = $xml->addChild('channel');

    $channel->title = $data['title'];
    $channel->link = $data['link'];
    $channel->addChild('description');
    $channel->pubDate = $date;
    $channel->lastBuildDate = $date;
    $channel->generator = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

    foreach($products as $product) {
      $name = $product->getName();

      $thumbUrl = Mage::helper('catalog/image')
                    ->init($product, 'small_image')
                    ->resize($thumbWidth, $thumbHeight)
                    ->__toString();

      $imageUrl = Mage::helper('catalog/image')
                    ->init($product, 'small_image')
                    ->resize($imageWidth, $imageHeight)
                    ->__toString();

      $item = $channel->addChild('item');

      $item->title = $name;
      $item->link = $product->getProductUrl();
      $item->description = $this->_getProductAttributes($product);
      $item->addChild('pubDate');
      $item->addChild('author');
      $item->addChild('guid');

      $item->addChild('title', htmlspecialchars($name), self::MEDIA_NS);

      $mediaContent = $item->addChild('content', '', self::MEDIA_NS);

      $mediaContent->addAttribute('url', $imageUrl);

      if (!is_null($imageWidth))
        $mediaContent->addAttribute('width', $imageWidth);

      if (!is_null($imageHeight))
        $mediaContent->addAttribute('height', $imageHeight);

      $mediaThumb = $item->addChild('thumbnail', '', self::MEDIA_NS);

      $mediaThumb->addAttribute('url', $thumbUrl);

      if (!is_null($thumbWidth))
        $mediaThumb->addAttribute('width', $thumbWidth);

      if (!is_null($thumbHeight))
        $mediaThumb->addAttribute('height', $thumbHeight);

      $price = Mage::helper('core')
                 ->currency($product->getPrice(), true, false);

      $item
        ->addChild('price', '', self::MEDIA_NS)
        ->addAttribute('price', $price);
    }

    return $this->formatXml($xml->asXML());
  }

  public function formatXml ($xml) {
    $dom = new DOMDocument('1.0');

    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml);

    return $dom->saveXML();
  }

  protected function _getProductAttributes ($product) {
    Mage::register('product', $product->load($product->getId()));

    $_attrs = $this
                ->getLayout()
                ->createBlock('catalog/product_view_attributes')
                ->getAdditionalData();

    Mage::unregister('product');

    $attrs = '';

    foreach ($_attrs as $_attr)
      $attrs .= $_attr['label'] . ': ' . $_attr['value'] . '<br />';

    return substr($attrs, 0, -6);
  }
}
