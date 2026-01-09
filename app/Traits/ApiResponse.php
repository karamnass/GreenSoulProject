<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($arg1 = null, $arg2 = null, int $code = 200): JsonResponse
    {
        //success($data, 'message', 200)  الجديد
        //success('message', $data, 200)  القديم
        $message = '';
        $data = null;

        // old success('message', $data, 200)
        if (is_string($arg1) && !is_string($arg2)) {
            $message = $arg1;
            $data = $arg2;
        } else {
            // new success($data, 'message', 200)
            $data = $arg1;
            $message = is_string($arg2) ? $arg2 : '';
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }


    // - error('message', $errors, 422)  القديم
    //- error('message', 422, $errors)   الجديد
    //- error('message', 403)     الجديد المختص

    protected function error(string $message = '', $arg2 = 422, $arg3 = null): JsonResponse
    {
        $code = 422;
        $errors = null;

        if (is_int($arg2)) {
            // new  error('msg', 403, errors?)
            $code = $arg2;
            $errors = $arg3;
        } else {
            // old error('msg', errors, code?)
            $errors = $arg2;
            if (is_int($arg3)) {
                $code = $arg3;
            }
        }
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }
}
