<?php

namespace TKAccounts\Repositories;

use TKAccounts\Models\User;

/*
* UserRepository
*/
class UserRepository
{
    public function __construct()
    {
    }


    public function findById($id) {
        return User::find($id);
    }
}