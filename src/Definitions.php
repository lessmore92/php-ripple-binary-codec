<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:43 PM
 */

namespace Lessmore92\RippleBinaryCodec;

use Lessmore92\Buffer\Buffer;
use Lessmore92\RippleBinaryCodec\Model\Bytes;
use Lessmore92\RippleBinaryCodec\Model\Field;

define('TRANSACTION_TYPE_WIDTH', 2);

class Definitions
{
    private static $initialized = false;
    private static $defs;
    private static $types;
    private static $fields;
    private static $transactionTypes;

    public static function getType($type): Bytes
    {
        if (!self::$initialized)
        {
            self::$initialized = true;
            self::init();
        }
        return isset(self::$types[$type]) ? self::$types[$type] : new Bytes(0, 0, 0);
    }

    private static function init()
    {
        $file       = __DIR__ . '/definitions.json';
        self::$defs = json_decode(file_get_contents($file));

        self::loadTypes();
        self::loadFields();
        self::loadTransactionTypes();
    }

    private static function loadTypes()
    {
        $types = [];
        foreach (self::$defs->TYPES as $name => $TYPE)
        {
            $_type        = new Bytes($name, $TYPE, TRANSACTION_TYPE_WIDTH);
            $types[$name] = $_type;
            $types[$TYPE] = $_type;
        };
        self::$types = $types;
    }

    private static function loadFields()
    {
        $fields = [];
        foreach (self::$defs->FIELDS as $FIELD)
        {
            $fields[$FIELD[0]] = Field::fromJson($FIELD[1]);

        };
        self::$fields = $fields;
    }

    private static function loadTransactionTypes()
    {
        $types = [];
        foreach (self::$defs->TRANSACTION_TYPES as $key => $TYPE)
        {
            $types[$key] = Buffer::hex(sprintf('%04X', $TYPE));
        };
        self::$transactionTypes = $types;
    }

    public static function getField($name): Field
    {
        if (!self::$initialized)
        {
            self::$initialized = true;
            self::init();
        }
        return isset(self::$fields[$name]) ? self::$fields[$name] : new Field;
    }

    public static function getTransactionType($type): Buffer
    {
        if (!self::$initialized)
        {
            self::$initialized = true;
            self::init();
        }
        return isset(self::$transactionTypes[$type]) ? self::$transactionTypes[$type] : new Buffer();
    }
}

