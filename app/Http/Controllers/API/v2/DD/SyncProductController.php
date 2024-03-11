<?php

namespace App\Http\Controllers\API\v2\DD;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Validator;
use Auth;

class SyncProductController extends Controller
{   
    /**
     * Client Instance
     *
     * @var object
     */
    protected $client;

    protected $headers;

     /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => config('portalpulsa.base_uri'),
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $userid = config('portalpulsa.userid');
        $key = config('portalpulsa.key');
        $secret = config('portalpulsa.secret');

        $this->headers = [
            "portal-userid: {$userid}",
            "portal-key: {$key}", 
            "portal-secret: {$secret}"
        ];
            
    }

    public function getPriceFromPortalPulsa( Request $request )
    {   
        /*$promise = $this->client->request('POST', '/', 
            [
                'stream' => false,
                'curl' => [
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_HTTPHEADER => $this->headers,
                    CURLOPT_POSTFIELDS => [
                        'inquiry' => 'HARGA', // konstan
                        'code' => 'PULSA', // pilihan: pln, pulsa, game
                    ]
                ]
            ]
        );*/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('portalpulsa.base_uri') );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  [
            'inquiry'   => 'HARGA', // konstan
            'code'      => 'PULSA', // pilihan: pln, pulsa, game
        ]);
        $result = curl_exec($ch);

        dump(json_decode($result, true));

        /*$promise->then(
            function (ResponseInterface $res) {
                dump($res) . "\n";
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );*/
    }
}