<?php

namespace CEhlers\Shortcode;

class Shortcode extends AbstractFragmentObject
{
    public function getTagStart(): string
    {
        return "[";
    }

    public function getTagEnd(): string
    {
        return "]";
    }

    public function getTagClose(): string
    {
        return "/";
    }

    public function getFragmentTypeName(): string
    {
        return "Shortcode";
    }
}