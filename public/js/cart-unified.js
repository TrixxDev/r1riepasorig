/**
 * cart-unified.js
 * Единая оптимизированная версия для работы с корзиной
 * Включает логику для управления количеством товаров, доставкой и монтажом
 */

// ГЛОБАЛЬНЫЕ НАСТРОЙКИ И ПЕРЕМЕННЫЕ
window.cartState = {
    initialized: false,
    quantityHandlersInitialized: false,
    lastProcessedProduct: {
        id: null,
        time: 0,
        threshold: 1000
    },
    ajaxRequests: {
        checkFitting: null
    }
};

/**
 * ИНИЦИАЛИЗАЦИЯ КОРЗИНЫ
 * Вызывается при загрузке DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    // console.log("Инициализация корзины...");
    
    if (window.cartState.initialized) {
        // console.log("Корзина уже инициализирована, пропускаем повторную инициализацию");
        return;
    }
    
    // Инициализация интерфейса
    initCartUI();
    
    // Установка обработчиков событий
    setupEventHandlers();
    
    // Проверка состояния корзины
    syncCartState();
    
    // Проверка монтажа при загрузке страницы
    if (window.location.pathname.includes('/shop')) {
        setTimeout(checkFittingAvailability, 300);
        setTimeout(checkFittingAvailability, 1000);
    }

    // Устанавливаем наблюдатель за изменениями в DOM корзины
    setupCartObserver();
    
    // Устанавливаем флаг инициализации
    window.cartState.initialized = true;
    // console.log("Инициализация корзины завершена");

    // Инициализация видимости адреса доставки при загрузке
    if ($('input[name="data[shipping_city]"]:checked').length) {
        $('#shipping-address-block').show();
    } else {
        $('#shipping-address-block').hide();
    }
    // Обработчик на смену города
    $('input[name="data[shipping_city]"]').on('change', function() {
        if ($('input[name="data[shipping_city]"]:checked').length) {
            $('#shipping-address-block').show();
        } else {
            $('#shipping-address-block').hide();
        }
    });
});

/**
 * СЕКЦИЯ 1: ИНИЦИАЛИЗАЦИЯ И УЛУЧШЕНИЯ UI
 */

/**
 * Инициализация интерфейса корзины
 */
function initCartUI() {
    // console.log("Инициализация интерфейса корзины...");
    
    // Инициализация анимаций и переходов
    addSmoothTransitions();
    
    // Настройка мгновенной обратной связи
    setupInstantFeedback();
    
    // Настройка подтверждения перед удалением товара
    setupRemovalConfirmation();
}

/**
 * Добавляет плавные переходы между секциями корзины
 */
function addSmoothTransitions() {
    // Добавляем плавное появление для отдельных элементов корзины
    setTimeout(() => {
        // Применяем анимацию к элементам итогов корзины
        document.querySelectorAll('.cart-summary-line').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.animation = 'fadeIn 0.3s forwards';
            el.style.animationDelay = `${index * 0.05}s`;
        });
        
        // Делаем кнопки интерактивнее
        document.querySelectorAll('.btn-primary').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });
    }, 100);
}

/**
 * Настраивает мгновенную обратную связь при изменении количества товаров
 */
function setupInstantFeedback() {
    // Добавляем стили для мгновенной обратной связи
    const style = document.createElement('style');
    style.textContent = `
        /* Стили для анимации загрузки */
        @keyframes spinner {
            to {transform: rotate(360deg);}
        }
        
        /* Анимация обновления цены */
        .price-highlight {
            animation: priceUpdate 0.6s ease;
        }
        
        @keyframes priceUpdate {
            0% { background-color: transparent; }
            50% { background-color: rgba(47, 181, 210, 0.15); }
            100% { background-color: transparent; }
        }
        
        /* Анимация появления элементов */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Плавная анимация для элементов корзины */
        .cart-summary-line {
            transition: background-color 0.3s ease;
        }
    `;
    document.head.appendChild(style);
    
    // Выделение измененных цен
    document.addEventListener('priceUpdated', function(e) {
        const elements = document.querySelectorAll(e.detail.selector);
        elements.forEach(el => {
            // Сначала удаляем класс, если он есть
            el.classList.remove('price-highlight');
            
            // Затем добавляем его после небольшой задержки
            setTimeout(() => {
                el.classList.add('price-highlight');
                
                // И удаляем через некоторое время
                setTimeout(() => {
                    el.classList.remove('price-highlight');
                }, 600);
            }, 10);
        });
    });
}

/**
 * Настраивает подтверждение перед удалением товара
 */
