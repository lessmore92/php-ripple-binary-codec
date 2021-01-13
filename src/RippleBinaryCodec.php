<?php
/**
 * User: Lessmore92
 * Date: 1/12/2021
 * Time: 3:35 PM
 */

namespace Lessmore92\RippleBinaryCodec;

class RippleBinaryCodec
{
    private $binary;

    public function __construct()
    {
        $this->binary = new Binary();
    }

    public function encodeForSigning($json)
    {
        return $this->binary->signingData($json);
    }

    public function encode($json)
    {
        return $this->binary->serializeObject($json, []);
    }
}
