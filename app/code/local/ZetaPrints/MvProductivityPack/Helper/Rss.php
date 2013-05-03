<?php

class ZetaPrints_MvProductivityPack_Helper_Rss
  extends Mage_Core_Helper_Abstract {

  const MEDIA_NS = 'http://search.yahoo.com/mrss/';

  const IMAGE_WIDTH = 300;
  const IMAGE_HEIGHT = 300;

  const THUMB_WIDTH = 75;
  const THUMB_HEIGHT = 75; 

  public function generateFeedForProducts ($products, $params) {
    $currency = Mage::app()
                  ->getStore(null)
                  ->getCurrentCurrency();

    $formatParams = array('display'=>Zend_Currency::NO_SYMBOL);

    $defaults = array(
      'images' => array(
        'main' => array(
          'width' => self::IMAGE_WIDTH,
          'height' => self::IMAGE_HEIGHT
        ),
        'thumb' => array(
          'width' => self::THUMB_WIDTH,
          'height' => self::THUMB_HEIGHT
        )
      )
    );

    $params['images'] = array_merge($defaults['images'], $params['images']);
    $params = array_merge($defaults, $params);

    $params['images']['content'] = $params['images']['main'];
    $params['images']['thumbnail'] = $params['images']['thumb'];

    unset($params['images']['main']);
    unset($params['images']['thumb']);

    $helper = Mage::helper('catalog/image');

    $xml = new SimpleXMLElement('<rss xmlns:media="' . self::MEDIA_NS . '"/>');

    $xml->registerXPathNamespace('media', self::MEDIA_NS);
    $xml->addAttribute('version', '2.0');

    $date = date('D, d M o G:i:s T',time());

    $channel = $xml->addChild('channel');

    $channel->title = $params['title'];
    $channel->link = $params['link'];
    $channel->addChild('description');
    $channel->pubDate = $date;
    $channel->lastBuildDate = $date;
    $channel->generator = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

    foreach($products as $product) {
      $name = $product->getName();

      $item = $channel->addChild('item');

      $item->title = $name;
      $item->link = $product->getProductUrl();
      $item->description = $this->_getProductAttributes($product);
      $item->addChild('pubDate');
      $item->addChild('author');
      $item->addChild('guid');

      $item->addChild('title', htmlspecialchars($name), self::MEDIA_NS);

      foreach ($params['images'] as $tag => $data) {
        $child = $item->addChild($tag, '', self::MEDIA_NS);

        $url = $helper->init($product, 'small_image');

        if (count($data) == 2) {
          $url->resize($data['width'], $data['height']);

          foreach (array('width', 'height') as $p)
            if ($data[$p])
              $child->addAttribute($p, $data[$p]);
        }

        $child->addAttribute('url', $url->__toString());
      }

      //We assume that current currency and base currency are same.
      //I.e. no currency convertion in the store
      $price = $currency->format($product->getPrice(), $formatParams, false);

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

  public function addFeedToHeader ($url, $title = null) {
    $head = $this
              ->getLayout()
              ->getBlock('head');

    if (!$head)
      return;

    if (!$title)
      $title = $head->getTitle();

    $head->addItem('rss', $url, 'title="' . $title . '"');
  }

  public function getLayout () {
    $layout = parent::getLayout();

    if ($layout)
      return $layout;

    $layout = Mage::app()
                ->getFrontController()
                ->getAction()
                ->getLayout();

    parent::setLayout($layout);

    return $layout;
  }

  protected function _getProductAttributes ($product) {
    Mage::register('product', $product->load($product->getId()));

    $_attrs = $this
                ->getLayout()
                ->createBlock('catalog/product_view_attributes')
                ->getAdditionalData(array(), false);

    Mage::unregister('product');

    $attrs = '';

    foreach ($_attrs as $_attr)
      $attrs .= $_attr['label'] . ': ' . $_attr['value'] . '<br />';

    return substr($attrs, 0, -6);
  }
}
