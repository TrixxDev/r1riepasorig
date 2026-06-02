<?php

namespace App\Services;

class ShippingService
{
    /**
     * Получить все варианты доставки для разных типов шин
     *
     * @return array
     */
    public static function getOptions(): array
    {
        $shippingOptions = [
            'Autotire' => [
                'shipping' => self::getShippingOptions('autotire'),
                'fitting' => self::getFittingOptions('autotire'),
                'fitting_suv' => self::getFittingOptions('suv_autotire'),
            ],
            'Moto' => [
                'shipping' => self::getShippingOptions('moto'),
                'fitting' => self::getFittingOptions('moto'),
            ],
            'Quadr' => [
                'shipping' => self::getShippingOptions('autotire'),
                'fitting' => self::getFittingOptions('quadr'),
            ],
            'Bigtire' => [
                'shipping' => self::getShippingOptions('industrial'),
            ],
        ];

        return array_merge(['shippingDef' => config('app.settings.shippingDef')], $shippingOptions);
    }

    /**
     * Получить опции доставки для определенного типа шин
     *
     * @param string $type
     * @return array
     */
    private static function getShippingOptions(string $type): array
    {
            if ($type == 'moto') {
                $array = [
                    1 => config("app.settings.shipping_{$type}_one"),
                    2 => config("app.settings.shipping_{$type}_two"),
                    3 => config("app.settings.shipping_{$type}_many"),
		];
	    } else {
		$array = [
		    1 => config("app.settings.shipping_{$type}_one"),
                    2 => config("app.settings.shipping_{$type}_two"),
                    4 => config("app.settings.shipping_{$type}_four"),
                    5 => config("app.settings.shipping_{$type}_many"),
	        ];
	    }
	    return $array;
    }

    /**
     * Получить опции монтажа для определенного типа шин
     *
     * @param string $type
     * @return array
     */
    private static function getFittingOptions(string $type): array
    {
        $fittingConfig = [];
	if ($type == 'autotire' || $type == 'suv_autotire') {
            for ($i = 16; $i <= 21; $i++) {
                $fittingConfig[$i] = [
                    1 => config("app.settings.fitting_{$type}_{$i}_one"),
                    2 => config("app.settings.fitting_{$type}_{$i}_two"),
                    4 => config("app.settings.fitting_{$type}_{$i}_four"),
                ];
            }
        } else if ($type == 'moto') {
            $fittingConfig = [
                1 => config("app.settings.fitting_{$type}_one"),
                2 => config("app.settings.fitting_{$type}_two"),
            ];
        } else {
            $fittingConfig = [
                1 => config("app.settings.fitting_{$type}_one"),
                2 => config("app.settings.fitting_{$type}_two"),
                4 => config("app.settings.fitting_{$type}_four"),
            ];
        }
        return $fittingConfig;
    }
    
    /**
     * Определить, является ли шина внедорожной (SUV)
     *
     * @param int $width Ширина шины
     * @param int $height Высота шины
     * @param int $size Диаметр шины
     * @param float $radiusBorder Граничное значение радиуса
     * @return bool
     */
    public function isSuvTire($width, $height, $size, $radiusBorder = 360.7): bool
    {
        $radius = (($width * ($height / 100)) * 2) + ($size * 25.4);
        return ($radius / 2) >= $radiusBorder;
    }
    
    /**
     * Рассчитать стоимость монтажа
     *
     * @param string $category Категория шины
     * @param int $size Размер шины
     * @param int $quantity Количество шин
     * @param bool $suvTire Флаг внедорожной шины
     * @return float Стоимость монтажа
     */
    public function calculateFittingPrice($category, $size, $quantity, $suvTire)
    {
        $options = self::getOptions();
        
        if ($category == 'Autotire') {
            if (!$suvTire) {
                if ($size == 16) {
                    return $options[$category]['fitting'][16][$quantity];
                } elseif ($size == 17 || $size == 18) {
                    return $options[$category]['fitting'][17][$quantity];
                } elseif ($size == 19 || $size == 20) {
                    return $options[$category]['fitting'][19][$quantity];
                } elseif ($size >= 21) {
                    return $options[$category]['fitting'][21][$quantity];
                } else {
                    return $options[$category]['fitting'][$size][$quantity];
                }
            } else {
                if ($size == 16) {
                    return $options[$category]['fitting_suv'][16][$quantity];
                } elseif ($size == 17 || $size == 18) {
                    return $options[$category]['fitting_suv'][17][$quantity];
                } elseif ($size == 19 || $size == 20) {
                    return $options[$category]['fitting_suv'][19][$quantity];
                } elseif ($size >= 21) {
                    return $options[$category]['fitting_suv'][21][$quantity];
                } else {
                    return $options[$category]['fitting_suv'][$size][$quantity];
                }
            }
        }

	if (!isset($options[$category]['fitting'])) {
            return 0.0;
        }

        return $options[$category]['fitting'][$quantity] ?? 0.0;
    }
} 