function setupRemovalConfirmation() {
    document.querySelectorAll('.remove-from-cart').forEach(link => {
        const originalHref = link.getAttribute('href');
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Создаем мини-диалог подтверждения
            const container = document.createElement('div');
            container.className = 'confirmation-dialog';
            container.style.position = 'absolute';
            container.style.backgroundColor = 'white';
            container.style.border = '1px solid #dee2e6';
            container.style.borderRadius = '4px';
            container.style.padding = '8px 12px';
            container.style.boxShadow = '0 2px 5px rgba(0,0,0,0.15)';
            container.style.zIndex = '100';
            container.style.maxWidth = '180px';
            
            const text = document.createElement('p');
            text.textContent = 'Dzēst preci no groza?';
            text.style.margin = '0 0 8px 0';
            text.style.fontSize = '14px';
            text.style.color = '#333';
            
            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.justifyContent = 'space-between';
            
            const confirmButton = document.createElement('button');
            confirmButton.textContent = 'Jā';
            confirmButton.className = 'btn btn-sm btn-danger';
            confirmButton.style.marginRight = '5px';
            confirmButton.style.padding = '3px 10px';
            
            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Nē';
            cancelButton.className = 'btn btn-sm btn-secondary';
            cancelButton.style.padding = '3px 10px';
            
            buttonContainer.appendChild(confirmButton);
            buttonContainer.appendChild(cancelButton);
            
            container.appendChild(text);
            container.appendChild(buttonContainer);
            
            // Позиционируем рядом с иконкой удаления
            const rect = this.getBoundingClientRect();
            container.style.top = (rect.bottom + window.scrollY) + 'px';
            container.style.left = (rect.left + window.scrollX - 100) + 'px';
            
            document.body.appendChild(container);
            
            // Обработчики кнопок
            confirmButton.addEventListener('click', function() {
                // Удаляем диалог
                document.body.removeChild(container);
                
                // Отправляем AJAX запрос на удаление
                $.ajax({
                    url: originalHref,
                    method: 'GET',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data) {
                        console.log("Товар успешно удален:", data);
                        
                        // Удаляем строку товара из таблицы
                        const itemRow = link.closest('.cart-item-container');
                        if (itemRow) {
                            itemRow.remove();
                        }
                        
                        // Перезагружаем страницу после удаления товара
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("Ошибка при удалении товара:", error);
                        alert('Произошла ошибка при удалении товара. Пожалуйста, попробуйте еще раз.');
                    }
                });
            });
            
            cancelButton.addEventListener('click', function() {
                document.body.removeChild(container);
            });
            
            // Закрыть диалог по клику вне него
            document.addEventListener('click', function closeDialog(e) {
                if (!container.contains(e.target) && e.target !== link) {
                    if (document.body.contains(container)) {
                        document.body.removeChild(container);
                    }
                    document.removeEventListener('click', closeDialog);
                }
            });
        });
    });
}

/**
 * Установка наблюдателя за изменениями в DOM корзины
 */
function setupCartObserver() {
    // Дополнительная проверка при любых изменениях в DOM корзины
    const cartObserver = new MutationObserver(function(mutations) {
        // console.log("Обнаружены изменения в DOM корзины, проверяем состояние");
        checkFittingAvailability();
    });
    
    // Наблюдаем за изменениями в корзине
    const cartContainer = document.querySelector('.cart-grid-body');
    if (cartContainer) {
        cartObserver.observe(cartContainer, { childList: true, subtree: true });
        // console.log("Наблюдение за корзиной установлено");
    }
}

/**
 * СЕКЦИЯ 2: ОБРАБОТЧИКИ СОБЫТИЙ И УПРАВЛЕНИЕ КОЛИЧЕСТВОМ
 */

/**
 * Установка обработчиков событий для корзины
 */
function setupEventHandlers() {
    // console.log("Установка обработчиков событий для корзины...");
    
    // Устанавливаем обработчики для кнопок изменения количества
    setupQuantityButtons();
    
    // Устанавливаем обработчики для опций доставки
    setupDeliveryHandlers();
    
    // Устанавливаем обработчики для опций монтажа
    setupFittingHandlers();
    
    // Устанавливаем обработчики для удаления товаров
    setupRemoveHandlers();
}

/**
 * Устанавливает обработчики для кнопок изменения количества товаров
 */
