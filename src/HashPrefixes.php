<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:37 PM
 */

namespace Lessmore92\RippleBinaryCodec;

use Lessmore92\Buffer\Buffer;

class HashPrefixes
{
    private static $transactionSig = 0x53545800;

    public static function TransactionSig(): Buffer
    {
        return Buffer::int(static::$transactionSig);
    }
}
