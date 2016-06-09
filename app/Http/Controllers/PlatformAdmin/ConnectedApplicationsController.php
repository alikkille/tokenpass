<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\ClientConnectionRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;
use Input;

class ConnectedApplicationsController extends ResourceController
{

    protected $view_prefix      = 'connectedapps';
    protected $repository_class = ClientConnectionRepository::class;

    // ------------------------------------------------------------------------

    protected function getValidationRules() {
        return [
            'client_id' => 'required|max:255',
            'user_id'   => 'required|numeric:255',
        ];
    }


    protected function modifyViewData_edit($view_data) {
        return $this->addClientAndUserOptions($view_data);
    }

    protected function modifyViewData_create($view_data) {
        return $this->addClientAndUserOptions($view_data);
    }

    protected function addClientAndUserOptions($view_data) {
        $clients = app('TKAccounts\Repositories\OAuthClientRepository')->findAll();
        $view_data['client_options'] = $clients->pluck('id')->combine($clients->pluck('name'));

        $users = app('TKAccounts\Repositories\UserRepository')->findAll();
        $view_data['user_options'] = $users->pluck('id')->combine($users->pluck('username'));

        return $view_data;
    }
    
    public function index()
    {
        
        $client_id = Input::get('client_id');
        if($client_id){
            $models = $models = $this->resourceRepository()->findByClientId($client_id);
        }
        else{
            $models = $this->resourceRepository()->findAll();
        }
        
        return view('platformadmin.'.$this->view_prefix.'.index', $this->modifyViewData([
            'models' => $models,
        ], __FUNCTION__));
    }

}
