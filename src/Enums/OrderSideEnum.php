<?php
namespace Kosar501\MexcPhpApi\Enums;
class OrderSideEnum
{

    const BUY = 'BUY';
    const SELL = 'SELL';


    /**
     * Gets constants list
     * @return array
     */
    public static function keys()
    {
        return [
            self::BUY,
            self::SELL
        ];
    }

}
