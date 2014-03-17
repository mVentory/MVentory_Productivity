<?php

class ZetaPrints_MvProductivityPack_Helper_Data
  extends Mage_Core_Helper_Abstract {

  const ATTRIBUTE_CODE = 'media_gallery';
  const REVIEWER_GROUP_CODE = 'REVIEWER';

  private $_mediaBackend = array();

  public function rotate ($file, $angle) {
    $image = Mage::getModel('catalog/product_image');

    $image
      ->setBaseFile($file)
      ->setQuality(100)
      ->setKeepFrame(false)
      ->rotate($angle)
      ->saveFile();

    return $image->getNewFile();
  }

  public function remove ($file, $product) {
    if (!$product->getId())
      return;

    $attributes = $product
                    ->getTypeInstance(true)
                    ->getSetAttributes($product);

    if (!isset($attributes[self::ATTRIBUTE_CODE]))
      return;

    $gallery = $attributes[self::ATTRIBUTE_CODE];

    if (!$gallery->getBackend()->getImage($product, $file))
      return;

    $gallery
      ->getBackend()
      ->removeImage($product, $file);

    try {
      $product->save();
    } catch (Mage_Core_Exception $e) {
      return;
    }

    return true;
  }

  public function setMainImage ($file, $product) {
    if (!$product->getId())
      return;

    $attributes = $product
                    ->getTypeInstance(true)
                    ->getSetAttributes($product);

    if (!isset($attributes[self::ATTRIBUTE_CODE]))
      return;

    $gallery = $attributes[self::ATTRIBUTE_CODE];

    if (!$gallery->getBackend()->getImage($product, $file))
      return;

    $currentImage = $product->getImage();

    if ($currentImage)
      $gallery
        ->getBackend()
        ->updateImage($product, $currentImage, array('exclude' => false));

    $gallery
      ->getBackend()
      ->updateImage($product, $file, array('exclude' => true));

    $gallery
      ->getBackend()
      ->setMediaAttribute($product,
                array('image', 'small_image', 'thumbnail'),
                $file);

    try {
      $product->save();
    } catch (Mage_Core_Exception $e) {
      Mage::log($product);
      return;
    }

    return true;
  }

  public function updateImageInGallery ($oldFile, $newFile, $product,
                                        $mediaAttribute = null, $move = true,
                                        $exclude = false) {

    if (!$product->getId())
      return;

    $attributes = $product
                    ->getTypeInstance(true)
                    ->getSetAttributes($product);

    if (!isset($attributes[self::ATTRIBUTE_CODE]))
      return;

    $mediaGalleryAttribute = $attributes[self::ATTRIBUTE_CODE];

    $mediaGalleryAttribute
      ->getBackend()
      ->removeImage($product, $oldFile);

    $file = $mediaGalleryAttribute
              ->getBackend()
              ->addImage($product, $newFile, $mediaAttribute, $move, $exclude);

    $product->save();

    return $file;
  }

  public function add ($product, $data) {
    if (!$product->getId())
      return;

    $backend = $this->_getMediaBackend($product);

    $gallery = $product->getData(self::ATTRIBUTE_CODE);

    $mediaAttribute = null;

    if (!(isset($gallery['images']) && $gallery['images']))
      $mediaAttribute = array_keys($product->getMediaAttributes());

    $file = $backend->addImage(
              $product,
              $data['path'] . $data['file'],
              $mediaAttribute,
              true,
              false
            );

    $backend->addImage(
      $product,
      $data['path'] . $data['file'],
      $mediaAttribute,
      true,
      false
    );

    $product->save();

    $gallery = $product->getData(self::ATTRIBUTE_CODE);

    if (!(isset($gallery['images']) && $gallery['images']))
      return;

    $image = end($gallery['images']);

    if (!(isset($image['new_file'], $image['file'])
          && $image['new_file']
          && $image['file']))
      return;

    return $image['file'];
  }

  protected function _getMediaBackend ($product) {
    $id = $product->getId();

    if (isset($this->_mediaBackend[$id]))
      return $this->_mediaBackend[$id];

    $attributes = $product
                    ->getTypeInstance(true)
                    ->getSetAttributes($product);

    if (!isset($attributes[self::ATTRIBUTE_CODE]))
      return;

    $mediaGalleryAttribute = $attributes[self::ATTRIBUTE_CODE];
    $backend = $mediaGalleryAttribute->getBackend();

    $this->_mediaBackend[$id] = $backend;

    return $backend;
  }

  public function isAdminLogged () {
    return Mage::registry('is_admin_logged') === true;
  }

  public function isReviewerLogged () {
    if ($this->isAdminLogged())
      return true;

    $session = Mage::getSingleton('customer/session');

    $groupId = $session->getCustomerGroupId();

    if ($groupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
      return false;

    if ($session->getCustomer()->getWebsiteId()
          != Mage::app()->getWebsite()->getId())
      return false;

    $group = Mage::getModel('customer/group')->load($groupId);

    if (!$group->getId())
      return false;

    return strcasecmp($group->getCode(), self::REVIEWER_GROUP_CODE) == 0;
  }

  /**
   * Sends email to stores's general contant address
   *
   * @param string $subject
   * @param string $message
   * @param int|string|Mage_Core_Model_Store $store Store, its ID or code
   */
  public function sendEmail ($subject,
                             $message,
                             $store = Mage_Core_Model_App::ADMIN_STORE_ID) {

    $store = Mage::app()->getStore($store);

    $email = $store->getConfig('trans_email/ident_general/email');
    $name = $store->getConfig('trans_email/ident_general/name');

    Mage::getModel('core/email')
      ->setFromEmail($email)
      ->setFromName($name)
      ->setToName($name)
      ->setToEmail($email)
      ->setBody($message)
      ->setSubject($subject)
      ->send();
  }

  /**
   * List attributes likely to be shown on product page.
   * 
   * @param Mage_Catalog_Model_Product $product
   * @return array of Mage_Catalog_Model_Resource_Eav_Attribute
   */
  public function getVisibleAttributes($product) {
    $result = array();
    if (!$product) return $result;

    $attributes = $product->getAttributes();
    // these attrs are always shown somewhere even if not "visible on front"
    $result['name'] = $attributes['name'];
    $result['description'] = $attributes['description'];
    $result['price'] = $attributes['price'];

    foreach ($attributes as $attribute) {
      $code = $attribute->getAttributeCode();

      if ($attribute->getIsVisibleOnFront() || substr($code, -1) === '_')
        $result[$code] = $attribute;
    }

    return $result;
  }

}
