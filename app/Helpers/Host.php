<?php

namespace App\Helpers;


class Host
{
    public function requestHost($host = '')
    {
        $hostProvide = [
            'customer' => 'http://127.0.0.1:8001/',
            'seller' => 'http://127.0.0.1:8002/',
            'supplier' => 'http://127.0.0.1:8003/',
            'coin' => 'http://127.0.0.1:8004/',
            'integrated' => 'http://127.0.0.1:8005/'
        ];
        try {
            return $hostProvide[$host];
        } catch (\Throwable $th) { }
    }
}
