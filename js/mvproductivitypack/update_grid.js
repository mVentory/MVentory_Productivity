function addMiddleClick() {
  $$('table.data tbody tr').each(function(item) {
    item.observe('mousedown', function(e) {
      if(Event.isMiddleClick(e)) {
        window.open(item.readAttribute('title'), '_blank');  
      } 
    });
    item.observe('click', function(e) {
      if(Event.isMiddleClick(e)) {
        Event.stop(e);  
      } 
    });
  }); 
}

Event.observe(window, 'load', addMiddleClick); 