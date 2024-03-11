<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use App\Model\Transaction;
use Carbon\Carbon;
use Auth;
use Validator;
use App\Model\Saldo;
use App\Model\TransactionItem;
use App\Model\Type;
use App\Model\CategorySelection;
use DB;
class V1Controller extends Controller
{
    protected function guard()
    {
        return Auth::guard('api');
    }

    // api type belonging to categories and products
    public function categories(){
        $data = Type::get();
        $result = [];
        $num = 0;
        foreach($data as $value){    
            $j = 0;
            $categories = [];
            foreach($value->categories as $item){
               $categories[$j]= [
                    'id' => $item->id,
                    'name'=> $item->name,                    
                    'created_at' => Carbon::parse($item->created_at)->format("Y-m-d h:i:s"),
                    'updated_at' => Carbon::parse($item->updated_at)->format("Y-m-d h:i:s"),
                    'products' => $item->products
                ];        
                $j++;
            }        
            $result[$num] = 
                [
                    'id' => $value->id,
                    'name'=> $value->name,                    
                    'created_at' => Carbon::parse($value->created_at)->format("Y-m-d h:i:s"),
                    'updated_at' => Carbon::parse($value->updated_at)->format("Y-m-d h:i:s"),
                    'categories' => $categories
                ];        
            $num ++;
        }
        return $result;
    }

