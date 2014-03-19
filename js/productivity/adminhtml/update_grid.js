/**
 * Productivity
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE-AFL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category  ZetaPrints
 * @package   ZetaPrints_Productivity
 * @copyright Copyright (c) 2014 ZetaPrints Ltd. (http://www.zetaprints.com)
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
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