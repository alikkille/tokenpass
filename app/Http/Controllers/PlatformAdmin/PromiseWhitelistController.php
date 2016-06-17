    <?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\ProvisionalWhitelistRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;
use TKAccounts\Models\OAuthClient;

class PromiseWhitelistController extends ResourceController
{

    protected $view_prefix      = 'whitelist';
    protected $repository_class = ProvisionalWhitelistRepository::class;

    protected function getValidationRules() {
        return [
            'address' => 'required|max:255',
            'proof' => '',
            'assets' => '',
            'client_id' => 'exists:oauth_clients,id',
        ];
    }    

    protected function modifyViewData_edit($view_data) {
        
        $view_data['clients'] = OAuthClient::all();
        if(!$view_data['clients']){
            $view_data['clients'] = array();
        }
        
        return $view_data;
    }
    
    protected function modifyViewData_create($view_data) {
        
        $view_data['clients'] = OAuthClient::all();
        if(!$view_data['clients']){
            $view_data['clients'] = array();
        }
        
        return $view_data;
    }

}
