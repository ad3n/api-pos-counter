<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Category;
use App\Models\CategorySelection;
use App\Traits\Authentication;
use App\Interfaces\Constants;
use App\Jobs\Events\MerchantProductAdded;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Log;
use DB;
use Auth;
use Cache;


class ProductRepository implements Constants
{

	use Authentication;

	/**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected $product;

    /**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return int
	 */
	protected $offset;

    /**
	 * Per page instance
	 *
	 * @author Dian Afrial
	 * @return int
	 */
	protected $perPage;

     /**
	 * Per page instance
	 *
	 * @author Dian Afrial
	 * @return array
	 */
	protected $orderBy;

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct(Product $product)
	{
		$this->product = $product;

		$this->offset = 0;
		$this->perPage = 20;
		$this->orderBy = ['id', 'desc'];
	}

	/**
	 * Get products by arguments
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getProducts($args = [])
	{
		$this->explodeList($args);

		$model = $this->searchCriteria($args);

		$model = $model->orderBy("products." . $this->orderBy[0], $this->orderBy[1]);

		return $model->orderBy("products." . $this->orderBy[0], $this->orderBy[1])
			->offset($this->offset)
			->take($this->perPage)
			->get();
	}

	public function getProductsWithPagination($args)
	{
		$this->explodeList($args);

		$model = $this->searchCriteria($args);

		$model = $model->orderBy("products." . $this->orderBy[0], $this->orderBy[1]);

		return [
			'count' => $model->count(),
			'items' => $model->offset($this->offset)->take($this->perPage)->get()
		];
	}

	/**
	 * Get search product barcode
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function getSearchBarcode($request)
	{
		try {

			$model = $this->searchCriteria($request->only('code'));
			$product2 = $model->exists() ? $model->first() : null;

			return $product2 ?
				['success' => true, 'product' => $product2] : ['error' => true, 'product' => null];

		} catch (ModelNotFoundException $e) {

			Log::error("ModelNotFoundException : " . $e->getMessage());

			abort(400, $e->getMessage());
		}
	}

	/**
	 * Get master categories
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getCategories($args = [])
	{
		$this->explodeList($args);

		$model = new Category;

		if (isset($args['search']) && !empty($args['search'])) {
			$model = $model->where("name", "LIKE", "%{$args['search']}%")
				->orderBy('name', 'asc');
		}

		return $model->orderBy($this->orderBy[0], $this->orderBy[1])
			->offset($this->offset)
			->take($this->perPage)
			->get();
	}

	/**
	 * Get master categories
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getCategoriesByCategories($args = [])
	{
		$this->explodeList($args);

		$model = new Category;

		if (isset($args['search']) && !empty($args['search'])) {
			$model = $model->where("name", "LIKE", "%{$args['search']}%")
				->orderBy('name', 'asc');
		}

		return $model->orderBy($this->orderBy[0], $this->orderBy[1])
			->offset($this->offset)
			->take($this->perPage)
			->get();
	}

	/**
	 * Search criteria
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public function searchCriteria($args)
	{
		$model = DB::table("products");
		$model = $model->whereNull("deleted_at")
			->where("products.merchant_id", $this->getUserMerchant()->id);

		if (isset($args['category_id']) && !empty($args['category_id'])) {
			$id = $args['category_id'];
			$model = $model
				->join('category_selections', function ($join) use ($id) {
					$join->on('products.id', '=', 'category_selections.product_id')
						->where('category_selections.category_id', '=', $id);
				});
		}

		if (isset($args['search']) && !empty($args['search'])) {
			$model = $model->where("name", "LIKE", "%{$args['search']}%")
				->orderBy('name', 'asc');
		}

		if (isset($args['code']) && !empty($args['code'])) {
			$model = $model->where("code", $args['code']);
		}

		$model = $model->join('category_selections', function ($join) {
			$join->on('products.id', '=', 'category_selections.product_id');
		});

		return $model;
	}

	/**
	 * title_here
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function getCategorized($request)
	{
		// Get cache if has
		if ($request->input('keyword') == '') {
			if (Cache::has($this->getKeyMerchantProducts())) {
				$json = Cache::get($this->getKeyMerchantProducts());
				return json_decode($json, true);
			}
		}

		$categories = (new Category)->getProducts($this->getUserMerchant()->id)->get();
		$data = [];
		if (!empty($categories)) {
			foreach ($categories as $item) {
				$id = $item->id;
				$data[$id]['name'] = $item->name;
				$models = DB::table('products')
					->join('category_selections', function ($join) use ($id) {
						$join->on('products.id', '=', 'category_selections.product_id')
							->where('category_selections.category_id', '=', $id);
					})
					->where("products.merchant_id", $this->getUserMerchant()->id)
					->whereNull("deleted_at");

				if ($request->input('keyword')) {
					$keyword = $request->input('keyword');
					$models = $models->where("products.name", "like", "%{$keyword}%")
						->orWhere("products.code", "like", "%{$keyword}%");
				}

				$models = $models->orderBy("name", "asc")
					->get()
					->toArray();


				$data[$id]['products'] = $models;
			}
		}

		// save new to cache
		if (!Cache::has($this->getKeyMerchantProducts())) {
			Cache::forever($this->getKeyMerchantProducts(), collect($data)->values()->toJson());
		}

		return collect($data)->values();
	}

	public function getCategoriesByMerchant($data)
	{
		$selections = (new CategorySelection)->where("merchant_id", $data['merchant_id'])->get();
		$grouped = $selections->groupBy("category_id")->keys();

		$data = collect([]);
		if (!empty($grouped)) {
			foreach ($grouped as $key) {
				$data->push(Category::find($key));
			}
		}
		//dd($categories);
		return $data;
	}

	/**
	 * Add new product
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function addUserProduct($request)
	{
		try {
			$res_id = $this->createProduct($request);
		} catch (QueryException $e) {

			Log::error("Add User Product SQL Query : " . $e->getMessage());

			abort($e->getCode(), $e->getMessage());
		}

		$res = [
			'success' 	=> true,
			'product' 	=> $this->product->find($res_id)->toArray(),
			'messages' 	=> __('user.success_add_product')
		];

		return $res;
	}

	/**
	 * Add new product
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function updateUserProduct($id, $request)
	{
		try {
			// get model product
			$product = $this->product->findOrFail($id);

			$prepare = [
				'name' 			=> $request->input("name"),
				'regular_price' => floatval($request->input("price")),
			];

			if ($request->input('code')) {
				$prepare['code'] = $request->input('code');
			}

			if ($request->input("on_sale")) {
				$prepare['on_sale'] = $request->input("on_sale");
			}

			if ($request->input("sale_price")) {
				$prepare['sale_price'] = $request->input("sale_price");
			}

			if ($request->input("capital_cost")) {
				$prepare['capital_cost'] = floatval($request->input("capital_cost"));
			}

			if ($request->input("qty")) {
				$prepare['qty'] = $request->input("qty");
			}

			if ($request->input("type")) {
				if (!in_array($request->input("type"), [
					static::PRODUCT_TYPE_PC,
					static::PRODUCT_TYPE_VOLUME,
					static::PRODUCT_TYPE_SALDO
				])) {
					abort(400, 'product type is not supported');
				}
				$prepare['type'] = $request->input("type");
			}


			$product->fill($prepare)->save();

			if ($request->input("category_id") != $product->categorySelection()->first()->category_id) {
				$this->syncCategorySelection(
					$product->categorySelection()->first(),
					$request->input("category_id")
				);
			}
		} catch (ModelNotFoundException $e) {

			Log::error("Model Not Found : " . $e->getMessage());

			abort(400, __("user.model_not_found"));
		} catch (QueryException $e) {

			Log::error("Edit User Product SQL Query : " . $e->getMessage());

			abort(500, $e->getMessage());
		}

		// forget product
		Cache::forget($this->getKeyMerchantProducts());

		$res = [
			'success' 	=> true,
			'product' 	=> $this->product->find($id)->toArray(),
			'messages' 	=> __('user.success_edit_product')
		];

		return $res;
	}

	/**
	 * Trash the product ID
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function deleteUserProduct($id, $request)
	{
		try {

			$model = $this->product->findOrFail($id);

			if (!$model->transactionItems()->exists()) {
				$model->forceDelete();
			} else {
				$model->delete();
			}
		} catch (ModelNotFoundException $e) {

			Log::error("Model Not Found : " . $e->getMessage());

			abort(400, __("user.model_not_found"));
		} catch (QueryException $e) {

			Log::error("Delete User Product SQL Query : " . $e->getMessage());

			abort($e->getCode(), $e->getMessage());
		}

		// forget product
		Cache::forget($this->getKeyMerchantProducts());

		$res = [
			'success' 	=> true,
			'messages' 	=> __('user.success_delete_product')
		];

		return $res;
	}

	/**
	 * Create new product
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected function createProduct($request)
	{
		try {
			// create product
			$newProduct = new $this->product;

			$newProduct->merchant_id = $this->getUserMerchant()->id;
			$newProduct->name = $request->input('name');
			$newProduct->regular_price = floatval($request->input('price'));

			if ($request->input("code")) {
				$newProduct->code = $request->input("code");
			}

			if ($request->input("on_sale")) {
				$newProduct->on_sale = $request->input("on_sale");
			}

			if ($request->input("sale_price")) {
				$newProduct->sale_price = $request->input("sale_price");
			}

			if ($request->input("capital_cost")) {
				$newProduct->capital_cost = $request->input("capital_cost");
			}

			if ($request->input("qty")) {
				$newProduct->qty = $request->input("qty");
			}

			if ($request->input("type")) {
				if (!in_array($request->input("type"), [
					static::PRODUCT_TYPE_PC,
					static::PRODUCT_TYPE_VOLUME,
					static::PRODUCT_TYPE_SALDO
				])) {
					abort(400, 'product type is not supported');
				}
				$newProduct->type = $request->input("type");
			}

			$newProduct->save();

			// Append product id to category selections
			$category = new CategorySelection;

			$category->merchant_id = $this->getUserMerchant()->id;
			$category->product_id = $newProduct->id;
			$category->category_id = $request->input('category_id');

			$category->save();

			// forget and save new items to cache
			Cache::forget($this->getKeyMerchantProducts());

			event(new MerchantProductAdded($this));

			return $newProduct->id;
		} catch (HttpException $e) {
			abort(400, $e->getMessage());
		} catch (QueryException $e) {
			abort(400, $e->getMessage());
		}
	}

	protected function syncCategorySelection($model, $new_id)
	{
		// append product id to category selections
		$category = new CategorySelection;

		$category->merchant_id = $this->getUserMerchant()->id;
		$category->product_id = $model->product_id;
		$category->category_id = $new_id;

		$category->save();

		// delete old category_id
		$model->where([
			"merchant_id" => $this->getUserMerchant()->id,
			"product_id" => $model->product_id,
			"category_id" => $model->category_id
		])->forceDelete();
	}

	protected function explodeList($args)
	{
		if (empty($args))
			return;

		if (isset($args['offset']) || !empty($args['offset']))
			$this->offset = $args['offset'];

		if (isset($args['per_page']) || !empty($args['per_page']))
			$this->perPage = $args['per_page'];

		if (isset($args['order_by']) || !empty($args['order_by']))
			$this->orderBy = $args['order_by'];
	}

	public function fetchCategory($id)
	{
		try {
			$model = Category::find($id);
			return $model;
		} catch (QueryException $e) {
			abort(400, $e->getMessage());
		}
	}

	public function getCategoryList()
	{
		try {
			$models = Category::all()->toArray();
			return $models;
		} catch (QueryException $e) {
			abort(400, $e->getMessage());
		}
	}

	public function createCategory($request)
	{
		try {
			$res = Category::create($request->only([
				'name'
			]));

			return [
				'success' => true,
				'messages' => 'Great! new category is successfully created!'
			];
		} catch (QueryException $e) {
			abort(400, $e->getMessage());
		}
	}

	public function updateCategory($model, $request)
	{
		try {
			$res = $model->fill($request->only([
				'name'
			]))->save();

			return [
				'success' => true,
				'messages' => 'Great! the category is successfully updated!'
			];
		} catch (QueryException $e) {
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Delete category
	 *
	 * @param int $id
	 * @return void
	 */
	public function deleteCategory($id)
	{
		$category = Category::find($id);

		if (!$category) {
			abort(400, 'Sorry! no category found');
		}

		if ($category->categorySelections()->count() > 0) {
			abort(400, 'Sorry! the category is unable to permanently deleted due to has products');
		}

		$category->forceDelete();

		$res = [
			'success' => true,
			'messages' => 'Success Deleted!',
		];

		return $res;
	}

	/**
	 * Backwards compability
	 */
	protected function getKeyMerchantProducts() : String
	{
		return "mrc_" . $this->getUserMerchant()->id;
	}
}
