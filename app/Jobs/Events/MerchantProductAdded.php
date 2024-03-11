<?php

namespace App\Jobs\Events;

use Illuminate\Queue\SerializesModels;

class MerchantProductAdded
{
    use SerializesModels;

    /**
     * User Model collection
     *
     * @var array
     */
    public $repository;
    
    /**
     * Create a new event instance.
     *
     * @param  \App\Model\User  $submission
     * @return void
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }
}
