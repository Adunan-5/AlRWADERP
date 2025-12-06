<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('gregorian_to_hijri')) {
    function gregorian_to_hijri($dateString, $timezone = 'Asia/Riyadh')
    {
        try {
            // Parse the date string and set time to noon to avoid day boundary issues
            $date = new DateTime($dateString, new DateTimeZone($timezone));
            $date->setTime(12, 0, 0); // Set to noon (12:00:00) to avoid boundary issues

            $formatter = new IntlDateFormatter(
                'en_US@calendar=islamic-umalqura',
                IntlDateFormatter::FULL,
                IntlDateFormatter::NONE,
                $timezone,
                IntlDateFormatter::TRADITIONAL,
                'yyyy-MM-dd'
            );

            $hijriDate = $formatter->format($date);

            // Fix: Add one day to match standard Hijri converters
            // IntlDateFormatter seems to be off by one day
            $hijriDateTime = new DateTime($hijriDate);
            $hijriDateTime->modify('+1 day');

            return $hijriDateTime->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}
