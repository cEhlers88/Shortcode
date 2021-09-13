<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\AbstractFragmentObject;
use CEhlers\Shortcode\Shortcode;
use CEhlers\Shortcode\TextFragment;

interface IConverterRule
{
    public function canHandle(AbstractFragmentObject $shortcode):bool;
    public function handle(AbstractFragmentObject $shortcode):TextFragment;
}