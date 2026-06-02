<?php

namespace App\Services;

class PriceCalculationService
{
    /**
     * Рассчитывает итоговую сумму заказа с учетом всех параметров
     * 
     * @param float $baseSum Базовая сумма заказа
     * @param string|null $promoType Тип промокода (percentage/fixed)
     * @param float|null $promoValue Значение промокода
     * @param float|null $deliveryPrice Стоимость доставки
     * @param float|null $mountingPrice Стоимость монтажа
     * @return float Итоговая сумма заказа
     */
    public function calculateTotalPrice($baseSum, $promoType = null, $promoValue = null, $deliveryPrice = null, $mountingPrice = null)
    {
        $total = $baseSum;

        // Применяем скидку если есть
        if ($promoType && $promoValue) {
            if ($promoType === 'percentage') {
                $total = $total * (1 - ($promoValue / 100));
            } else if ($promoType === 'fixed') {
                $total = $total - $promoValue;
            }
        }

        // Добавляем стоимость доставки
        if ($deliveryPrice) {
            $total += $deliveryPrice;
        }

        // Добавляем стоимость монтажа
        if ($mountingPrice) {
            $total += $mountingPrice;
        }

        return round($total, 2);
    }

    /**
     * Рассчитывает сумму скидки
     * 
     * @param float $totalPrice Общая сумма
     * @param string|null $discountType Тип скидки
     * @param float|null $discountValue Значение скидки
     * @return float Сумма скидки
     */
    public function calculateDiscountAmount($totalPrice, $discountType, $discountValue)
    {
        if (!$discountValue || $discountValue <= 0) {
            return 0;
        }

        return ($discountType == 'fixed')
            ? $discountValue
            : ($totalPrice * $discountValue) / 100;
    }
} 