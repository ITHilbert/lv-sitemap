<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Standard-Dateiname
    |--------------------------------------------------------------------------
    | Routen ohne expliziten Dateinamen landen in dieser Datei (unter public/).
    */
    'default_filename' => 'sitemap.xml',

    /*
    |--------------------------------------------------------------------------
    | Standard-Priorität
    |--------------------------------------------------------------------------
    | Wert zwischen 0.0 und 1.0.
    */
    'default_priority' => '0.5',

    /*
    |--------------------------------------------------------------------------
    | Standard-Änderungsfrequenz
    |--------------------------------------------------------------------------
    | Erlaubt: always, hourly, daily, weekly, monthly, yearly, never
    */
    'default_changefreq' => 'monthly',

    /*
    |--------------------------------------------------------------------------
    | Standard-Lastmod
    |--------------------------------------------------------------------------
    | true       = aktuelles Datum als ATOM-String
    | false/null = kein lastmod-Tag in der Sitemap
    | string     = fixer ISO-8601-String
    */
    'default_lastmod' => true,

    /*
    |--------------------------------------------------------------------------
    | Basis-URL
    |--------------------------------------------------------------------------
    */
    'url_base' => env('APP_URL', 'http://localhost'),

];
