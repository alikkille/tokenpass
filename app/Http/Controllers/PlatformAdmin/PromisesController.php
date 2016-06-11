<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\ProvisionalRepository;
use Tokenly\CurrencyLib\CurrencyUtil;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class PromisesController extends ResourceController
{

    protected $view_prefix      = 'promise';
    protected $repository_class = ProvisionalRepository::class;



    protected function getValidationRules() {
        return [
            'source'      => 'required|max:255',
            'destination' => 'required|max:255',
            'asset'       => 'required|max:255',
            'quantity'    => 'required|numeric',
        ];
    }    

    protected function modifyVarsBeforeCreate($create_vars) {
        $date = date('Y-m-d H:i:s');

        $create_vars['quantity'] = CurrencyUtil::valueToSatoshis($create_vars['quantity']);
        $create_vars['created_at'] = $date;
        $create_vars['updated_at'] = $date;
        $create_vars['pseudo'] = 0;

        return $create_vars;
    }

    protected function modifyVarsBeforeUpdate($update_vars) {
        $update_vars['quantity'] = CurrencyUtil::valueToSatoshis($update_vars['quantity']);

        return $update_vars;
    }

}
