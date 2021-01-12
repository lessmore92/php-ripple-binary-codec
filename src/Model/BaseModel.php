<?php
/**
 * User: Lessmore92
 * Date: 1/13/2021
 * Time: 2:27 AM
 */

namespace Lessmore92\RippleBinaryCodec\Model;

abstract class BaseModel
{
    protected $attributes = [];

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function __set($name, $value)
    {
        return $this->attributes[$name] = $value;
    }
}
