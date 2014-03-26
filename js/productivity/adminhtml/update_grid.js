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
 * @author <anemets1@gmail.com>
 */

function addMiddleClick() {
  $$('table.data tbody tr').each(function(item) {
    item.stopObserving('click');
    item.observe('mousedown', function(e) {
      if(Event.isMiddleClick(e)) {
        window.open(item.readAttribute('title'), '_blank');
      }
    });
    item.observe('click', function(e) {
      if(Event.isLeftClick(e)) {
        document.location.href = item.readAttribute('title');
      }
    });
  });
}

Event.observe(window, 'load', addMiddleClick);