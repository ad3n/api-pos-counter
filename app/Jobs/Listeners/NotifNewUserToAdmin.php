<?php

namespace App\Jobs\Listeners;

use App\Jobs\Events\NewRegister as Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\NewUserNotification;
use App\Onpose\Components\Shared\Repositories\StaffRepository;

class NotifNewUserToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Staff Repository
     *
     * @return StaffRepository
     */
    protected $repository;

    /**
     * Constructor
     */
    public function __construct(StaffRepository $repository)
    {
      $this->repository = $repository;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Onpose\Events\InvoiceProcess  $event
     * @return void
     */
    public function handle(Event $event)
    {
      $when = now()->addSeconds(30);
      // send notif to customer
      $user->notify(
        (new NewUserNotification($event->invoice, $event->status, $user->getName()))->delay($when)
      );
    }
}