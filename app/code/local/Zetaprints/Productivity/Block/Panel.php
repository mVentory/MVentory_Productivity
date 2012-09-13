<?php
/**
 * Block for frontend top panel
 *
 * @category   Zetaprints_Productivity_Block_Panel
 * @package    Zetaprints_Productivity
 * @author     Zetaprints <anemets1@gmail.com>
 */ 
class Zetaprints_Productivity_Block_Panel extends Mage_Core_Block_Template {
   
    public function isAdmin() {   
		  return Mage::registry('is_admin_logged');
    }

}