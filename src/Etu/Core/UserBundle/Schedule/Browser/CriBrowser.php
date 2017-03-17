<?php

namespace Etu\Core\UserBundle\Schedule\Browser;

/**
 * CRI's server browser.
 */
class CriBrowser
{
    public const ROOT_URL = 'http://edt.utt.fr/ung/site-etu-api/app.php';

    public function request(array $parameters = [])
    {
        $get = '';

        foreach ($parameters as $key => $value) {
            $get .= $key.'='.$value.'&';
        }

        return file_get_contents(self::ROOT_URL.'?'.mb_substr($get, 0, -1));
    }
}