function setupQuantityButtons() {
    // Проверяем, не установлены ли уже обработчики
    if (window.cartState.quantityHandlersInitialized) return;
    
    // Функция для предотвращения дублирования запросов
    function isDuplicateRequest(productId) {
        const now = Date.now();
        if (window.cartState.lastProcessedProduct.id === productId && 
            now - window.cartState.lastProcessedProduct.time < window.cartState.lastProcessedProduct.threshold) {
            // console.log('Предотвращение дублирования запроса для ID:', productId);
            return true;
        }
        window.cartState.lastProcessedProduct.id = productId;
        window.cartState.lastProcessedProduct.time = now;
        return false;
    }
    
    // Функция безопасного вызова ajaxChangeQty
    function safeAjaxChangeQty(productId, newValue, price) {
        // console.log('safeAjaxChangeQty вызвана с параметрами:', {productId, newValue, price});
        
        // Обновляем значение поля ввода сразу для лучшего UX
        const input = document.querySelector('.js-cart-line-product-quantity[data-product-id="' + productId + '"]');
        if (input) {
            input.value = newValue;
        }
        
        if (typeof window.ajaxChangeQty === 'function') {
            try {
                window.ajaxChangeQty(productId, newValue, price);
                
                // Используем единую функцию проверки монтажа
                setTimeout(function() {
                    checkFittingAvailability(true);
                }, 300);
            } catch (error) {
                // console.error('Ошибка вызова ajaxChangeQty:', error);
                // В случае ошибки перезагружаем страницу
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            // console.error('Функция ajaxChangeQty недоступна, перезагружаем страницу');
            setTimeout(() => window.location.reload(), 1000);
        }
    }
    
    // Функция для обработки события увеличения количества
    function handleIncreaseClick(event) {
        // console.log('Нажата кнопка увеличения количества');
        event.preventDefault();
        event.stopPropagation();
        
        // Находим родительский элемент с кнопками
        const buttonGroup = this.closest('.input-group-btn-vertical');
        if (!buttonGroup) {
            // console.error('Не найден родительский элемент .input-group-btn-vertical');
            return;
        }
        
        // Находим поле ввода
        const input = buttonGroup.parentElement.querySelector('input.js-cart-line-product-quantity');
        if (!input) {
            // console.error('Не найдено поле ввода');
            return;
        }
        
        const productId = input.getAttribute('data-product-id');
        const price = input.getAttribute('data-item-price');
        
        if (!productId || !price) {
            // console.error('Не найдены data-атрибуты в поле ввода', {productId, price});
            return;
        }
        
        if (isDuplicateRequest(productId)) return;
        
        const currentValue = parseInt(input.value) || 0;
        const newValue = currentValue + 1;
        
        safeAjaxChangeQty(productId, newValue, price);
    }
    
    // Функция для обработки события уменьшения количества
    function handleDecreaseClick(event) {
        // console.log('Нажата кнопка уменьшения количества');
        event.preventDefault();
        event.stopPropagation();
        
        // Находим родительский элемент с кнопками
        const buttonGroup = this.closest('.input-group-btn-vertical');
        if (!buttonGroup) {
            // console.error('Не найден родительский элемент .input-group-btn-vertical');
            return;
        }
        
        // Находим поле ввода
        const input = buttonGroup.parentElement.querySelector('input.js-cart-line-product-quantity');
        if (!input) {
            // console.error('Не найдено поле ввода');
            return;
        }
        
        const productId = input.getAttribute('data-product-id');
        const price = input.getAttribute('data-item-price');
        
        if (!productId || !price) {
            // console.error('Не найдены data-атрибуты в поле ввода', {productId, price});
            return;
        }
        
        const currentValue = parseInt(input.value) || 0;
        if (currentValue <= 1) {
            // console.log('Нельзя уменьшить количество меньше 1');
            return;
        }
        
        if (isDuplicateRequest(productId)) return;
        
        const newValue = currentValue - 1;
        
        safeAjaxChangeQty(productId, newValue, price);
    }
    
    // Функция для обработки события изменения значения поля ввода
    function handleInputChange(event) {
        // console.log('Изменено значение поля ввода');
        
        const input = this;
        const productId = input.getAttribute('data-product-id');
        const price = input.getAttribute('data-item-price');
        
        if (!productId || !price) {
            // console.error('Не найдены data-атрибуты в поле ввода', {productId, price});
            return;
        }
        
        if (isDuplicateRequest(productId)) return;
        
        let newValue = parseInt(input.value) || 0;
        if (newValue < 1) {
            newValue = 1;
            input.value = 1;
        }
        
        safeAjaxChangeQty(productId, newValue, price);
    }
    
    // Удаляем существующие обработчики
    document.querySelectorAll('.js-increase-product-quantity, .js-decrease-product-quantity').forEach(button => {
        button.removeEventListener('click', handleIncreaseClick);
        button.removeEventListener('click', handleDecreaseClick);
    });
    
    document.querySelectorAll('.js-cart-line-product-quantity').forEach(input => {
        input.removeEventListener('change', handleInputChange);
    });
    
    // Устанавливаем обработчики для кнопок увеличения
    document.querySelectorAll('.js-increase-product-quantity').forEach(button => {
        button.addEventListener('click', handleIncreaseClick);
    });
    
    // Устанавливаем обработчики для кнопок уменьшения
    document.querySelectorAll('.js-decrease-product-quantity').forEach(button => {
        button.addEventListener('click', handleDecreaseClick);
    });
    
    // Устанавливаем обработчики для полей ввода
    document.querySelectorAll('.js-cart-line-product-quantity').forEach(input => {
        input.addEventListener('change', handleInputChange);
    });
    
    // Отмечаем, что обработчики уже установлены
    window.cartState.quantityHandlersInitialized = true;
    // console.log("Установлены обработчики для изменения количества товаров");
}

/**
 * Основная функция для изменения количества товаров через AJAX
 */
window.ajaxChangeQty = function(productId, quantity, price) {
    // console.log("Обновление количества товара:", {productId, quantity, price});
    
    // Получаем строку товара
    const itemRow = document.querySelector(`.cart-item-container .product-price[data-product-id="${productId}"]`) ||
                   $(`.cart-item-container .product-price[data-product-id="${productId}"]`)[0];
                   
    // Добавляем класс обновления для визуального эффекта
    if (itemRow) {
        itemRow.classList.add('item-updating');
    }
    
    // Отправляем AJAX запрос
    $.ajax({
        url: '/shop/ajaxChangeQty',
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
            product_id: productId,
            qty: quantity,
            price: price
        },
        dataType: 'JSON',
        success: function(data) {
            // console.log("Ответ от сервера (ajaxChangeQty):", data);
            
            // Обновляем интерфейс корзины
            updateCartUI(data, productId, quantity, price);
        },
        error: function(xhr, status, error) {
            // console.error("Ошибка при обновлении количества товара:", error);
            
            // Удаляем класс обновления
            if (itemRow) {
                itemRow.classList.remove('item-updating');
            }
            
            // Восстанавливаем предыдущее значение
            document.querySelector(`.js-cart-line-product-quantity[data-product-id="${productId}"]`).value = 
                document.querySelector(`.js-cart-line-product-quantity[data-product-id="${productId}"]`).defaultValue;
                
            // Выводим сообщение об ошибке
            alert('Произошла ошибка при обновлении корзины. Пожалуйста, попробуйте еще раз.');
        }
    });
};

/**
 * Обновляет интерфейс корзины после изменения количества товаров
 * @param {Object} data - Данные, полученные с сервера
 * @param {number} productId - ID товара
 * @param {number} quantity - Новое количество
 * @param {number} price - Цена товара
 */
