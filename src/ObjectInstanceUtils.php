<?php

namespace CEhlers\Shortcode;

class ObjectInstanceUtils
{
    private static int $lastInstanceId=0;

    public static function generateInstanceId():int {
        self::$lastInstanceId++;
        return self::$lastInstanceId;
    }
}
