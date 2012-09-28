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