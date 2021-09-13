<?php

namespace CEhlers\Shortcode;

class DomNode extends AbstractFragmentObject
{
    public function getTagStart(): string
    {
        return "<";
    }

    public function getTagEnd(): string
    {
        return ">";
    }

    public function getTagClose(): string
    {
        return "/";
    }
}