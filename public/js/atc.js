/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-9999 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

$(document).ready(function () {
  if ($('.category-vasaras-riepas').length >= 1){
    $('.summer-sorter').each(function() {
      $(this).tablesorter({
          headers: {
            0: {sorter: false},
            1: {sorter: true},
            2: {sorter: false},
            3: {sorter: false},
            4: {sorter: false},
            5: {sorter: false},
            6: {sorter: false},
            9: {sorter: false},
            10: {sorter: false},
            11: {sorter: false}
          },
        }
      );
    });
  } else if ($('.category-ziemas-riepas').length >= 1){
    $('.summer-sorter').each(function() {
      $(this).tablesorter({
          headers: {
            0: {sorter: false},
            1: {sorter: true},
            2: {sorter: false},
            3: {sorter: false},
            4: {sorter: false},
            5: {sorter: false},
            6: {sorter: false},
            7: {sorter: false},
            8: {sorter: true},
            9: {sorter: true},
            10: {sorter: false},
            11: {sorter: false}
          },
        }
      );
    });
  }

  $('.industrial-sorter').each(function() {
    $(this).tablesorter({
        sortList: [[6,1]],
        headers: {
          // 0: {sorter: false},
          // 1: {sorter: false}6,
          // 2: {sorter: false},
          // 3: {sorter: false},
          // 4: {sorter: false},
          5: {sorter: true},
          6: {sorter: true},
          // 7: {sorter: false},
          // 8: {sorter: false},
          // 9: {sorter: false}
        },
      }
    );
  });
  $('.moto-sorter').each(function() {
    $(this).tablesorter({
        sortList: [[6,1]],
        headers: {
          0: {sorter: false},
          1: {sorter: true},
          2: {sorter: false},
          3: {sorter: false},
          4: {sorter: false},
          5: {sorter: true},
          6: {sorter: true},
          7: {sorter: false},
          8: {sorter: false},
          9: {sorter: false}
        },
      }
    );
  });

  $('.moto-tread-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: false},
          2: {sorter: false},
          3: {sorter: false},
          4: {sorter: true},
          5: {sorter: true},
          6: {sorter: false},
          7: {sorter: false},
          8: {sorter: false}
        },
      }
    );
  });

  $('.quadr-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: true},
          2: {sorter: false},
          3: {sorter: true},
          4: {sorter: true},
          5: {sorter: false},
          6: {sorter: false},
          7: {sorter: false}
        },
      }
    );
  });

  $('.quadr-tread-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: false},
          2: {sorter: false},
          3: {sorter: true},
          4: {sorter: true},
          5: {sorter: false},
          6: {sorter: false},
          7: {sorter: false},
          8: {sorter: false}
        },
      }
    );
  });

  $('.rims-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: true},
          2: {sorter: false},
          3: {sorter: false},
          4: {sorter: false},
          5: {sorter: false},
          6: {sorter: false},
          7: {sorter: true},
          8: {sorter: true},
          9: {sorter: false},
          10: {sorter: false},
          11: {sorter: false}
        },
      }
    );
  });

  $('.rims-tread-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: false},
          2: {sorter: false},
          3: {sorter: false},
          4: {sorter: false},
          5: {sorter: false},
          6: {sorter: false},
          7: {sorter: false},
          8: {sorter: true},
          9: {sorter: true},
          10: {sorter: false},
          11: {sorter: false},
          12: {sorter: false}
        },
      }
    );
  });

  $('.studs-sorter').each(function() {
    $(this).tablesorter({
        headers: {
          0: {sorter: false},
          1: {sorter: true},
          2: {sorter: false},
          3: {sorter: false},
          4: {sorter: true},
          5: {sorter: true},
          6: {sorter: false},
          7: {sorter: false},
          8: {sorter: true}
        },
      }
    );
  });
});