window.updateCartUI = function(data, productId, quantity, price) {
    console.log("Обновление интерфейса корзины:", {
        data: data,
        productId: productId,
        quantity: quantity,
        price: price
    });
    
    // Получаем элемент с ценой товара
    const priceElement = document.querySelector(`.product-price[data-product-id="${productId}"]`);
    console.log("Найден элемент цены:", priceElement);
    
    if (priceElement) {
        // Вычисляем общую стоимость товара
        const totalPrice = quantity * parseFloat(price);
        console.log("Расчет цены:", {quantity, price, totalPrice});
        
        // Обновляем отображение цены
        priceElement.innerHTML = `<strong>€ ${totalPrice}</strong>`;
    }
    
    // Обновляем общее количество товаров в подытоге
    if (data.total_items) {
        $('#cart-subtotal-products .js-subtotal').html(data.total_items + ' Preces');
        
        // Обновляем количество товаров в шапке сайта
        const cartCountElement = document.querySelector('.cart-products-count');
        if (cartCountElement) {
            cartCountElement.textContent = `(${data.total_items})`;
        }
    }
    
    // Обновляем подытог (сумму всех товаров)
    if (data.subtotal) {
        $('#cart-subtotal-products .value').html('€ ' + data.subtotal);
    }
    
    // Проверяем, выбрана ли доставка
    const deliveryRadio = document.querySelector('input[name="data[cart_delivery_radio]"]:checked');
    const hasDelivery = deliveryRadio && deliveryRadio.value === '3';
    
    // Если выбрана доставка, пересчитываем её стоимость
    if (hasDelivery) {
        console.log("Выбрана доставка, пересчитываем стоимость");
        checkShipping();
    } else {
        // Всегда проверяем возможность монтажа при самовывозе
        console.log("Выбран самовывоз, проверяем возможность монтажа");
        checkFittingAvailability(true);
        
        // Если монтаж был выбран, пересчитываем его стоимость
        const fittingRadio = document.querySelector('input[name="cart-montage-radio"]:checked');
        const hasFitting = fittingRadio && fittingRadio.value === '1';
        
        if (hasFitting) {
            console.log("Монтаж выбран, пересчитываем стоимость");
            checkFitting();
        } else {
            // Если ни доставка, ни монтаж не выбраны, просто обновляем общую стоимость
            if (data.total) {
                $('.cart-total .value').html('€ ' + data.total);
            }
        }
    }
    
    // Обновляем общую стоимость корзины
    setTimeout(function() {
        if (typeof updateTotalPrice === 'function') {
            updateTotalPrice();
        }
    }, 100);
};

/**
 * Синхронизация состояния корзины
 */
function syncCartState() {
    // console.log("Синхронизация состояния корзины...");
    
    // Проверяем, находимся ли мы на странице корзины
    if (!window.location.pathname.includes('/shop')) {
        return;
    }
    
    // Проверяем наличие товаров в корзине
    const hasItems = document.querySelector('.cart-item-container') !== null;
    if (!hasItems) {
        // console.log("Корзина пуста, пропускаем синхронизацию");
        return;
    }
    
    // Проверяем выбранные опции
    const deliveryRadio = document.querySelector('input[name="data[cart_delivery_radio]"]:checked');
    const fittingRadio = document.querySelector('input[name="cart-montage-radio"]:checked');
    
    // Если выбрана доставка, пересчитываем её стоимость
    if (deliveryRadio && deliveryRadio.value === '3') {
        console.log("При загрузке: выбрана доставка, пересчитываем стоимость");
        checkShipping();
    }
    // Если выбран монтаж, пересчитываем его стоимость
    else if (fittingRadio && fittingRadio.value === '1') {
        console.log("При загрузке: выбран монтаж, пересчитываем стоимость");
        checkFitting();
    }
    
    // Проверяем состояние доставки и монтажа
    checkDeliveryState();
    checkFittingAvailability();
}

/**
 * СЕКЦИЯ 4: УПРАВЛЕНИЕ ДОСТАВКОЙ
 */

/**
 * Проверяет состояние опций доставки
 */
function checkDeliveryState() {
    // console.log("Проверка состояния доставки...");
    
    // Получаем выбранную опцию доставки
    const deliveryRadio = document.querySelector('input[name="data[cart_delivery_radio]"]:checked');
    const hasDelivery = deliveryRadio && deliveryRadio.value === '3';
    
    if (hasDelivery) {
        // console.log("Выбрана доставка, показываем соответствующие блоки");
        $('.cart-delivery-option').show();
        $('#cart-subtotal-shipping').show();
        $('input[name="delivery"]').val(true);
        
        // Скрываем блок монтажа, так как доставка и монтаж взаимоисключающие
        $('.cart-montage-choice').hide();
        $('#cart-subtotal-montage').hide();
        
        // Сбрасываем выбор монтажа
        if ($('input.cart-montage-radio[value="1"], input[name="cart-montage-radio"][value="1"]').is(':checked')) {
            $('input.cart-montage-radio[value="0"], input[name="cart-montage-radio"][value="0"]').prop('checked', true);
            $('input[name="fitting"]').val(false);
            $('input[name="fitting_price"]').val(0);
        }
    } else {
        // console.log("Выбран самовывоз, скрываем блоки доставки");
        $('.cart-delivery-option').hide();
        $('#cart-subtotal-shipping').hide();
        $('input[name="delivery"]').val(false);
        
        // Проверяем возможность показа блока монтажа
        checkFittingAvailability(true);
    }
}

