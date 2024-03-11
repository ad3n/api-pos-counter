<?php

namespace App\Jobs\Events;

use Illuminate\Queue\SerializesModels;

class NewRegister
{
    use SerializesModels;

    /**
     * User Model collection
     *
     * @var object
     */
    public $user;

    /**
     * Merchant Model collection
     *
     * @var object
     */
    public $merchant;
    
    /**
     * Create a new event instance.
     *
     * @param  \App\Model\User  $submission
     * @return void
     */
    public function __construct($user, $merchant)
    {
        $this->user = $user;
        $this->merchant = $merchant;
    }
}