    public function category_selections(Request $request){
        
        $validate = Validator::make($request->all(),[                    
            'categories' => 'required',
            'user_id' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{  

            $category_selection = CategorySelection::where('user_id', $request->user_id)->get();            
            if(count($category_selection) > 0){               
                $del = CategorySelection::where('user_id', $request->user_id)->delete();                               
            }

            $categories = $request->categories;
            $saved;
            for($i=0; $i < count($categories); $i++)
            {                

                $category = Category::where('id', $categories[$i]['id'])->get();

                foreach($category as $value)
                {
                    $data = new CategorySelection;
                    $data->user_id = $request->user_id;
                    $data->type_id = $value->type_id;
                    $data->category_id = $value->id;
                    $saved = $data->save();        
                }
            }            
            if($saved){
                $selections = CategorySelection::where('user_id', $request->user_id)->groupBy('type_id')->get();                
                $result;
                $z = 0;                
                foreach($selections as $value){

                    $result[$z]= [
                        'id' => $value->type_id,
                        'name' => $value->type->name,
                        'categories' => $this->getCategoriesBelongingType($value->type_id)
                    ];
                    $z++;
                }                                
                return response()->json(['message'=> "Pilihan dagangan berhasil di simpan", 'data'=> $result], 200);
            }else{
                return response()->json(['message'=> "Gagal simpan pilihan dagangan"], 400);
            }            
        }      
    }

    private function getCategoriesBelongingType($type_id)
    {
        $data = Category::where('type_id', $type_id)->get();
        return $data;
    }

    // get latest category selection by user
    public function last_category_selections(Request $request){
        $data = CategorySelection::where('user_id', $request->user_id)->orderBy('id', 'desc')->first();     
        // return $this->getCategorySelection($data);  
        $jcat =  json_decode($data->categories, true);
        $result = [
            'id' => $data->id,
            'user_id' => $data->user_id,
            'categories' => json_encode($jcat,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'created_at' => Carbon::parse($data->created_at)->format("Y-m-d h:i:s"),
            'updated_at' => Carbon::parse($data->updated_at)->format("Y-m-d h:i:s"),
        ];
        return $data;
    }    

    private function getCategorySelection($data){
        $jcat =  json_decode($data->categories);
        $categories = [];
        $i = 0;
        foreach($jcat as $value){
            $categories[$i] = [
                'id' => $value->id,                
            ];
                $i++;
        }
        
        return response()->json($categories);
    }

    public function products(Request $request){        
        $product = null;
        if(isset($request->search_product)){ //param for search products
            $product = DB::table('products')
            ->join('category_selections', 'products.category_id', '=', 'category_selections.category_id')
            ->where('category_selections.user_id', $request->user_id)
            ->where('products.name', 'like', '%'.$request->search_product.'%')
            ->paginate($request->per_page);    
        }else{
            $product = DB::table('products')
            ->select('products.id', 'products.name', 'products.category_id', 'products.price', 'category_selections.user_id', 'category_selections.type_id')
            ->join('category_selections', 'products.category_id', '=', 'category_selections.category_id')            
            ->where('category_selections.user_id', $request->user_id)            
            ->paginate($request->per_page);
        }   
        $response = []; 
        $i=0;    
        foreach($product->items() as $value){
            $response[$i] = [
                "id" => $value->id,
                "name" => $value->name,
                "category_id" => $value->category_id,
                "price" => $value->price,                
                "user_id" => $value->user_id,
                "type_id" => $value->type_id,
                "quantity" => $this->getQuantityOrder($value, $request->user_id)
            ];
            $i++;
        }
        return $response;        
    }

    private function getQuantityOrder($objProduct, $user_id){
        // cek apa ada order process
        $order = Transaction::where('user_id', $user_id)->where('status', 'process')->first();
        if(count((array) $order) === 0){
            return "0";
        }else{
            $data = TransactionItem::where('product_id', $objProduct->id)->first();
            if($data){
                return $data->quantity;
            }else{
                return "0";
            }
        }        
    }
    
    public function orders_add(Request $request){
                
        $validate = Validator::make($request->all(),[                    
            'product_id' => 'required',
            'user_id' => 'required',
            'quantity' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{            
            $order = Transaction::where('user_id', $request->user_id)->where('status', 'process')->first();
            if(count((array) $order) === 0){
                // insert
                $data = new  Transaction;
                $data->order_id = $this->generateOrderId();
                $data->user_id = $request->user_id;
                $data->status = 'process';
                $saved = $data->save();

                if($saved){
                    // save items
                    $item = new TransactionItem;
                    $item->product_id = $request->product_id;
                    $item->quantity = $request->quantity;
                    $item->transaction_id = $data->id;
                    $save_item = $item->save();
                    if($save_item){
                        return response()->json(['message'=> 'Item ditambah'], 200);
                    }else{
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }else{
                    return response()->json(['message'=> 'Gagal simpan item'], 400);
                }                
            }else{
                // update qty
                $item = TransactionItem::where('product_id', $request->product_id)->where('transaction_id', $order->id)->first();
                if(isset($item)){
                    $item->quantity = ($item->quantity + $request->quantity);
                    $saved = $item->save();

                    if($saved)
                    {
                        return response()->json(['message'=> 'Item ditambah'], 200);
                    }else{                        
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }else{
                    $item = new TransactionItem;
                    $item->product_id = $request->product_id;
                    $item->quantity = $request->quantity;
                    $item->transaction_id = $order->id;
                    $save_item = $item->save();
                    
                    if($save_item)
                    {
                        return response()->json(['message'=> 'Item ditambah'], 200);
                    }else{
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }
            }               
        }
    }


    public function orders_min(Request $request){
                
        $validate = Validator::make($request->all(),[                    
            'product_id' => 'required',
            'user_id' => 'required',
            'quantity' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{            
            $order = Transaction::where('user_id', $request->user_id)->where('status', 'process')->first();
            if(count((array) $order) === 0){
                // insert
                $data = new  Transaction;
                $data->order_id = $this->generateOrderId();
                $data->user_id = $request->user_id;
                $data->status = 'process';
                $saved = $data->save();

                if($saved){
                    // save items
                    $item = new TransactionItem;
                    $item->product_id = $request->product_id;
                    $item->quantity = $request->quantity;
                    $item->transaction_id = $data->id;
                    $save_item = $item->save();
                    if($save_item){
                        return response()->json(['message'=> 'Item dikurangkan'], 200);
                    }else{
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }else{
                    return response()->json(['message'=> 'Gagal simpan item'], 400);
                }                
            }else{
                // update qty
                $item = TransactionItem::where('product_id', $request->product_id)->where('transaction_id', $order->id)->first();
                if(isset($item)){
                    $item->quantity = ($item->quantity == 0) ? 0 : ($item->quantity - $request->quantity);
                    $saved = $item->save();

                    if($saved)
                    {
                        return response()->json(['message'=> 'Item dikurangkan'], 200);
                    }else{                        
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }else{
                    $item = new TransactionItem;
                    $item->product_id = $request->product_id;
                    $item->quantity = $request->quantity;
                    $item->transaction_id = $order->id;
                    $save_item = $item->save();
                    
                    if($save_item)
                    {
                        return response()->json(['message'=> 'Item dikurangkan'], 200);
                    }else{
                        return response()->json(['message'=> 'Gagal simpan item'], 400);
                    }
                }
            }               
        }
    }

    private function generateOrderId()
    {
        $str = "OR";
        $res = $str.mt_rand(0,999999);
        return $res;
    }

    public function save_category(Request $request){
        $validate = Validator::make($request->all(),[                    
            'name' => 'required'        
        ]);

        if ($validate->fails()) {
            
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{
            if(isset($request->category_id)){
                $data = Category::find($request->category_id);
                $data->name = $request->name;
                $saved =$data->save();
                if($saved){
                    return response()->json(['message'=> "Dagangan berhasil di simpan"], 200);
                }else{
                    return response()->json(['message'=> "Gagal simpan dagangan"], 400);
                }
            }else{
                $data = new Category;
                $data->name = $request->name;
                $saved =$data->save();
                if($saved){
                    return response()->json(['message'=> "Dagangan berhasil di simpan"], 200);
                }else{
                    return response()->json(['message'=> "Gagal simpan dagangan"], 400);
                }
            }            
        }
    }

    public function summaryTransaction(Request $request){
        $response = [];

        $response = [
            "total_cart" => $this->getTotalCart($request->user_id),
            "count_cart" => $this->getCountCart($request->user_id),
            "transaction_success" => '0',
            "transaction_cancel" => '0',
            "total_transaction" => '0',
            "total_product" => $this->getTotalProduct($request->user_id)
        ];

        return $response;
    }

    public function getUserproducts($user_id){        
        $products = DB::table('products')
            ->select('products.id', 'products.name', 'products.category_id', 'products.price', 'category_selections.user_id', 'category_selections.type_id')
            ->join('category_selections', 'products.category_id', '=', 'category_selections.category_id')            
            ->where('category_selections.user_id', $user_id)->get();                    
        $response = []; 
        $i=0;    
        foreach($products as $value){
            $response[$i] = [
                "id" => $value->id,
                "name" => $value->name,
                "category_id" => $value->category_id,
                "price" => $value->price,                
                "user_id" => $value->user_id,
                "type_id" => $value->type_id,
                "quantity" => $this->getQuantityOrder($value, $user_id)
            ];
            $i++;
        }
        return $response;        
    }

    private function getTotalCart($user_id){
        $products = $this->getUserproducts($user_id);
        $res = 0;    
        foreach($products as $value){
            $total = ($value['price'] * $value['quantity']);
            $res += $total;
        }
        return $res;
    }

    private function getCountCart($user_id){
        $order = Transaction::where('user_id', $user_id)->where('status', 'process')->first();
        if(count((array) $order) === 0){
            return "0";
        }else{

            $res = DB::table('products')
            ->select('products.id', 'products.name', 'products.category_id', 'products.price', 'category_selections.user_id', 'category_selections.type_id')
            ->join('category_selections', 'products.category_id', '=', 'category_selections.category_id')
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')            
            ->where('transaction_items.quantity', '!=', '0')
            ->where('category_selections.user_id', $user_id)->count();                                                
            
            if($res){
                return $res;
            }else{
                return "0";
            }
        }
    }

    private function getTotalProduct($user_id){
        $products = $this->getUserproducts($user_id);
        $res = count($products) . " " . "item(s)";
        return $res;
    }

    public function orders_edit(Request $request){                
        $validate = Validator::make($request->all(),[                    
            'transaction_id' => 'required' ,
            'product_id' => 'required',
            'user_id' => 'required'         
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{            
            $data = Transaction::find($request->transaction_id);
            $data->product_id = $request->product_id;
            $data->user_id = $request->user_id;
            $saved = $data->save();
            if($saved){
                return response()->json(['message'=> "Transaksi berhasil di simpan"], 200);
            }else{
                return response()->json(['message'=> "Gagal simpan transaksi"], 400);
            }
        }
    }

    public function orders(Request $request){
        $validate = Validator::make($request->all(),[                    
            'user_id' => 'required'            
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{      
            if(isset($request->start_date)){                
                $start = $request->start_date;
                $until = $request->until_date;
                $data = Transaction::where('user_id', $request->user_id)->whereBetween('created_at', [$start, $until])->orderBy('created_at', 'desc')->get(); 
                $res = [];
                foreach($data as $value){
                    //  Carbon::createFromTimeStamp(strtotime($value->created_at))->diffForHumans(),                    
                    // Carbon::parse($value->updated_at)->format('d-m-Y H:i:s')
                    $res[] = [
                        'id' => $value->id,
                        'product' => $value->product,
                        'created_at' => time_elapsed_string($value->created_at),                        
                        'updated_at' => ($value->updated_at !== null) ? time_elapsed_string($value->updated_at) : null
                    ];
                }             
                return $res;                
            }else{
                $data = Transaction::where('user_id', $request->user_id)->orderBy('created_at', 'desc')->get(); 
                $res = [];
                foreach($data as $value){
                    //  Carbon::createFromTimeStamp(strtotime($value->created_at))->diffForHumans(),                    
                    // Carbon::parse($value->updated_at)->format('d-m-Y H:i:s')
                    $res[] = [
                        'id' => $value->id,
                        'product' => $value->product,
                        'created_at' => time_elapsed_string($value->created_at),
                        
                        'updated_at' => ($value->updated_at !== null) ? time_elapsed_string($value->updated_at) : null
                    ];
                }             
                return $res;
            }                  
        }
    }

    public function saldo(Request $request){
        $validate = Validator::make($request->all(),[                    
            'user_id' => 'required'            
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{ 
            $data = Saldo::where('user_id', $request->user_id)->first();
            return $data; 
        }
    }

    public function topup_saldo(Request $request){        
        $validate = Validator::make($request->all(),[                    
            'user_id' => 'required'            
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{             
            $data = Saldo::where('user_id', $request->user_id)->first();
            $data->price = $request->price + $data->price;
            $saved = $data->save();
            if($saved){
                return response()->json(['message'=> "Sldo berhasil ditopup"], 200);
            }else{
                return response()->json(['message'=> "Gagal topup saldo"], 400);
            }
        }
    }

    public function save_product(Request $request){        
        $validate = Validator::make($request->all(),[                    
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required'
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => "Validation failed",'errors' => $validate->errors()], 400);
        }else{             
            if(isset($request->product_id)){
                //update
                $data = Product::find($request->product_id);
                $data->name = $request->name;                
                $data->price = $request->price;        
                $saved = $data->save();
                if($saved){
                    return response()->json(['message'=> "Item berhasil disimpan"], 200);
                }else{
                    return response()->json(['message'=> "Gagal simpan item"], 400);
                }
            }else{
                //create
                $data = new Product;
                $data->name = $request->name;
                $data->category_id = $request->category_id;
                $data->price = $request->price;        
                $saved = $data->save();
                if($saved){
                    return response()->json(['message'=> "Item berhasil disimpan"], 200);
                }else{
                    return response()->json(['message'=> "Gagal simpan item"], 400);
                }
            }            
        }
    }

}