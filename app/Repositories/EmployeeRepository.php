<?php

namespace App\Repositories;

use Auth;
use Hash;
use Cache;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\EmployeeSession;
use App\Models\Role;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Traits\Authentication;
use App\Interfaces\Constants;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EmployeeRepository implements Constants
{

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
    public function __construct(Employee $user, Merchant $merchant)
    {
        $this->user = $user;
        $this->merchant = $merchant;

        $this->cache = Cache::store(config('global.date.driver'));
    }

    public function fetchUser($id)
    {
        try {
            $user = $this->user->findOrFail($id);
            $transaction_count = Transaction::where("employee_id", $user->id)->whereNotNull("paid_at")->get()->count();

            return [
                'user' => $user,
                'stars' => $transaction_count
            ];
        } catch (ModelNotFoundException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function getUserList($request)
    {
        try {
            if ($request->input('merchant_id')) {
                $users = $this->user->where("merchant_id", $request->input('merchant_id'))->get();
            } else {
                $users = $this->user->get();
            }

            return $users;
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function setFlagUser($user, $request)
    {
        try {
            if ($request->input("flag") == 'yes' && $user->flag == 0) {
                $message = 'Set flag';
                $user->flag = 1;
            } else if ($request->input("flag") == 'no' && $user->flag == 1) {
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
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function createUser(array $data)
    {
        try {
            $res = $this->user->fill($data)->save();
            return [
                'success'   => true,
                'messages' => 'Great! new employee user is successfully created!'
            ];
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function updateUserData($id, array $data)
    {
        try {
            $user = $this->fetchUser($id);

            $now_date = date('Y-m-d');
            $model = EmployeeSession::where("work_date", $now_date)
                ->where("employee_id", $id)
                ->whereNull("exited_at")
                ->first();

            if ($model && isset($data['active_work']) && $data['active_work'] == false) {
                abort(400, 'Tidak bisa update active work karena staff sedang shifted');
            }

            $user->fill($data)->save();

            $res = [
                'success'   => true,
                'messages'  => 'Great! this employee is successfully updated!'
            ];

            if ($id === auth("employee")->user()->id) {
                // logout
                auth("employee")->logout();
                // then login and generate new token
                $newToken = auth("employee")->login($this->fetchUser($id));
                $res['token'] = $newToken;
            }

            return $res;
        } catch (HttpException $e) {
            abort(400, $e->getMessage());
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function changePassword($id, array $data)
    {
        try {
            $user = $this->fetchUser($id);

            if (!auth("employee")->validate([
                'no_hp'     => $user->no_hp,
                'password'  => $data['old_password']
            ])) {
                abort(400, 'Invalid or wrong current password');
            }

            $res = $user->fill($data)->save();

            return [
                'success'   => true,
                'messages'  => 'Great! change password is successfully updated!'
            ];
        } catch (HttpException $e) {
            abort(400, $e->getMessage());
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function getLastSession()
    {
        try {
            $id = auth("employee")->user()->id;
            //dump($id);
            $session = EmployeeSession::where("employee_id", $id)->orderBy("id", "desc")->first();

            return [
                'session'   => $session,
                'work_date' => $this->getWorkDate()
            ];
        } catch (QueryException $th) {
            abort(400, $th->getMessage());
        }
    }

    /**
     * Update session
     *
     * @param string $state
     * @return mixed
     */
    public function updateSession($state)
    {
        try {
            $id = auth("employee")->user()->id;

            $model = $this->fetchUser($id);

            if ($state == 'open') {
                $this->updateSessionOpen($id);
                $model->fill([
                    'active_work' => 1
                ])->save();
            } else if ($state == 'close') {
                $this->updateSessionClose($id);
            }

            $state = ucfirst($state);

            // logout
            auth("employee")->logout(true);

            // then login and generate new token
            $newToken = auth("employee")->login($model);

            // Create response
            return [
                'token'     => $newToken,
                'success'   => true,
                'messages'  => "Great! State has been {$state}",
                'session'   => $this->getLastSession()
            ];
        } catch (QueryException $th) {
            abort(400, $th->getMessage());
        } catch (HttpException $th) {
            abort(400, $th->getMessage());
        }
    }

    /**
     * Update session login for employee
     *
     * @return void
     */
    protected function updateSessionOpen($id)
    {
        if (!$id) {
            return;
        }

        $employee = Employee::find($id);
        $carbonNowTime = Carbon::parse(now("Asia/Jakarta")->toTimeString());
        $closeTime = Carbon::parse($employee->begun_at, "Asia/Jakarta");
        $diff = $closeTime->diffInMinutes($employee->exited_at, false);

        // if pass over Jam Keluar, deny to open
        if (
            $carbonNowTime->diffInMinutes($employee->begun_at, false) <= -10 && ($carbonNowTime->diffInMinutes($employee->exited_at, false) <= 0 ||
                $carbonNowTime->diffInMinutes($employee->exited_at, false) >= $diff) &&
            $employee->active_work == 0
        ) {
            abort(400, 'Sorry! Anda telah tidak bisa buka karena di luar jam kerja Anda, silahkan minta ke Admin untuk mendapat hak akses buka toko');
        }

        $now_date = date('Y-m-d');
        $check_now_date = EmployeeSession::where("work_date", $now_date)->where("employee_id", $id)->first();

        if ($check_now_date && optional($check_now_date)->exited_at == null) {
            abort(400, 'Sorry! Anda punya session masuk kerja sebelumnya!');
        } else {
            EmployeeSession::create([
                'employee_id'   => $id,
                'work_date'     => $now_date,
                'begun_at'      => date('Y-m-d H:i:s'),
            ]);

            if (!$this->getWorkDate()) {
                $this->addWorkDate($this->getCurrentDate());
            }
        }
    }

    /**
     * Update session login for employee
     *
     * @return mixed
     */
    protected function updateSessionClose($id)
    {
        if (!$id) {
            return;
        }

        $employee = Employee::find($id);
        $now_date = date('Y-m-d');
        $model = EmployeeSession::where("work_date", $now_date)
            ->where("employee_id", $id)
            ->whereNull("exited_at")
            ->first();

        if ($model) {

            // Check if any records, no records remove session
            if ($this->cancelOpenIfNoRecords($id, $model->begun_at) === false) {
                $model->delete();
                return true;
            }

            // set active work to 0
            $employee->active_work = 0;
            $employee->save();

            // set close date
            $model->fill([
                'exited_at' => date('Y-m-d H:i:s'),
            ])->save();
        } else {
            $oldModel = EmployeeSession::where("employee_id", $id)
                ->whereNull("exited_at")
                ->first();

            if ($oldModel) {
                // Check if any records, no records remove session
                if ($this->cancelOpenIfNoRecords($id, $oldModel->begun_at) === false) {
                    $oldModel->delete();
                    return true;
                }

                // set active work to 0
                $employee->active_work = 0;
                $employee->save();

                $oldModel->fill([
                    'exited_at' => date('Y-m-d H:i:s'),
                ])->save();
            } else {
                return null;
            }
        }

        return null;
    }

    protected function cancelOpenIfNoRecords($id, $datetime)
    {
        $hasRecords = Transaction::where('employee_id', $id)
            ->whereDate('created_at', '>', $datetime)
            ->exists();

        return $hasRecords;
    }

    public function deleteUser()
    { }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles()
    {
        return config('global.employee.roles');
    }
}
