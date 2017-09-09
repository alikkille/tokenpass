<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Rhumsaa\Uuid\Uuid;

class OAuthScopesTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();

        DB::table('oauth_scopes')->delete();

        $datetime = Carbon::now();

        $scopes = [
            [
                'id'          => 'email',
                'description' => 'View Your Email',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
                'uuid'        => Uuid::uuid4()->toString(),
            ],
            [
                'id'          => 'user',
                'description' => 'View Your Username',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
                'uuid'        => Uuid::uuid4()->toString(),
            ],
        ];

        DB::table('oauth_scopes')->insert($scopes);
    }
}