/**
 * Устанавливает обработчики для опций доставки
 */
function setupDeliveryHandlers() {
    // console.log("Установка обработчиков для опций доставки...");
    
    // Для отслеживания предыдущего значения
    let previousDeliveryValue = $('input[name="data[cart_delivery_radio]"]:checked').val() || '1';
    
    // Функция обработчик для переключения доставки
    function deliveryRadioHandler() {
        const currentValue = $(this).val();
        // console.log("Изменен способ доставки на:", currentValue);
        
        // Получаем общее количество товаров
        let totalItems = 0;
        $('.js-cart-line-product-quantity').each(function() {
            totalItems += parseInt($(this).val()) || 0;
        });
        
        // Если не получилось получить из полей ввода, пробуем из заголовка
        if (totalItems === 0) {
            const subtotalText = $('.js-subtotal').text().trim();
            if (subtotalText) {
                const match = subtotalText.match(/(\d+)/);
                if (match && match[1]) {
                    totalItems = parseInt(match[1]);
                }
            }
        }
        
        const validQuantityForFitting = getValidQuantitiesForFitting().includes(totalItems);
        // Добавлено: если количество не 1, 2, 4 — всегда скрываем монтаж
        if (!validQuantityForFitting) {
            $('.cart-montage-choice').hide();
            $('#cart-subtotal-montage').hide();
            $('input[name="cart-montage-radio"][value="0"]').prop('checked', true);
            $('input[name="fitting"]').val(false);
            $('input[name="fitting_price"]').val(0);
            // Не продолжаем дальше, чтобы не сбить остальную логику
            return;
        }
        
        if (currentValue === '3') {
            // Новая проверка: если не выбран город, скрываем доставку и стоимость
            const cityRadio = document.querySelector('input[name="data[shipping_city]"]:checked');
            if (!cityRadio) {
                $('.cart-delivery-option').show();
                $('#cart-subtotal-shipping').hide();
                $('#cart-subtotal-shipping #shipping_price').html('');
                return;
            }
            console.log("Выбрана доставка, скрываем блок монтажа");
            $('.cart-delivery-option').show();
            $('#cart-subtotal-shipping').show();
            
            // Скрываем блок монтажа полностью при выборе доставки
            $('.cart-montage-choice').hide();
            $('#cart-subtotal-montage').hide();
            
            // Сбрасываем выбор монтажа
            $('input[name="cart-montage-radio"][value="0"]').prop('checked', true);
            $('input[name="fitting"]').val(false);
            $('input[name="fitting_price"]').val(0);
            
            $('input[name="delivery"]').val(true);
            checkShipping();
        } else {
            console.log("Выбран самовывоз, проверяем возможность монтажа");
            $('.cart-delivery-option').hide();
            $('#cart-subtotal-shipping').hide();
            
            $('input[name="delivery"]').val(false);
            $('input[name="delivery_price"]').removeAttr('value');
            
            // Сбрасываем состояние монтажа ТОЛЬКО если предыдущий выбор был доставка (значение 3)
            if (previousDeliveryValue === '3') {
                $('input[name="cart-montage-radio"][value="0"]').prop('checked', true);
                $('input[name="fitting"]').val(false);
                $('input[name="fitting_price"]').val(0);
                $('#cart-subtotal-montage').hide();
            }
            
            // Показываем или скрываем блок монтажа в зависимости от количества
            checkFittingAvailability(false, false);
            
            // Обновляем общую стоимость без монтажа
            updateTotalPrice();
        }
        
        // Сохраняем текущее значение как предыдущее для следующего вызова
        previousDeliveryValue = currentValue;
    }
    
    // Обработчик для города доставки
    function shippingCityHandler() {
        // Показываем/скрываем адрес
        if ($('input[name="data[shipping_city]"]:checked').length) {
            $('#shipping-address-block').show();
        } else {
            $('#shipping-address-block').hide();
        }
        // Снимаем required если скрыто
        toggleShippingAddressRequired();
        // Снимаем ошибку и подсветку
        $('.cart-delivery-label input[name="data[shipping_city]"]').parent().removeClass('is-invalid-pulse');
        $('#shipping-city-error').html('');
        // Пересчитываем доставку
        checkShipping();
        $('#cart-subtotal-shipping').show();
    }
    
    // Удаляем существующие обработчики
    $('input[name="data[cart_delivery_radio]"]').off('change');
    $('input[name="data[shipping_city]"]').off('change');
    
    // Устанавливаем обработчики
    $('input[name="data[cart_delivery_radio]"]').on('change', deliveryRadioHandler);
    $('input[name="data[shipping_city]"]').on('change', shippingCityHandler);
}

/**
 * Расчет стоимости доставки
 * @param {number|null} qty - Количество товаров (если null, будет взято из DOM)
 */
