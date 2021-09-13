<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\Shortcode;
use CEhlers\Shortcode\TextFragment;

interface IConverterRule
{
    public function canHandle(Shortcode $shortcode):bool;
    public function handle(Shortcode $shortcode):TextFragment;
}