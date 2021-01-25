<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:39 PM
 */

namespace Lessmore92\RippleBinaryCodec;

define('ARRAY_END_MARKER', 0xf1);
define('OBJECT_END_MARKER_BYTE', 0xe1);

use BN\BN;
use Exception;
use Lessmore92\Buffer\Buffer;
use Lessmore92\RippleAddressCodec\RippleAddressCodec;
use Lessmore92\RippleBinaryCodec\Model\Field;

class TxSerializer
{
    private $addressCodec;

    public function __construct()
    {
        $this->addressCodec = new RippleAddressCodec();
    }

    public function SerializeTx($tx, $options)
    {
        $properties = [];
        foreach (json_decode(json_encode($tx), true) as $name => $item)
        {
            $property        = Definitions::getField($name);
            $property->name  = $name;
            $property->value = $item;

            $properties[] = $property;
        }
        $filtered = array_filter($properties, function (Field $item) {
            return $item->isSerialized;
        });

        usort($filtered, function (Field $a, Field $b) {
            return $a->ordinal - $b->ordinal;
        });

        if (isset($options['signingFieldsOnly']))
        {
            $filtered = array_filter($filtered, function (Field $item) {
                return $item->isSigningField;
            });
        }

        $bytes = [];
        /**
         * @var Field $item
         */
        foreach ($filtered as $item)
        {
            $value = $this->encodeValue($item)
                          ->getHex()
            ;

            $bytes[] = $item->header->getHex();
            if ($item->isVariableLengthEncoded)
            {
                $bytes[] = Utils::decimalArrayToHexStr($this->encodeVariableLength(strlen($value) / 2));
            }
            $bytes[] = $value;

            if ($item->type->name == 'STObject')
            {
                $bytes[] = Buffer::int(OBJECT_END_MARKER_BYTE)
                                 ->getHex()
                ;
            }
        }

        return Buffer::hex(join($bytes));
    }

    public function encodeValue(Field $item)
    {
        if ($item->name == 'TransactionType')
        {
            return $this->encodeTransactionType($item->value);
        }
        else if ($item->type->name == 'UInt32')
        {
            return $this->encodeUInt32($item->value);
        }
        else if ($item->type->name == 'Amount')
        {
            return $this->encodeAmount($item->value);
        }
        else if ($item->type->name == 'Blob')
        {
            return $this->encodeBlob($item->value);
        }
        else if ($item->type->name == 'AccountID')
        {
            return $this->encodeAccountID($item->value);
        }
        else if ($item->type->name == 'STArray')
        {
            return $this->encodeSTArray($item->value);
        }
        else if ($item->type->name == 'STObject')
        {
            return $this->encodeSTObject($item->value);
        }
        else
        {
            throw new Exception(sprintf('field %s not supported field', $item->name));
        }
    }

    public function encodeTransactionType($type)
    {
        return Definitions::getTransactionType($type);
    }

    public function encodeUInt32($value)
    {
        return Buffer::hex(sprintf('%08X', $value));
    }

    public function encodeAmount($value)
    {
        $mask   = new BN(0x00000000ffffffff);
        $number = new BN($value);

        $intBuf = [
            sprintf('%08X', $number->shrn(32)
                ->toString()),
            sprintf('%08X', $number->iand($mask)
                ->toString()),
        ];

        $amount = Buffer::hex(join($intBuf));

        $amount    = $amount->getDecimal();
        $amount[0] |= 0x40;

        $amount = join(array_map(function ($item) {
            return sprintf('%02X', $item);
        }, $amount));
        return Buffer::hex($amount);
    }

    public function encodeBlob($value)
    {
        return Buffer::hex($value);
    }

    public function encodeAccountID($value)
    {
        if ($value == '')
        {
            Buffer::hex(str_repeat('0', 40));
        }

        return preg_match('/^[A-F0-9]{40}$/', $value) ? Buffer::hex($value) : $this->addressCodec->decodeAccountID($value);
    }

    public function encodeVariableLength(int $length)
    {
        $lenBytes = Buffer::hex('000000')
            ->getDecimal()
        ;

        if ($length <= 192)
        {
            $lenBytes[0] = $length;
            return array_slice($lenBytes, 0, 1);
        }
        else if ($length <= 12480)
        {
            $length      -= 193;
            $lenBytes[0] = 193 + Utils::unsignedRightShift($length, 8);
            $lenBytes[1] = $length & 0xff;
            return array_slice($lenBytes, 0, 2);
        }
        else if ($length <= 918744)
        {
            $length      -= 12481;
            $lenBytes[0] = 241 + Utils::unsignedRightShift($length, 16);
            $lenBytes[1] = ($length >> 8) & 0xff;
            $lenBytes[2] = $length & 0xff;
            return array_slice($lenBytes, 0, 3);
        }
        throw new Exception("Overflow error");
    }

    public function encodeSTArray($array)
    {
        $bytes = [];
        foreach ($array as $_obj)
        {
            $bytes = array_merge($bytes, $this->SerializeTx($_obj, [])
                                              ->getDecimal());
        }

        $bytes[] = ARRAY_END_MARKER;
        return Buffer::hex(Utils::decimalArrayToHexStr($bytes));
    }

    public function encodeSTObject($obj)
    {
        return $this->SerializeTx($obj, []);
    }
}
