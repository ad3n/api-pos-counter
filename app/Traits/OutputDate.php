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
            'raw'  => $this->iso8601String($date),
            'long' => $this->firstLocaldate($date, false, true),
            'short' => $this->firstLocaldate($date, true, true)
        ];
    }

    public function iso8601String($date): string
    {
        $country_id = 'id_Date';
        $tz_id = 'Asia/Jakarta';

        if( method_exists($this, 'country') ) {
            if( $this->country()->first() && optional($this->country()->first())->locale ) {
                $country_id = $this->country()->first()->locale;
            }

            if( $this->country()->first() && optional($this->country()->first())->timezone ) {
                $tz_id = $this->country()->first()->timezone;
            }
        }

        $carbon = Carbon::parse($date)->setTimezone( $tz_id );


        return $carbon->toIso8601String();
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
        $country_id = 'id_Date';
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
            $carbon = Carbon::parse($date)->locale($country_id)->setTimezone( $tz_id );
        } else {
            $carbon = Carbon::now()->locale($country_id)->setTimezone( $tz_id );
        }

        // if( $short ) {
        //     $format = '%d %b %Y';
        //     if( $day_name ) {
        //         $format = '%a, %d %B %Y %H:%M';
        //     }
        // } else {
        //     $format = '%d %B %Y';
        //     if( $day_name ) {
        //         $format = '%A, %d %B %Y %H:%M';
        //     }
        // }

        if( $short ) {
            $format = "{$carbon->day} {$carbon->shortMonthName} {$carbon->year}";
            if( $day_name ) {
                $format =  "{$carbon->shortDayName}, {$carbon->day} {$carbon->shortMonthName} {$carbon->year} {$carbon->format('H:i')}";
            }
        } else {
            $format = "{$carbon->day} {$carbon->monthName} {$carbon->year}";
            if( $day_name ) {
              $format = "{$carbon->dayName}, {$carbon->day} {$carbon->monthName} {$carbon->year} {$carbon->format('H:i')}";
            }
        }


        return $format;
    }
}
