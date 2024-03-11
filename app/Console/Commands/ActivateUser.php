<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\UserRepository;

class ActivateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:activate {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate an user';

    /**
     * The repository.
     *
     * @var UserRepository
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $username = $this->argument('phone');
     
        // execute method
        $data = $this->repository->activateUser($username);

        if( ! $data ) {
          $this->error('something wrong or invalid data request');
          return;
        } 

        $this->info("info : " . $data );
       
    }
}
