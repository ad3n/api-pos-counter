<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\SuperRepository;

class AddRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role {name} {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new role';

    /**
     * The repository.
     *
     * @var SuperRepository
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SuperRepository $repository)
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
        $name = $this->argument('name');
        $slug = $this->argument('slug');
     
        // execute method
        $data = $this->repository->createRoleCommand($name, $slug);

        if( ! $data ) {
          $this->error('something wrong or invalid data request');
          return;
        } 

        $this->info("info : " . $data );
       
    }
}
