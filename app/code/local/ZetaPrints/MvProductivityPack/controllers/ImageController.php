<?php

class ZetaPrints_MvProductivityPack_ImageController
  extends Mage_Core_Controller_Front_Action {

  const ATTRIBUTE_CODE = 'media_gallery';

  public function rotateAction () {
    $helper = Mage::helper('MvProductivityPack');

    if (!$helper->isReviewerLogged())
      return;

    $request = $this->getRequest();

    $hasRequiredParam = $request->has('params') && $request->has('productId');

    if (!$hasRequiredParam)
      return;

    $params = $request->get('params');
    $productId = $request->get('productId');

    $angels = array('left' => 90, 'right' => -90);

    $hasRequiredValues = $params['file']
                         && array_key_exists($params['rotate'], $angels);

    if (!$hasRequiredValues)
      return;

    //Export $file, $width, $height and $rotate variables
    extract($params);

    unset($params);

    // rotate image and get new file
    $newFileAbsolute = $helper->rotate($file, $angels[$rotate]);

    $type = $request->get('thumb') == 'true'?'thumbnail':'image';

    if($type == 'image') {
      // update main product image and get new base filename
      $file
        = $helper
            ->updateImageInGallery($file,
                                   $newFileAbsolute,
                                   $productId,
                                   array('image', 'small_image', 'thumbnail'));
    } else {
      $file = $helper
                ->updateImageInGallery($file, $newFileAbsolute, $productId);
    }

    $_product = Mage::getModel('catalog/product')->load($productId);

    // get resized version of image
    $image = Mage::helper('catalog/image')->init($_product, $type, $file)
               ->resize($width, $height)->__toString();

    $params = Zend_Json::encode(compact('file', 'width', 'height'));

    echo Zend_Json::encode(compact('image', 'params'));

    return Zend_Json::encode(true);
  }

  public function removeAction () {
    $helper = Mage::helper('MvProductivityPack');

    if (!$helper->isReviewerLogged())
      return;

    $request = $this->getRequest();

    $hasRequiredParam = $request->has('params') && $request->has('product');

    if (!$hasRequiredParam)
      return;

    $params = $request->get('params');
    $productId = (int) $request->get('product');

    $hasRequiredValues = $params['file']
                         && $productId >= 0;

    if (!$hasRequiredValues)
      return;

    //Export $file, $width and $height variables
    extract($params);

    unset($params);

    $helper->remove($file, $productId);

    if($request->get('thumb') != 'true') {
      $_product = Mage::getModel('catalog/product')
                    ->load($productId);
      $image = Mage::helper('catalog/image')->init($_product, 'image')
               ->resize($width, $height)->__toString();
      echo Zend_Json::encode(array('image'=>$image));
    }

    return Zend_Json::encode(true);
  }

  public function setmainAction () {
    $helper = Mage::helper('MvProductivityPack');

    if (!$helper->isReviewerLogged())
      return;

    $request = $this->getRequest();

    $hasRequiredParam = $request->has('params')
                        && $request->has('main_image_params')
                        && $request->has('product');

    if (!$hasRequiredParam)
      return;

    $thumb = $request->get('params');
    $image = $request->get('main_image_params');
    $productId = (int) $request->get('product');

    $hasRequiredValues = $thumb['file']
                         && $image['file']
                         && $productId >= 0;

    if (!$hasRequiredValues)
      return;

    $helper->setMainImage($thumb['file'], $productId);

    $_product = Mage::getModel('catalog/product')
                  ->load($productId);

    $thumbImage = Mage::helper('catalog/image')
                    ->init($_product, 'thumbnail', $image['file'])
                    ->resize($thumb['width'], $thumb['height'])
                    ->__toString();

    $mainImage = Mage::helper('catalog/image')
                   ->init($_product, 'image', $thumb['file'])
                   ->resize($image['width'], $image['height'])
                   ->__toString();

    $file = $thumb['file'];
    $thumb['file'] = $image['file'];
    $image['file'] = $file;

    $params = Zend_Json::encode($thumb);
    $main_image_params = Zend_Json::encode($image);

    $result = compact('thumbImage', 'mainImage', 'params', 'main_image_params');

    echo Zend_Json::encode($result);

    return Zend_Json::encode(true);
  }
}
