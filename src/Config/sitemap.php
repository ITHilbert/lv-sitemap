<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard-Dateiname
    |--------------------------------------------------------------------------
    | Name der Sitemap-Datei, die standardmäßig unter public/ generiert wird.
    | Routen ohne expliziten Dateinamen landen in dieser Datei.
    */
    'default_filename' => 'sitemap.xml',

    /*
    |--------------------------------------------------------------------------
    | Standard-Priorität
    |--------------------------------------------------------------------------
    | Wert zwischen 0.0 und 1.0. Wird verwendet, wenn eine Route mit
    | ->sitemap() ohne explizite Priorität deklariert wird.
    */
    'default_priority' => '0.5',

    /*
    |--------------------------------------------------------------------------
    | Standard-Änderungsfrequenz
    |--------------------------------------------------------------------------
    | Erlaubte Werte: always, hourly, daily, weekly, monthly, yearly, never
    */
    'default_changefreq' => 'monthly',

    /*
    |--------------------------------------------------------------------------
    | Standard-Lastmod
    |--------------------------------------------------------------------------
    | Wenn true, wird das aktuelle Datum als lastmod-Datum in die Sitemap
    | geschrieben. Kann auch ein festes Datum (string) gesetzt werden.
    */
    'default_lastmod' => true,

    /*
    |--------------------------------------------------------------------------
    | Basis-URL
    |--------------------------------------------------------------------------
    | Wird aus der APP_URL-Umgebungsvariable gelesen.
    */
    'url_base' => env('APP_URL', 'http://localhost'),

];
