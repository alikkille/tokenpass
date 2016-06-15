<?php
namespace TKAccounts\Http\Controllers\Auth;

use Exception, Input, Session, DB;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\OAuthClient;
use Illuminate\Support\Facades\Auth;
use Rhumsaa\Uuid\Uuid;
use TKAccounts\Repositories\OAuthClientRepository;
use InvalidArgumentException;

class AppsController extends Controller
{

    public function __construct(OAuthClientRepository $repository)
    {
		$this->repository = $repository;
        $this->middleware('auth');
		$this->user = Auth::user();
    }
    
    public function index()
    {
		$clients = OAuthClient::getUserClients($this->user->id);
		if($clients){
			foreach($clients as &$client){
				$client->endpoints = $this->loadEndpoints($client);
				$client->user_count = DB::table('client_connections')->where('client_id', $client->id)->count();
			}
		}

		return view('auth.client-apps', array(
			'client_apps' => $clients,
		));
		
	}
    
    public function registerApp()
    {
		$input = Input::all();
		
		if(!isset($input['name']) OR trim($input['name']) == ''){
			Session::flash('message', 'Client name required');
			Session::flash('message-class', 'alert-danger');
			return redirect('auth/apps');
		}
		
		$name = trim(htmlentities($input['name']));
		$endpoints = '';
		if(isset($input['endpoints'])){
			$endpoints = trim($input['endpoints']);
		}
		
		$token_generator = app('Tokenly\TokenGenerator\TokenGenerator');
		$client = new OAuthClient;
		$client->id = $token_generator->generateToken(32, 'I');
		$client->secret = $token_generator->generateToken(40, 'K');
		$client->name = $name;
		$client->uuid = Uuid::uuid4()->toString();
		$client->user_id = $this->user->id;
		$save = $client->save();
		
		if(!$save){
			Session::flash('message', 'Error saving new application');
			Session::flash('message-class', 'alert-danger');
			return redirect('auth/apps');
		}
		
		try{
			$update_endpoints = $this->updateEndpoints($client, $endpoints);
		}
		catch(InvalidArgumentException $e){
			Session::flash('message', 'Invalid client endpoints');
			Session::flash('message-class', 'alert-danger');
			$client->delete();
			return redirect('auth/apps');
		}
		
		Session::flash('message', 'Client application registered!');
		Session::flash('message-class', 'alert-success');
		
		return redirect('auth/apps');
	}
	
	public function updateApp($app_id)
	{
		$client = OAuthClient::where('id', $app_id)->first();
		if(!$client OR $client->user_id != $this->user->id){
			Session::flash('message', 'Client application not found');
			Session::flash('message-class', 'alert-danger');
		}	
		else{
			$input = Input::all();
			$name = trim(htmlentities($input['name']));
			$endpoints = '';
			if(isset($input['endpoints'])){
				$endpoints = trim($input['endpoints']);
			}			
			
			$client->name = $name;
			$save = $client->save();
			
			if(!$save){
				Session::flash('message', 'Error saving new application');
				Session::flash('message-class', 'alert-danger');
				return redirect('auth/apps');
			}
			else{
				try{
					$update_endpoints = $this->updateEndpoints($client, $endpoints);
				}
				catch(InvalidArgumentException $e){
					Session::flash('message', 'Invalid client endpoints');
					Session::flash('message-class', 'alert-danger');
					return redirect('auth/apps');
				}
			}
		}
		return redirect('auth/apps');
	}
	
	public function deleteApp($app_id)
	{
		$get = OAuthClient::where('id', $app_id)->first();
		if(!$get OR $get->user_id != $this->user->id){
			Session::flash('message', 'Client application not found');
			Session::flash('message-class', 'alert-danger');
		}
		else{
			$delete = $get->delete();
			if(!$delete){
				Session::flash('message', 'Error deleting client application');
				Session::flash('message-class', 'alert-danger');
			}
			else{
				Session::flash('message', 'Client application deleted!');
				Session::flash('message-class', 'alert-success');
			}
		}
		
		return redirect('auth/apps');
	}
	
   protected function updateEndpoints(OAuthClient $client, $endpoints_string) {
        $endpoints = [];
        foreach (explode("\n", $endpoints_string) as $endpoint) {
            $endpoint = trim($endpoint);
            if (!strlen($endpoint)) { continue; }

            $url = parse_url($endpoint);
            $scheme = isset($url['scheme']) ? $url['scheme'].'://' : '';
            $host = isset($url['host']) ? $url['host'] : '';
            $port = isset($url['port']) ? ':'.$url['port'] : '';
            $user = isset($url['user']) ? $url['user'] : '';
            $pass = isset($url['pass']) ? ':'.$url['pass']  : '';
            $pass = ($user || $pass) ? "$pass@" : '';
            $path = isset($url['path']) ? $url['path'] : '';
            $query = isset($url['query']) && $url['query'] ? '?'.$url['query'] : '';
            $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';

            if (!$host OR !$scheme) { throw new InvalidArgumentException("URL was invalid", 1); }

            $endpoint = $scheme.$user.$pass.$host.$port.$path.$query.$fragment;

            if (strlen($endpoint)) { $endpoints[] = $endpoint; }
        }

        DB::transaction(function() use ($client, $endpoints) {
            // delete all
            DB::table('oauth_client_endpoints')
                ->where('client_id', $client['id'])
                ->delete();

            // add new
            foreach($endpoints as $endpoint) {
                DB::table('oauth_client_endpoints')
                    ->insert([
                        'client_id' => $client['id'],
                        'redirect_uri' => $endpoint,
                    ]);
            }
        });
    }

    protected function loadEndpoints(OAuthClient $client) {
        $out = '';
        foreach (DB::table('oauth_client_endpoints')->where('client_id', $client['id'])->get() as $endpoint) {
            // Log::debug("\$endpoint=".json_encode($endpoint, 192));
            $out .= $endpoint->redirect_uri."\n";
        }

        return trim($out);
    }    
    
}
