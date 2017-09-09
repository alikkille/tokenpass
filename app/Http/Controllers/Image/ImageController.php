<?php
/**
 * Created by PhpStorm.
 * User: one
 * Date: 04/07/16
 * Time: 12:05.
 */

namespace TKAccounts\Http\Controllers\Image;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery\CountValidator\Exception;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Image;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $type = substr($request->file('file')->getClientMimeType(), 0, 5);

        try {
            if ($type == 'image') {
                $result = Image::store($request);
            } else {
                return response()->json('Only image type files are accepted as an avatar.', 400);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

        if (!$result or !$result['message']) {
            return response()->json('There has been an error', 400);
        } else {
            return response()->json('Avatar defined.');
        }
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