window.checkShipping = function(qty = null) {
    // console.log("Запрос на расчет стоимости доставки...");
    
    // Получаем выбранный город и общую стоимость товаров
    let city = $('input[name="data[shipping_city]"]:checked').val() || 1;
    let totalPrice = parseInt($('#cart-subtotal-products .value').html().trim().replace('€ ', '').replace(/,/g, '')) || 0;
    
    // Отправляем AJAX запрос
    $.ajax({
        url: '/shop/checkShipping',
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {city: city, qty: qty, total_price: totalPrice},
        dataType: 'JSON',
        success: function(data) {
            // console.log("Ответ от сервера (checkShipping):", data);
            
            // Устанавливаем значения полей формы
            $('input[name="delivery"]').val(true);
            $('input[name="fitting"]').val(false);
            
            // Обрабатываем стоимость доставки
            if (city == 1) {
                if (totalPrice >= 115) {
                    $('#cart-subtotal-shipping #shipping_price').html('Bezmaksas');
                    $('.cart-total .value').html('€ ' + totalPrice);
                    $('input[name="delivery_price"]').removeAttr('value');
                } else {
                    $('#cart-subtotal-shipping #shipping_price').html('€ ' + data.cartOptions.shipping_price);
                    $('.cart-total .value').html('€ ' + (totalPrice + parseInt(data.cartOptions.shipping_price)));
                    $('input[name="delivery_price"]').val(data.cartOptions.shipping_price);
                }
            } else if (city == 3) {
                $('#cart-subtotal-shipping #shipping_price').html('€ ' + data.cartOptions.shipping_price);
                $('.cart-total .value').html('€ ' + (totalPrice + parseInt(data.cartOptions.shipping_price)));
                $('input[name="delivery_price"]').val(data.cartOptions.shipping_price);
            }
        },
        error: function(xhr, status, error) {
            // console.error("Ошибка при расчете стоимости доставки:", error);
        }
    });
};

/**
 * СЕКЦИЯ 3: УПРАВЛЕНИЕ МОНТАЖОМ
 */

/**
 * Определяет тип товаров в корзине
 * @returns {string} - 'moto' для мотошин, 'auto' для автошин
 */
function getCartProductType() {
    // Проверяем URL страницы
    if (window.location.pathname.includes('motociklu-riepas')) {
        return 'moto';
    }
    
    // Проверяем ссылки товаров в корзине
    let hasMoto = false;
    $('.cart-item-table .item-name a').each(function() {
        const href = $(this).attr('href');
        if (href && href.includes('/motociklu-riepas/')) {
            hasMoto = true;
            return false; // прерываем цикл
        }
    });
    
    return hasMoto ? 'moto' : 'any';
}

/**
 * Получает массив допустимых количеств для монтажа в зависимости от типа товара
 * @returns {number[]} - массив допустимых количеств
 */
function getValidQuantitiesForFitting() {
    const productType = getCartProductType();
    return productType === 'moto' ? [1, 2] : [1, 2, 4];
}

/**
 * Единая функция для проверки и управления опцией монтажа
 * @param {boolean} forceCheck - Принудительная проверка, даже если ничего не изменилось
 * @returns {boolean} - Возвращает true если монтаж доступен
 */
window.checkFittingAvailability = function(forceCheck = false, preventReload = false) {
    $('#cart-subtotal-montage').hide();
    // console.log("ПРОВЕРКА МОНТАЖА: Запущена единая функция проверки");
    
    // Получаем общее количество товаров
    let totalItems = 0;
    $('.js-cart-line-product-quantity').each(function() {
        totalItems += parseInt($(this).val()) || 0;
    });
    
    // Если не получилось получить из полей ввода, пробуем из заголовка
    if (totalItems === 0) {
        const subtotalText = $('.js-subtotal').text().trim();
        if (subtotalText) {
            const match = subtotalText.match(/(\d+)/);
            if (match && match[1]) {
                totalItems = parseInt(match[1]);
            }
        }
    }
    
    // console.log("ПРОВЕРКА МОНТАЖА: Количество товаров =", totalItems);
    
    // Проверяем, подходит ли количество для монтажа (1, 2 или 4)
    const validForFitting = getValidQuantitiesForFitting().includes(totalItems);
    // console.log("ПРОВЕРКА МОНТАЖА: Подходит для монтажа =", validForFitting);
    
    // Если явно выбран "монтаж не нужен" — всегда скрываем блок и цену
    if (preventReload === true) {
        if ($('input[name="cart-montage-radio"][value="0"]').is(':checked')) {
            $('#cart-subtotal-montage').hide();
            $('input[name="fitting"]').val(false);
            $('input[name="fitting_price"]').val(0);
            return false;
        }
    }
    
    // Если выбрана доставка, полностью скрываем монтаж
    const deliveryRadio = document.querySelector('input[name="data[cart_delivery_radio]"]:checked');
    const hasDelivery = deliveryRadio && deliveryRadio.value === '3';
    
    if (hasDelivery) {
        $('.cart-montage-choice').hide();
        $('#cart-subtotal-montage').hide();
        
        // Сбрасываем выбор монтажа
        $('input[name="cart-montage-radio"][value="0"]').prop('checked', true);
        $('input[name="fitting"]').val(false);
        $('input[name="fitting_price"]').val(0);
        
        // console.log("ПРОВЕРКА МОНТАЖА: Монтаж скрыт, так как выбрана доставка");
        return false;
    }
    
    // Если количество не подходит для монтажа, полностью скрываем монтаж
    if (!validForFitting) {
        $('.cart-montage-choice').hide();
        $('#cart-subtotal-montage').hide();
        
        // Сбрасываем выбор монтажа
        $('input[name="cart-montage-radio"][value="0"]').prop('checked', true);
        $('input[name="fitting"]').val(false);
        $('input[name="fitting_price"]').val(0);
        
        // console.log("ПРОВЕРКА МОНТАЖА: Монтаж скрыт, так как количество не подходит");
        return false;
    }
    
    // Если количество подходит для монтажа, показываем блок монтажа
    // Новая проверка: только если один тип товара
    if ($('.cart-item-table').length !== 1) {
        $('.cart-montage-choice').hide();
        $('#cart-subtotal-montage').hide();
        return false;
    }
    $('.cart-montage-choice').show();
    
    // Обновляем текст для опции монтажа
    $('input[name="cart-montage-radio"][value="1"]').next('span').text(totalItems + ' ' + (totalItems === 1 ? 'Riepai' : 'Riepām'));
    
    // Включаем радиокнопку монтажа
    $('input[name="cart-montage-radio"][value="1"]').prop('disabled', false);
    $('input[name="cart-montage-radio"][value="1"]').next('span').css('opacity', '1');
    
    // Если монтаж выбран, показываем блок с ценой
    if ($('input[name="cart-montage-radio"][value="1"]').is(':checked')) {
        $('#cart-subtotal-montage').show();
        $('input[name="fitting"]').val(true);
        
        // Если цена еще не получена, запускаем расчет
        if ($('#cart-subtotal-montage #shipping_price').text() === 'Nav' || 
            $('#cart-subtotal-montage #shipping_price').text().includes('spinner-border')) {
            checkFitting();
        }
    }
    
    // console.log("ПРОВЕРКА МОНТАЖА: Монтаж доступен и отображается");
    return true;
};

