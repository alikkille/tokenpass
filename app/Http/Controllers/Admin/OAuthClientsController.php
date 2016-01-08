<?php

namespace TKAccounts\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Http\Requests;
use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\User;
use TKAccounts\Repositories\OAuthClientRepository;
use InvalidArgumentException;

class OAuthClientsController extends Controller
{

    function __construct(OAuthClientRepository $repository) {
        $this->repository = $repository;

        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
		$get = $this->repository->findAll();
		foreach($get as &$row){
			$row->owner = User::find($row->user_id);
			$row->user_count = DB::table('client_connections')->where('client_id', $row->id)->count();
		}
        return view('admin.oauthclients.index', [
            'models' => $get,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.oauthclients.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        // 'id', 'secret'
        $client = $this->repository->create($request->only(['name',]));
        Log::debug("\$client=".json_encode($client, 192));

        $this->updateEndpoints($client, $request->get('endpoints'));

        return view('admin.oauthclients.created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $model = $this->repository->findByID($id);
        $model['endpoints'] = $this->loadEndpoints($model);
        return view('admin.oauthclients.edit', [
            'model' => $model,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $model = $this->repository->findByID($id);
        $this->repository->update($model, $request->only(['name',]));

        $this->updateEndpoints($model, $request->get('endpoints'));

        return view('admin.oauthclients.edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->repository->delete($this->repository->findByID($id));
        return $this->index();
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
