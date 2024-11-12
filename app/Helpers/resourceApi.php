<?php

namespace App\Helpers;

class resourceApi
{
    public static function sendResponse($code = 200, $msg = null, $data = [])
    {
        $response = [
            'status' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        return response()->json($response, $code);
    }

    public static function pagination($itme, $Resource)
    {
        if (count($itme) > 0) {

            $data = [
                'rows' => $Resource,
                'paginationLinks' => [
                    'currentPages' => $itme->currentPage(),
                    'perPage' => $itme->lastpage(),
                    'total' => $itme->total(),
                    'links' => [
                        'first' => $itme->url(1),
                        'last' => $itme->url($itme->lastpage()),
                    ],
                ],
            ];

            return resourceApi::sendResponse(200, 'تم بنجاح', $data);
        }
        return resourceApi::sendResponse(200, 'لايوجد بيانات ', []);
    }
}
