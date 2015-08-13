<?php

namespace TKAccounts\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Http\Requests;
use TKAccounts\Repositories\OAuthClientRepository;

class OAuthClientsController extends Controller
{

    function __construct(OAuthClientRepository $repository) {
        $this->repository = $repository;

        Log::debug("admin middleware disabled");
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.oauthclients.index', [
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
        $this->repository->create($request->only(['name',]));
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
        return view('admin.oauthclients.edit', [
            'model' => $this->repository->findByID($id)
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
        $this->repository->update($this->repository->findByID($id), $request->only(['name',]));
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
}
