<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License BY-NC-ND.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material,
 * you may not distribute the modified material.
 * See the full license at http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * See http://mventory.com/legal/licensing/ for other licensing options.
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2015 mVentory Ltd. (http://mventory.com)
 * @license http://creativecommons.org/licenses/by-nc-nd/4.0/
 */

/**
 * Block for displaying build version in system config
 *
 * @package MVentory/Productivity
 * @author Andrew Gilman <andrew@mventory.com>
 */

class MVentory_Productivity_Block_Config_Buildinfo
  extends Mage_Adminhtml_Block_Abstract
  implements Varien_Data_Form_Element_Renderer_Interface
{
  protected $_template = 'productivity/config/build-info.phtml';

  /**
   * Render fieldset html
   *
   * @param Varien_Data_Form_Element_Abstract $element
   * @return string
   */
  public function render (Varien_Data_Form_Element_Abstract $element) {
    return $this->toHtml();
  }
}

