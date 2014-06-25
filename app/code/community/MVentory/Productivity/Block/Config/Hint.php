<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Block for system config hint
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

class MVentory_Productivity_Block_Config_Hint
  extends Mage_Adminhtml_Block_Abstract
  implements Varien_Data_Form_Element_Renderer_Interface
{
  protected $_template = 'productivity/config/hint.phtml';

  /**
   * Render fieldset html
   *
   * @param Varien_Data_Form_Element_Abstract $element
   * @return string
   */
  public function render(Varien_Data_Form_Element_Abstract $element) {
    return $this->toHtml();
  }
}
