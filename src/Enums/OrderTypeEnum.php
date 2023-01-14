<?php
namespace Kosar501\MexcPhpApi\Enums;
class OrderTypeEnum
{

    const LIMIT = 'LIMIT';
    const MARKET = 'MARKET';
    const LIMIT_MAKER = 'LIMIT_MAKER';
    const IMMEDIATE_OR_CANCEL = 'IMMEDIATE_OR_CANCEL';
    const FILL_OR_KILL = 'FILL_OR_KILL';


    /**
     * Gets constants list
     * @return array
     */
    public static function keys()
    {
        return [
            self::LIMIT,
            self::MARKET,
            self::LIMIT_MAKER,
            self::IMMEDIATE_OR_CANCEL,
            self::FILL_OR_KILL,
        ];
    }
}
