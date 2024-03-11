<?php

namespace App\Traits;

use Carbon\Carbon;

trait OutputDate {

    /**
     * Encapsulate date output to raw, long date, and short date
     */
    public function encapsulateDate($date)
    {
        return [
            'raw'  => $date,
            'long' => $this->firstLocaldate($date, false, true),
            'short' => $this->firstLocaldate($date, true, true)
        ];
    }

    /**
     * Get local date whene prepare authenticating
     *
     * @param string $date
     * @param boolean $short
     * @param boolean $day_name
     * @return string
     */
    protected function firstLocaldate($date = null, $short = false, $day_name = false)
    {
        $country_id = 'id';
        $tz_id = 'Asia/Jakarta';

        if( method_exists($this, 'country') ) {
            if( $this->country()->first() && optional($this->country()->first())->locale ) {
                $country_id = $this->country()->first()->locale;
            }

            if( $this->country()->first() && optional($this->country()->first())->timezone ) {
                $tz_id = $this->country()->first()->timezone;
            }
        }

        Carbon::setLocale( $country_id );
        
        if( $date ) {
            $carbon = Carbon::parse($date)->setTimezone( $tz_id );
        } else {
            $carbon = Carbon::now()->setTimezone( $tz_id );
        }

        if( $short ) {
            $format = '%d %b %Y';
            if( $day_name ) {
                $format = '%a, %d %B %Y %H:%M';
            }
        } else {
            $format = '%d %B %Y';
            if( $day_name ) {
                $format = '%A, %d %B %Y %H:%M';
            }
        }
        
        return $carbon->formatLocalized($format);
    }
}