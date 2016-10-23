<?php

namespace Etu\Core\UserBundle\Model;

class CountriesManager
{
    protected static $initialized = false;
    protected static $countries = array();

    public static function getCountriesList()
    {
        if (!self::$initialized) {
            self::initialize();
        }

        return self::$countries;
    }

    protected static function initialize()
    {
        $countries = file_get_contents(__DIR__.'/../Resources/objects/countries.txt');
        $countries = explode("\n", $countries);

        foreach ($countries as $country) {
            self::$countries[trim($country)] = trim($country);
        }
    }
}
