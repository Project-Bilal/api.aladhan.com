<?php

use function Mamluk\Kipchak\env;

return [
    'apikey' =>  env('GOOGLE_API_KEY', 'cf0561832a24e55fedaf201db7f1c2d2'),
    'geocode_baseurl' => env('GOOGLE_GEOCODE_BASEURL', 'https://maps.googleapis.com/maps/api/geocode/json'),
    'timezone_baseurl' => env('GOOGLE_TIMEZONE_BASEURL', 'https://maps.googleapis.com/maps/api/timezone/json'),
];