<?php
namespace App\Repositories;

use Auth;
use Hash;
use Cache;
use App\Models\Super;
use App\Models\Role;
use App\Models\Merchant;
use App\Models\MerchantType;
use App\Models\Referral;
use App\Models\Country;
use App\Models\Province;
use App\Models\Regency;
use App\Interfaces\Constants;
use App\Traits\Authentication;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;

class SuperRepository implements Constants {

  use Authentication;

	/**
	 * User Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $user;

	/**
	 * Merchant Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
  protected $merchant;

  /**
	 * Cache Driver
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	protected $cache;

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct( Super $user, Merchant $merchant )
	{
		$this->user = $user;
        $this->merchant = $merchant;

        $this->cache = Cache::store( config('global.date.driver') );
    }

    public function fetchUser($id)
    {
        try{
            $user = $this->user->find($id);

            return $user;

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    public function getUserList()
    {
        try{
            $users = $this->user->get();

            return $users;

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    public function setFlagUser($user, $request)
    {
        try {
            if( $request->input("flag") == 'yes' && $user->flag == 0 ) {
                $message = 'Set flag';
                $user->flag = 1;
            } else if ( $request->input("flag") == 'no' && $user->flag == 1 ) {
                $message = 'Invoke flag';
                $user->flag = 0;
            } else {
                abort(400, 'No operation exists');
            }

            $user->save();

            $res = [
                'success' => true,
                'messages' => $message . ' is done'
            ];

            return $res;

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    public function createUser($request)
    {
        try {
            $res = $this->user->fill($request->only([
                'name',
                'email',
                'phone',
                'password',
                'role_id'
            ]))->save();

            return [
                'success' => true,
                'messages' => 'Great! new user is successfully created!'
            ];

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    public function updateUser($user, $request)
    {
        try {
            $res = $user->fill($request->only([
                'name',
                'email',
                'phone',
                'role_id'
            ]))->save();

            return [
                'success' => true,
                'messages' => 'Great! the user is successfully updated!'
            ];

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles()
    {
        try {
            $roles = (new Role)->get();

            $data = [];
            if( $roles->count() > 0 ) {
                foreach($roles as $i) {
                    $data[] = ['key' => $i->id, 'label' => $i->title];
                }
            }

            return $data;
        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    public function createRoleCommand($name, $slug)
    {
        if( ! $name && ! $slug ) {
            return '';
        }

        $exist_slug = Role::where('slug', $slug)->first();

        if( $exist_slug ) {
            return "Sorry! " . $slug . ' is exists';
        }

        $id = Role::create([
            'title' => $name,
            'slug' => $slug
        ]);

        return $id ? 'Greate new role' : 'Error create role';
    }
}
