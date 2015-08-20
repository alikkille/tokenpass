<?php

namespace TKAccounts\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Http\Requests;
use TKAccounts\Models\OAuthScope;
use TKAccounts\Repositories\OAuthScopeRepository;
use InvalidArgumentException;

class OAuthScopesController extends Controller
{

    function __construct(OAuthScopeRepository $repository) {
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
        return view('admin.oauthscopes.index', [
            'models' => $this->repository->findAll()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.oauthscopes.create');
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
        $client = $this->repository->create($request->only(['id','description',]));
        return view('admin.oauthscopes.created');
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
        return view('admin.oauthscopes.edit', [
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
        $this->repository->update($model, $request->only(['id','description',]));

        return view('admin.oauthscopes.edited');
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


}
