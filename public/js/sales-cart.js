/**
 * Cart functionality for /akcijas (sales) page
 * This file handles adding products to cart from the sales page
 */

(function() {
  'use strict';

  // Wait for DOM to be ready
  $(document).ready(function() {
    // Initialize cart handlers for sales page
    initSalesCart();
  });

  function initSalesCart() {

    public_url = '\\';

    // NOTE: checkboxes are inputs themselves, not parents containing children.
    // Use each() directly so handlers bind correctly on sales (/akcijas) pages.
    $('.tire-table-checkbox').each(function(key, value) {
      const $checkbox = $(value);

      // Avoid повторного навешивания при динамических вставках
      if ($checkbox.data('cart-bound')) {
        return;
      }
      $checkbox.data('cart-bound', true);

      // PARSE TO INT
      if (typeof products !== 'undefined') {
        products.push(parseInt($checkbox.val()));
      }

      // ON SHOPPING CART BUTTON CLICK
      const $btn = $checkbox.parent().parent().find('.cart-shopping-button');

      // Снимаем возможные старые хендлеры и навешиваем namespaced
      $btn.off('click.salesCart').on('click.salesCart', function(e) {
        // Check if admin variable is defined globally
        const isAdmin = typeof admin !== 'undefined' ? admin : false;

        if (!isAdmin) {
          const $button = $(this);
          const tire_id = $button.data('info');
          const currentUrl = typeof url !== 'undefined' ? url : window.location.pathname;
          let ajaxUrl = currentUrl;

          let tire_name = $button.parent().parent().parent().find('.table-tire-name-cell');
          if (tire_name.attr('data-link')) {
            ajaxUrl = tire_name.attr('data-link');
          }

          // Проверяем, является ли это литым диском по нескольким признакам
          const isRimPage = window.location.pathname.includes('/lietie-diski') || window.location.pathname.includes('/lietie-diski/');
          const buttonUrl = $button.attr('data-url') || '';
          const isRimUrl = buttonUrl.includes('lietais-disks') || buttonUrl.includes('lietie-diski');
          const isRimLink = ajaxUrl.includes('lietie-diski');

          // Если это литой диск, пропускаем обработку (rimAjax.js обработает)
          if (isRimPage || isRimUrl || isRimLink) {
            return; // Пусть rimAjax.js обработает этот клик
          }

          $.ajax({
            url: ajaxUrl + '/ajax',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: 'POST',
            data: {
              tire_id: ajaxUrl.includes('lietie-diski') ? undefined : tire_id,
              rim_id: ajaxUrl.includes('lietie-diski') ? tire_id : undefined
            },
            success: function(data) {
              // data может приходить строкой или уже объектом
              if (typeof data === 'string') {
                try {
                  data = JSON.parse(data);
                } catch (e) {
                  console.error('Invalid JSON from /ajax:', data);
                  return;
                }
              }

              // Проверяем базовую структуру ответа
              if (!data || !data.cart) {
                console.error('Unexpected /ajax response shape:', data);
                return;
              }

              let cart_quantity = data.quantity || 0;
              cart_quantity = parseInt(cart_quantity);
              let total_sum = data.total_sum || 0;
              total_sum = parseInt(total_sum);

              const publicUrl = typeof public_url !== 'undefined' ? public_url : '\\';
              const grozsUrl = typeof grozs_url !== 'undefined' ? grozs_url : '/grozs';

              // Определяем, это литой диск или шина
              const isRim = ajaxUrl.includes('lietie-diski') || (data.cart.products && Object.keys(data.cart.products).length > 0);
              
              if (isRim && data.cart.products) {
                // Обработка литых дисков
                const rimId = tire_id;
                const rimProduct = data.cart.products[rimId];
                
                if (!rimProduct) {
                  console.error('Rim product not found in cart response:', rimId, data);
                  return;
                }

                // Загружаем изображение диска
                if (rimProduct.image) {
                  fetch(publicUrl + rimProduct.image, { method: 'HEAD' })
                    .then(res => {
                      if (res.ok) {
                        $('.modal-image-preview img').attr('src', publicUrl + rimProduct.image);
                      } else {
                        $('.modal-image-preview img').attr('src', '/img/p/en-default-home_default.jpg');
                      }
                    })
                    .catch(() => {
                      $('.modal-image-preview img').attr('src', '/img/p/en-default-home_default.jpg');
                    });
                } else {
                  $('.modal-image-preview img').attr('src', '/img/p/en-default-home_default.jpg');
                }

                // Заполняем модальное окно данными диска
                $('.modal-product-info .product-name').html(rimProduct.name || '');
                $('.modal-product-info .product-price').html(parseInt(rimProduct.price || 0)).attr('data-price', parseInt(rimProduct.price || 0));
                if ($('.modal-product-info .product-rim-width').length) {
                  $('.modal-product-info .product-rim-width').html(rimProduct.d1 || '');
                }
                if ($('.modal-product-info .product-radius').length) {
                  $('.modal-product-info .product-radius').html(rimProduct.d3 || '');
                }
                if ($('.modal-product-info .product-lug-distance').length) {
                  $('.modal-product-info .product-lug-distance').html((rimProduct.skr || '') + 'x' + (rimProduct.pcd || ''));
                }
                if ($('.modal-product-info .product-comment').length) {
                  $('.modal-product-info .product-comment').html(rimProduct.comment || '');
                }

                // Обновляем корзину
                $('.cart-content .cart-products-total').html(total_sum);
                $('span.cart-products-count').html('(' + cart_quantity + ')');
                $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
                $('.blockcart.cart-preview .header').empty();
                $('<a rel="nofollow" href="' + grozsUrl + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
              } else if (data.cart.options && data.cart.options.tire) {
                // Обработка шин (оригинальная логика)
                if (typeof data.cart.options.tire.tread.tread_id === "undefined") {
                  if (data.cart.options.tire.make_id) {
                    fetch(publicUrl + data.cart.options.image + '/tread/' + data.cart.options.tire.make_id + '-o.jpg',
                      { method: 'GET' })
                      .then(res => {
                        if (res.ok) {
                          $('.modal-image-preview img').attr('src', publicUrl + data.cart.options.image + '/tread/' + data.cart.options.tire.make_id + '-o.jpg');
                        } else {
                          $('.modal-image-preview img').attr('src', '/img/p/en-default-home_default.jpg');
                        }
                      });
                  }
                } else {
                  fetch(publicUrl + data.cart.options.image + '/tread/' + data.cart.options.tire.tread.tread_id + '-o.jpg',
                    { method: 'GET' })
                    .then(res => {
                      if (res.ok) {
                        $('.modal-image-preview img').attr('src', publicUrl + data.cart.options.image + '/tread/' + data.cart.options.tire.tread.tread_id + '-o.jpg');
                      } else {
                        $('.modal-image-preview img').attr('src', '/img/p/en-default-home_default.jpg');
                      }
                    });
                }

                if (data.cart.options.image == 'stud') {
                  // STUD IMAGE INSIDE MODAL
                  $('.modal-product-info .product-name').html(data.cart.name);
                  $('.modal-product-info .product-price').html(parseInt(data.cart.options.tire.price2)).attr('data-price', parseInt(data.cart.options.tire.price2));
                  $('.modal-product-info .product-stud-length').html(data.cart.options.tire.stud_length);
                  $('.modal-product-info .product-stud-count').html(data.cart.options.tire.stud_count);
                  $('.modal-product-info .product-comment').html(data.cart.options.tire.comment);
                  $('.cart-content .cart-products-total').html(total_sum);
                  $('span.cart-products-count').html('(' + cart_quantity + ')');
                  $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
                  $('.blockcart.cart-preview .header').empty();
                  $('<a rel="nofollow" href="' + grozsUrl + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
                } else if (data.cart.options.image == 'rims' || data.cart.options.image == 'quadrims') {
                  // RIMS IMAGE INSIDE MODAL
                  $('.modal-product-info .product-name').html(data.cart.name);
                  $('.modal-product-info .product-price').html(parseInt(data.cart.options.tire.price2)).attr('data-price', parseInt(data.cart.options.tire.price2));
                  $('.modal-product-info .product-rim-width').html(data.cart.options.tire.d1);
                  $('.modal-product-info .product-radius').html(data.cart.options.tire.d3);
                  $('.modal-product-info .product-lug-distance').html(data.cart.options.tire.skr + 'x' + data.cart.options.tire.pcd);
                  $('.modal-product-info .product-comment').html(data.cart.options.tire.comment);
                  $('.cart-content .cart-products-total').html(total_sum);
                  $('span.cart-products-count').html('(' + cart_quantity + ')');
                  $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
                  $('.blockcart.cart-preview .header').empty();
                  $('<a rel="nofollow" href="' + grozsUrl + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
                } else {
                  // TIRE IMAGE INSIDE MODAL
                  $('.modal-product-info .product-name').html(data.cart.name);
                  if (data.cart.options.tire.price2 != null) {
                    $('.modal-product-info .product-price').html(parseInt(data.cart.options.tire.price2)).attr('data-price', parseInt(data.cart.options.tire.price2));
                  } else {
                    $('.modal-product-info .product-price').html(parseInt(data.cart.options.tire.price3)).attr('data-price', parseInt(data.cart.options.tire.price3));
                  }
                  $('.modal-product-info .product-width').html(data.cart.options.tire.d1);
                  $('.modal-product-info .product-height').html(data.cart.options.tire.d2);
                  $('.modal-product-info .product-radius').html(data.cart.options.tire.d3);
                  $('.modal-product-info .product-type').html(data.cart.options.tire.d3);
                  $('.modal-product-info .product-li').html(data.cart.options.tire.li);
                  $('.modal-product-info .product-si').html(data.cart.options.tire.si);
                  $('.cart-content .cart-products-total').html(total_sum);
                  $('.modal-product-info .product-qty').html($('.modal-product-info .product-qty').attr('data-qty')).attr('data-qty', parseInt(data.quantity));
                  $('span.cart-products-count').html('(' + cart_quantity + ')');
                  $('.blockcart.cart-preview').removeClass('inactive').addClass('active');
                  $('.blockcart.cart-preview .header').empty();
                  $('<a rel="nofollow" href="' + grozsUrl + '"><i class="material-icons shopping-cart">shopping_cart</i><span class="hidden-sm-down">Grozs: </span><span class="cart-products-count">(' + cart_quantity + ')</span></a>').appendTo('.blockcart.cart-preview .header');
                }
              } else {
                console.error('SalesCart: Unknown response structure:', data);
              }
            },
            error: function(xhr, status, error) {
              console.error('SalesCart: ошибка AJAX:', error, xhr.responseText);
            }
          });
        } else {
          // IF ADMIN
          const tire_data = $(this).parent().parent().parent();
          const $a = $('.table-tire-name-cell a', tire_data);
          let article = $a.data('article');
          if (!article || (typeof article === 'string' && article.length === 0)) article = 'no_article';
          
          const currentUser = typeof user !== 'undefined' ? user : '';

          // Product title can be absent in data-*; fallback to link text / article
          const prodRaw =
            $a.data('content') ||
            $a.data('name') ||
            $a.attr('title') ||
            $a.text();
          const prod = (prodRaw || '').toString().trim() || article;
          
          $('.popup input[name=prod]').val(prod);
          $('.popup input[name=price]').val($('.tire-price-red', tire_data).html().replace('€ ', ''));
          $('.popup input[name=qty]').val($('.table-tire-name-cell a', tire_data).data('quantity'));
          $('.popup input[name=total]').val(parseInt($('.tire-price-red', tire_data).html().replace('€ ', '')) * $('.popup input[name=qty]').val());
          $('.popup input[name=user]').val(currentUser).attr('readonly', true).prop('readonly', true);
          $('.popup input[name=article]').val($('.table-tire-name-cell a', tire_data).data('article'));

          const calcData = {
            'article': article,
            'qty': $('.table-tire-name-cell a', tire_data).data('quantity'),
            'user': currentUser,
            'prod': prod,
            'price': $('.tire-price-red', tire_data).html().replace('€', ''),
          };

          if (typeof addEntry === 'function') {
            addEntry(calcData);
          }

          const urlData = new URLSearchParams(calcData).toString();

          if (typeof popCalc === 'function') {
            popCalc('/testing3', 950, 650);
          }
        }
      });
    });
  }

  // Re-initialize when new content is loaded dynamically
  $(document).on('DOMNodeInserted', function(e) {
    if ($(e.target).find('.tire-table-checkbox').length > 0) {
      initSalesCart();
    }
  });
})();

