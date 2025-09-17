<?php

namespace Api\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Request as ApiRequest;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Timezone
{

    /**
     * Timezones list with GMT offset
     *
     * @return array
     * @link http://stackoverflow.com/a/9328760
     */
    public static function tz_list(): array
    {
        $zones_array = array();
        $timestamp = time();

        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp))/100;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return $zones_array;
    }

    /**
     * Checks if a timezone is valid
     * @param  String  $timezone Example: Europe/London
     * @return boolean
     */
    public static function isTimeZoneValid(?string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC));
    }

    /**
     * Builds list of Timezone Abbreviations against times
     * @param array    $timings An Array containing prayer timings
     * @param DateTime $date
     */
    public static function addTimezoneAbbreviation(array $timings, \DateTime $date): array
    {
        foreach ($timings as $key => $time) {
            $timings[$key] = $time . ' ('. $date->format('T') . ')';
        }

        return $timings;
    }

    /**
     * Get GMT offset for a timezone
     * @param String  $timezoneString
     * @param DateTime $date
     */
    public static function getTimeZoneOffsetString(string $timezoneString, \DateTime $date): int
    {
        $tz = new \DateTimeZone($timezoneString);
        return $tz->getOffset($date)/3600;
    }

    /**
     * Computes timezone only if the passed timezone is empty or null
     * @param  [type] $latitude  [description]
     * @param  [type] $longitude [description]
     * @param  [type] $timezone  [description]
     * @param  [type] $locations Locations Model Objext
     * @return String           [description]
     */
    public static function computeTimezone(float $latitude, float $longitude, ?string $timezone, string $apikey, string $timezoneBaseUrl, ServerRequestInterface $request): null|string
    {
        //Compute only if timezone is empty or null
        if ($timezone == '' || $timezone  === null) {
            // Compute it.
            if (ApiRequest::isLatitudeValid($latitude) && ApiRequest::isLongitudeValid($longitude)) {
                // Build Google Timezone API URL
                $timestamp = time(); // Current UTC timestamp
                $url = "https://maps.googleapis.com/maps/api/timezone/json?location={$latitude},{$longitude}&timestamp={$timestamp}&key={$apikey}";
                
                // Make the HTTP request
                $response = file_get_contents($url);
                $data = json_decode($response, true);
                
                // Handle the response
                if ($data && $data['status'] === 'OK' && isset($data['timeZoneId'])) {
                    return $data['timeZoneId']; // Return timezone like "America/Los_Angeles"
                } else {
                    throw new HttpBadRequestException($request, 'Invalid coordinates. Could not compute timezone.');
                }
            }

            // If we get here it means that we could not calculate the timezone. Just return UTC in this case.
            return 'UTC';
        }

        return $timezone;
    }
}