/**
 * Устанавливает обработчики для опций монтажа
 */
function setupFittingHandlers() {
    // console.log("Установка обработчиков для опций монтажа...");
    
    // Функция обработчик для переключения монтажа
    function fittingRadioHandler() {
        let value = $(this).val();
        // console.log("Изменена опция монтажа на:", value);
        
        // Получаем общее количество товаров
        let totalItems = 0;
        $('.js-cart-line-product-quantity').each(function() {
            totalItems += parseInt($(this).val()) || 0;
        });
        
        // Если не получилось получить из полей ввода, пробуем из заголовка
        if (totalItems === 0) {
            const subtotalText = $('.js-subtotal').text().trim();
            if (subtotalText) {
                const match = subtotalText.match(/(\d+)/);
                if (match && match[1]) {
                    totalItems = parseInt(match[1]);
                }
            }
        }
        
        const validQuantityForFitting = getValidQuantitiesForFitting().includes(totalItems);
        // console.log("Количество товаров:", totalItems, "Подходит для монтажа:", validQuantityForFitting);
        
        // Если количество не подходит для монтажа, блокируем выбор
        if (!validQuantityForFitting && value === '1') {
            // console.log("Количество товаров не подходит для монтажа, блокируем выбор");
            
            // Отменяем выбор монтажа
            $('input.cart-montage-radio[value="0"], input[name="cart-montage-radio"][value="0"]').prop('checked', true);
            
            // Показываем уведомление
            alert('Монтаж доступен только для 1, 2 или 4 шин');
            return;
        }
        
        // Устанавливаем значение скрытого поля
        $('input[name="fitting"]').val(value === '1');
        
        // Показываем/скрываем блок с ценой монтажа
        if (value === '1') {
            // Показываем блок с ценой монтажа
            $('#cart-subtotal-montage').show();
            
            // Рассчитываем стоимость монтажа
            checkFitting();
        } else {
            // Скрываем блок с ценой монтажа
            $('#cart-subtotal-montage').hide();
            $('input[name="fitting_price"]').val(0);
            
            // Обновляем общую стоимость
            updateTotalPrice();
        }
    }
    
    // Удаляем существующие обработчики
    $(document).off('change', '.cart-montage-radio, input[name="cart-montage-radio"]');
    
    // Устанавливаем обработчики
    $(document).on('change', '.cart-montage-radio, input[name="cart-montage-radio"]', fittingRadioHandler);
}

/**
 * Функция для расчета стоимости монтажа
 * @param {number|null} qty - Количество товаров (если null, будет взято из DOM)
 */
window.checkFitting = function(qty = null) {
    // console.log("Запрос на расчет стоимости монтажа...");
    
    // Прерываем предыдущий запрос, если он есть
    if (window.cartState.ajaxRequests.checkFitting) {
        window.cartState.ajaxRequests.checkFitting.abort();
    }
    
    // Показываем спиннер загрузки
    $('#cart-subtotal-montage #shipping_price').html('<div class="spinner-border spinner-border-sm text-info" role="status"></div>');
    
    // Отправляем AJAX запрос
    window.cartState.ajaxRequests.checkFitting = $.ajax({
        url: '/shop/checkFitting',
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
            'fitting': 1
        },
        dataType: 'JSON',
        success: function(response) {
            console.log("Ответ от сервера (checkFitting):", response);
            
            // Разбираем ответ от контроллера
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            const cartOptions = data.cartOptions || {};
            
            if (cartOptions.fitting_price > 0) {
                $('#cart-subtotal-montage').show();
                $('#cart-subtotal-montage #shipping_price').html('€ ' + cartOptions.fitting_price);
            } else {
                $('#cart-subtotal-montage').hide();
            }
            
            // Устанавливаем значения полей формы
            $('input[name="fitting_price"]').val(cartOptions.fitting_price);
            $('input[name="fitting"]').val(true);
            
            // Обновляем общую стоимость
            updateTotalPrice();
        },
        error: function(xhr, status, error) {
            // console.error("Ошибка при расчете стоимости монтажа:", error, xhr.responseText);
            
            // В случае ошибки скрываем блок
            $('#cart-subtotal-montage #shipping_price').html('Nav');
            $('#cart-subtotal-montage').hide();
            $('input[name="fitting_price"]').val(0);
            
            // Обновляем общую стоимость
            updateTotalPrice();
        }
    });
};

