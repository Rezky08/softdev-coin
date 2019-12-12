<?php
namespace App\Helpers;


class Currency
{
    public function intToIdr($int = 0)
    {
        return number_format($int,0,',','.');
    }
}

?>
