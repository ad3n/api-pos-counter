<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\SuperRepository;
use DB;

class RefreshTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truncate:transactions {merchant_id=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all data transctions';

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
        $merchant_id = $this->argument('merchant_id');

        $security = $this->secret('Password security?');
        if($security !== 'qkjoss' ) {
            return false;
        }
        
        if ($this->confirm('Are you sure truncate?') ) { 
            if( $merchant_id == 'all' ) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table("transaction_saldos")->truncate();
                DB::table("transaction_items")->truncate();
                DB::table("transactions")->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                DB::table('saldos')->update(['usage' => 0, 'closed_at' => null]);

                $this->info("Great! Truncate all data transactions");
            } else if( $merchant_id !== 'all' && $merchant_id > 0 ) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table("transactions")->where("merchant_id", $merchant_id)->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                DB::table('saldos')->where()->update(['usage' => 0, 'closed_at' => null]);
            }

        }
       
    }
}