/**
 * Обновляет общую стоимость корзины
 */
function updateTotalPrice() {
    // console.log("Обновление общей стоимости корзины...");
    
    // Получаем стоимость товаров
    let totalPrice = parseInt($('#cart-subtotal-products .value').html().trim().replace('€ ', '').replace(/,/g, '')) || 0;
    
    // Проверяем, выбрана ли доставка
    const deliveryRadio = document.querySelector('input[name="data[cart_delivery_radio]"]:checked');
    const hasDelivery = deliveryRadio && deliveryRadio.value === '3';
    
    // Если выбрана доставка, добавляем ее стоимость
    if (hasDelivery) {
        const shippingPriceText = $('#cart-subtotal-shipping #shipping_price').html();
        if (shippingPriceText && shippingPriceText !== 'Bezmaksas' && shippingPriceText !== 'Nav') {
            const shippingPrice = parseInt(shippingPriceText.replace('€ ', '')) || 0;
            totalPrice += shippingPrice;
        }
    }
    
    // Проверяем, выбран ли монтаж
    const fittingRadio = document.querySelector('input[name="cart-montage-radio"]:checked');
    const hasFitting = fittingRadio && fittingRadio.value === '1';
    
    // Если выбран монтаж, добавляем его стоимость
    if (hasFitting) {
        const fittingPriceText = $('#cart-subtotal-montage #shipping_price').html();
        if (fittingPriceText && fittingPriceText !== 'Nav' && !fittingPriceText.includes('spinner-border')) {
            const fittingPrice = parseInt(fittingPriceText.replace('€ ', '')) || 0;
            totalPrice += fittingPrice;
        }
    }
    
    // Обновляем отображаемую стоимость
    $('.cart-total .value').html('€ ' + totalPrice);
}

/**
 * СЕКЦИЯ 6: ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
 */

/**
 * Функция для скрытия элементов монтажа (полностью или только цену)
 * @param {boolean} onlyPrice - Если true, скрывает только цену монтажа
 */
window.hideAllMontageElements = function(onlyPrice = false) {
    if (onlyPrice) {
        // console.log("Скрываем только цену монтажа");
        $('#cart-subtotal-montage').hide();
        $('input[name="fitting"]').val(false);
        $('input[name="fitting_price"]').val(0);
    } else {
        // console.log("Полностью скрываем блок монтажа");
        $('.cart-montage-container').hide();
        $('.montage-block').hide();
        $('#cart-subtotal-montage').hide();
        $('input[name="fitting"]').val(false);
        $('input[name="fitting_price"]').val(0);
        
        // Сбрасываем выбор монтажа
        $('input.cart-montage-radio[value="0"], input[name="cart-montage-radio"][value="0"]').prop('checked', true);
    }
};

/**
 * Устанавливает обработчики для удаления товаров из корзины
 */
function setupRemoveHandlers() {
    // Функция для обработки удаления товара
    function handleRemoveClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Здесь мы просто предотвращаем действие по умолчанию
        // AJAX запрос будет отправлен только после нажатия "Jā" в диалоге подтверждения,
        // который создается в функции setupRemovalConfirmation
    }
    
    // Удаляем существующие обработчики
    $('.remove-from-cart').off('click');
    
    // Устанавливаем обработчики
    $('.remove-from-cart').on('click', handleRemoveClick);
}

// Экспортируем функции для использования в других скриптах
window.cart = {
    checkFittingAvailability: window.checkFittingAvailability,
    checkFitting: window.checkFitting,
    checkShipping: window.checkShipping,
    ajaxChangeQty: window.ajaxChangeQty,
    hideAllMontageElements: window.hideAllMontageElements,
    updateTotalPrice: updateTotalPrice
};

// Инициализируем корзину через картинг
// console.log("cart-unified.js загружен и готов к инициализации");

function toggleShippingAddressRequired() {
    if ($('#shipping-address-block').is(':visible')) {
        $('#shipping_address').attr('required', true);
    } else {
        $('#shipping_address').removeAttr('required');
    }
}

// Удаляю все предыдущие обработчики на data[shipping_city]
$('input[name="data[shipping_city]"]').off('change');
// Новый универсальный обработчик
$('input[name="data[shipping_city]"]').on('change', function() {
    // Показываем/скрываем адрес
    if ($('input[name="data[shipping_city]"]:checked').length) {
        $('#shipping-address-block').show();
    } else {
        $('#shipping-address-block').hide();
    }
    // Снимаем required если скрыто
    toggleShippingAddressRequired();
    // Снимаем ошибку и подсветку
    $('.cart-delivery-label input[name="data[shipping_city]"]').parent().removeClass('is-invalid-pulse');
    $('#shipping-city-error').html('');
    // Пересчитываем доставку
    checkShipping();
    $('#cart-subtotal-shipping').show();
});
$(document).ready(toggleShippingAddressRequired);

// Валидация при отправке формы
$('#cart-home-form').on('submit', function(e) {
    if ($('input[name="data[cart_delivery_radio]"]:checked').val() == '3') {
        // Проверка города
        if (!$('input[name="data[shipping_city]"]:checked').length) {
            $('.cart-delivery-label input[name="data[shipping_city]"]').parent().addClass('is-invalid-pulse');
            if (!$('#shipping-city-error').html()) {
                $('#shipping-city-error').html('Izvēlieties pilsētu!');
            }
            e.preventDefault();
            return false;
        }
    }
}); 
