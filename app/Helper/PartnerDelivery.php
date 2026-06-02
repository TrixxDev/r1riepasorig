<?php

namespace App\Helper;

/**
 * Partner warehouse delivery text for yellow-dot tooltips (auto, moto, quad, industrial).
 */
class PartnerDelivery
{
    private const TIER_ONE_DAY = 1;
    private const TIER_TWO_THREE_DAYS = 2;
    private const TIER_FOUR_FIVE_DAYS = 3;

    /** itype => fastest delivery tier when that partner has stock */
    private const ITYPE_TIER = [
        'i3' => self::TIER_ONE_DAY,       // Latakko
        'rz' => self::TIER_ONE_DAY,       // RiepuZona
        'rg' => self::TIER_ONE_DAY,       // Riepu Garāža
        'gy' => self::TIER_TWO_THREE_DAYS, // GoodYear
        'starco' => self::TIER_TWO_THREE_DAYS, // Bohnenkamp (industrial)
        'bk' => self::TIER_TWO_THREE_DAYS,
        'bohnenkamp' => self::TIER_TWO_THREE_DAYS,
        'duell' => self::TIER_FOUR_FIVE_DAYS,
    ];

    private const DELIVERY_MESSAGES = [
        self::TIER_ONE_DAY => 'Piegāde 1 darbadienas laikā.',
        self::TIER_TWO_THREE_DAYS => 'Piegāde 2-3 darbadienu laikā.',
        self::TIER_FOUR_FIVE_DAYS => 'Piegāde 4-5 darbadienu laikā.',
    ];

    /**
     * @param iterable $stocks Rows with itype and quantity (partner stock models).
     */
    public static function resolveDeliveryMessage(iterable $stocks): ?string
    {
        $inStock = [];
        foreach ($stocks as $stock) {
            if ($stock !== null && (int) $stock->quantity > 0) {
                $inStock[] = (string) $stock->itype;
            }
        }

        if ($inStock === []) {
            return null;
        }

        // Latakko + DUELL: show 1 business day even though DUELL alone is 4-5 days.
        if (in_array('i3', $inStock, true) && in_array('duell', $inStock, true)) {
            return self::DELIVERY_MESSAGES[self::TIER_ONE_DAY];
        }

        $bestTier = self::TIER_FOUR_FIVE_DAYS;
        foreach ($inStock as $itype) {
            $tier = self::ITYPE_TIER[$itype] ?? self::TIER_TWO_THREE_DAYS;
            if ($tier < $bestTier) {
                $bestTier = $tier;
            }
        }

        return self::DELIVERY_MESSAGES[$bestTier];
    }

    /**
     * @param iterable $stocks
     * @param string $wrapper HTML tag: span or p
     */
    public static function partnerAvailabilityHtml(iterable $stocks, string $wrapper = 'span'): string
    {
        $delivery = self::resolveDeliveryMessage($stocks);
        if ($delivery === null) {
            return '';
        }

        $tag = $wrapper === 'p' ? 'p' : 'span';

        return '<' . $tag . ' style="text-align: center;">Riepas pieejamas partneru noliktavās<br>'
            . $delivery . '</' . $tag . '>';
    }
}
