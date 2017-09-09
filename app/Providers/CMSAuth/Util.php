<?php

namespace TKAccounts\Providers\CMSAuth;

class Util
{
    public static function slugify($str)
    {
        $slug = strtolower(trim($str));
        $slug = preg_replace("/[^a-zA-Z0-9[:space:]\/s-]/", '', $slug);
        $slug = preg_replace("/(-| |\/)+/", '-', $slug);

        return $slug;
    }
}
