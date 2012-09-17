<?php
/**
 * Block for frontend top panel
 *
 * @category   ZetaPrints_MvProductivityPack_Block_Panel
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */ 
class ZetaPrints_MvProductivityPack_Block_Panel extends Mage_Core_Block_Template {
   
    public function isAdmin() {   
		  return Mage::registry('is_admin_logged');
    }

}