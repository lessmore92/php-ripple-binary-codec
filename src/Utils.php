<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:42 PM
 */

namespace Lessmore92\RippleBinaryCodec;

class Utils
{
    public static function unsignedRightShift($a, $b)
    {
        if ($b >= 32 || $b < -32)
        {
            $m = (int)($b / 32);
            $b = $b - ($m * 32);
        }

        if ($b < 0)
        {
            $b = 32 + $b;
        }

        if ($b == 0)
        {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        if ($a < 0)
        {
            $a = ($a >> 1);
            $a &= 0x7fffffff;
            $a |= 0x40000000;
            $a = ($a >> ($b - 1));
        }
        else
        {
            $a = ($a >> $b);
        }
        return $a;
    }

    public static function decimalArrayToHexStr(array $decimal)
    {
        return join(array_map(function ($item) {
            return sprintf('%02X', $item);
        }, $decimal));
    }
}
