<?php
/**
 * Created by PhpStorm.
 * User: one
 * Date: 04/07/16
 * Time: 12:05
 */

namespace TKAccounts\Http\Controllers\Image;

use Aws\S3\S3Client;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Log\Writer;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use League\Flysystem\AwsS3v2\AwsS3Adapter;
use Mockery\CountValidator\Exception;
use TKAccounts\Repositories\ImageRepository;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Image;
use Tokenly\LaravelEventLog\EventLog;


class ImageController extends Controller {

    public function store(Request $request){

        $result = Image::store($request);

        try {
            $result = Image::store($request);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), route('auth.update'));
        }

        if (!$result OR !$result['message']) {
            return response()->json('Please enter a valid bitcoin address', route('auth.update'));
        }

        return response()->json('Avatar defined.');
    }

    public function show($id, Guard $auth, ImageRepository $repository, APIControllerHelper $api_helper)
    {
        
        $resource = $api_helper->requireResourceOwnedByUser($id, $auth->getUser(), $repository);
        return $api_helper->transformResourceForOutput($resource);
    }


    /**
     * Get the failed message.
     *
     * @return string
     */
    protected function getGenericFailedMessage()
    {
        return Lang::has('auth.generic.fail')
            ? Lang::get('auth.generic.fail')
            : 'There has been an error, please check your input.';
    }

}
