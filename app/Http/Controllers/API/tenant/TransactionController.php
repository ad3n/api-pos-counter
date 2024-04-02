<?php

namespace App\Http\Controllers\API\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use App\Interfaces\Constants;
use Symfony\Component\HttpKernel\Exception\HttpException;


class TransactionController extends Controller implements Constants
{
    /**
     * Engine Repository
     *
     * @author Dian Afrial
     * @return object
     */
    protected $engine;

    /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(TransactionRepository $engine)
    {
        $this->engine = $engine;
    }

    public function getProvider(Request $request)
    {
        try {
            // add new supplier
            $res = $this->engine->getProvider();

            // if success throw 200 OK
            return response()->json([
                'success'   => true,
                'data'      => $res
            ], 200);

        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }
}
