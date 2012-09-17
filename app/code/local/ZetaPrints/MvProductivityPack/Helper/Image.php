<?php

class ZetaPrints_MvProductivityPack_Helper_Image extends Mage_Catalog_Helper_Image
{
    public function __toString()
    {
        try {
            if( $this->getImageFile() ) {
                $this->_getModel()->setBaseFile( $this->getImageFile() );
            } else {         
                if (in_array($this->_getModel()->getDestinationSubdir(), array('image', 'small_image')) && $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) == 'no_selection' && Mage::getModel('catalog/product')->load($this->getProduct()->getId())->getData('bk_thumbnail_')) {
                    return Mage::getModel('catalog/product')->load($this->getProduct()->getId())->getData('bk_thumbnail_') . '&zoom=' . ($this->_getModel()->getDestinationSubdir() == 'image' ? 1 : 5);
                } else {
                    $this->_getModel()->setBaseFile( $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) );
                }
            }

            if( $this->_getModel()->isCached() ) {
                return $this->_getModel()->getUrl();
            } else {
                if( $this->_scheduleRotate ) {
                    $this->_getModel()->rotate( $this->getAngle() );
                }

                if ($this->_scheduleResize) {
                    $this->_getModel()->resize();
                }

                if( $this->getWatermark() ) {
                    $this->_getModel()->setWatermark($this->getWatermark());
                }

                $url = $this->_getModel()->saveFile()->getUrl();
            }
        } catch( Exception $e ) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }
}
