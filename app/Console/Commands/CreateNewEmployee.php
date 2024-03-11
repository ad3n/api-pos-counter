<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\EmployeeRepository;
use Illuminate\Foundation\Testing\HttpException;
use Carbon\Exceptions\InvalidDateException;
use Carbon\Carbon;

class CreateNewEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:new {merchant_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new employee';

    /**
     * The repository.
     *
     * @var EmployeeRepository
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EmployeeRepository $repository)
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
        $merchant_id = $this->argument('merchant_id');
        $phone = $this->ask('Your phone?');
        $name = $this->ask("Your name?");
        $role = $this->choice('What is role?', ['staff', 'manager', 'administrator']);
        $email = $this->ask("Your email?");
        $password = $this->secret("Your password?");

        // execute method
        if ($phone && $name && $role && $email && $password) {
            if ($role == 'administrator') {
                $begunAt = date('07:00:00');
                $exitedAt = date('03:00:00');
            } else {
                $inputBegunAt = $this->ask("Jam mulai kerja?");
                $inputExitedAt = $this->ask("Jam pulang kerja?");

                try {
                    Carbon::setLocale(get_locale());
                    $begunAtCarbon =  Carbon::parse($inputBegunAt)->setTimezone(get_timezone());
                    $begunAt = $begunAtCarbon->format('H:i:s');

                    $exitedAtCarbon =  Carbon::parse($inputExitedAt)->setTimezone(get_timezone());
                    $exitedAt = $exitedAtCarbon->format('H:i:s');
                } catch (InvalidDateException $e) {
                    $this->error($e->getMessage());
                    return;
                }
            }

            try {
                $data = $this->repository->createUser([
                    'merchant_id' => $merchant_id,
                    'no_hp' => $phone,
                    'name' => $name,
                    'role' => $role,
                    'email' => $email,
                    'password' => $password,
                    'begun_at' => $begunAt,
                    'exited_at' => $exitedAt
                ]);
            } catch (HttpException $th) {
                $this->error($th->getMessage());
            }
        } else {
            $this->error("No data input");
        }

        if (!$data) {
            $this->error('something wrong or invalid data request');
            return;
        }

        $this->info("info : " . $data);
    }
}
