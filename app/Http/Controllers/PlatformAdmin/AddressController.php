<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Input;
use TKAccounts\Models\User;
use TKAccounts\Repositories\AddressRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class AddressController extends ResourceController
{
    public function __construct()
    {
        $this->middleware('sign');
    }

    protected $view_prefix = 'address';
    protected $repository_class = AddressRepository::class;

    protected function getValidationRules()
    {
        return [
            'user_id'              => 'exists:users,id',
            'label'                => 'max:255',
            'verified'             => 'numeric',
            'active_toggle'        => 'numeric',
            'second_factor_toggle' => 'numeric',
            'public'               => 'numeric',
            'login_toggle'         => 'numeric',
            'from_api'             => 'numeric',
            'address'              => 'max:255',
            'xchain_address_id'    => 'max:255',
            'send_monitor_id'      => 'max:255',
            'receive_monitor_id'   => 'max:255',
        ];
    }

    public function index()
    {
        $username = trim(Input::get('username'));
        if ($username and $username != '') {
            $models = [];
            $getUser = User::where('username', $username)->first();
            if ($getUser) {
                $models = $models = $this->resourceRepository()->findAllByUserID($getUser->id);
            }
        } else {
            $models = $this->resourceRepository()->findAll();
        }

        return view('platformadmin.'.$this->view_prefix.'.index', $this->modifyViewData([
            'models' => $models,
        ], __FUNCTION__));
    }
}
