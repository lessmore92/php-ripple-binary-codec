<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:37 PM
 */

namespace Lessmore92\RippleBinaryCodec;


class Binary
{
    public function signingData($transaction, $prefix = null)
    {
        if (is_null($prefix))
        {
            $prefix = HashPrefixes::TransactionSig();
        }

        return $this->serializeObject($transaction, ['prefix' => $prefix, 'signingFieldsOnly' => true]);
    }

    public function serializeObject($json, $options)
    {
        $out    = [];
        $prefix = $options['prefix'];
        if ($prefix)
        {
            $out[] = $prefix->getHex();
        }
        $ser   = new TxSerializer();
        $out[] = $ser->SerializeTx($json)
                     ->getHex()
        ;

        return strtoupper(join($out));
    }
}
