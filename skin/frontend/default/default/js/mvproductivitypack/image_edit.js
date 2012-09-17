jQuery(document).ready(function ($) {
  var $form = $('#product_addtocart_form');
  var $menus = $form.find('.tm-image-editor-menu');

  $menus
    .parent()
    .on('mouseenter', function () {
      var $this = $(this);

      var offset = $this.offset();

      $this
        .children('.tm-image-editor-menu')
        .css({
          top: offset.top + $this.height() - 10,
          left: offset.left + 10
        })
        .show();
    })
    .on('mouseleave', function () {
      $(this)
          .children('.tm-image-editor-menu')
          .hide();
    });

  $menus
    .children('.rotate-image')
    .filter('.rotate-left')
      .on('click', { rotate: 'left' }, rotate_button_click_handler)
    .end()
    .filter('.rotate-right')
      .on('click', { rotate: 'right' }, rotate_button_click_handler);

  $menus
    .children('.remove-image')
    .on('click', remove_button_click_handler);

  $menus
    .children('.set-main-image')
    .on('click', set_main_button_click_handler);

  function rotate_image (file, rotate, complete) {
    $.ajax({
      url: _tm_image_editor_rotate_url,
      type: 'POST',
      dataType: 'json',
      data: { file: file, rotate:  rotate},
      error: function (jqXHR, status, errorThrown) {
        alert(status);
      },
      /*success: function (data, status, jqXHR) {
        console.log(data);
      },*/
      complete: complete
    });
  }

  function remove_image (file, product_id, complete) {
    $.ajax({
      url: _tm_image_editor_remove_url,
      type: 'POST',
      dataType: 'json',
      data: { file: file, product: product_id },
      error: function (jqXHR, status, errorThrown) {
        alert(status);
      },
      /*success: function (data, status, jqXHR) {
        console.log(data);
      },*/
      complete: complete
    });
  }

  function set_main_image (file, product_id, complete) {
    $.ajax({
      url: _tm_image_editor_setmain_url,
      type: 'POST',
      dataType: 'json',
      data: { file: file, product: product_id },
      error: function (jqXHR, status, errorThrown) {
        alert(status);
      },
      /*success: function (data, status, jqXHR) {
        console.log(data);
      },*/
      complete: complete
    });
  }

  function rotate_button_click_handler (event) {
    var $this = $(this);

    $this
      .parent()
      .children('.rotate-image')
      .off('click', rotate_button_click_handler);

    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();

    var rotate = event.data.rotate;

    rotate_image(image, rotate, function () {
      //$this.on('click', { rotate: rotate }, rotate_button_click_handler);

      $this
        .parent()
        .remove();
    });

    var $imgs = $form
                  .find('img')
                  .filter('[src$="' + image + '"]');

    if (rotate == 'left')
      $imgs.addClass('rotate-90');
    else
      $imgs.addClass('rotate90');

    return false;
  }

  function remove_button_click_handler (event) {
    var $this = $(this);

    $this.off('click', remove_button_click_handler);

    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    remove_image(image, product_id, function () {
      $this
        .parent()
        .remove();
    });

    $form
      .find('img')
      .filter('[src$="' + image + '"]')
      .remove();
  }

  
  function set_main_button_click_handler (event) {
    var $this = $(this);

    $this.off('click', set_main_button_click_handler);

    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    set_main_image(image, product_id, function () {
      $this
        .parent()
        .remove();
    });

    var $img = $form.find('.product-image img')

    var image_url = $img.prop('src');

    var $thumb = $form
                   .find('img')
                   .filter('[src$="' + image + '"]')

    $thumb
      .width($thumb.width())
      .prop('src', image_url);

    $img
      .width($img.width())
      .prop('src', '/media/catalog/product/' + image);
  }
});
