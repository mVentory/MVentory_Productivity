Event.observe(window,'load', function() {
  Event.observe('content','change', function() {alert('1');});
});
function addColumnEdit() {
  $$('table.data thead tr').each(function(item) {
    if(item.readAttribute('class') == 'headings') {
      item.down('.last').removeClassName('last');
      var th = new Element('th');
      th.update('<span class="nobr">Action</span>');
      th.className = 'no-link last';
      th.setStyle({'width':'50px'});
      item.insert(th);
    } else {
      item.down('.last').removeClassName('last');
      var th = new Element('th');
      th.className = 'no-link last'
      item.insert(th);
    }   
  });
  $$('table.data tbody tr').each(function(item) {
    item.down('.last').removeClassName('last');
    var td = new Element('td');
    td.update('<a href="'+item.readAttribute('title')+'">Edit</a>');
    td.className = 'last';
    item.insert(td);
  }); 
}

Event.observe(window, 'load', addColumnEdit); 