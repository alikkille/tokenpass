<?php

namespace TKAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisionalWhitelist extends Model
{
    protected static $unguarded = true;

    protected $table = 'provisional_tca_addresses';
}
