<?php

namespace App\Jobs\Events;

use Illuminate\Queue\SerializesModels;

class NewReferral
{
    use SerializesModels;

    /**
     * User Model collection
     *
     * @var array
     */
    public $user;
    
    /**
     * Create a new event instance.
     *
     * @param  \App\Model\User  $submission
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
