/**
 * debounce-functions.js
 * Вспомогательные функции для ограничения частоты вызовов
 */

// Проверяем, загружен ли файл с основными функциями корзины
document.addEventListener('DOMContentLoaded', function() {
    // Проверка пути страницы
    if (!window.location.pathname.includes('/shop')) {
        console.log("Страница не содержит '/shop', функции debounce-functions.js могут быть не нужны");
        return;
    }
    
    if (window.cartState && window.cartState.initialized) {
        console.log("Используются функции из cart-unified.js. Функции debounce-functions.js отключены.");
        return;
    }
    
    console.warn("Файл cart-unified.js не загружен или не активирован. Будут использованы функции из debounce-functions.js.");
    
    // Стандартная функция debounce для предотвращения множественных вызовов
    window.debounce = function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };
}); 