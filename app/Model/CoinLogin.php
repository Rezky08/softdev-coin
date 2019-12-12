<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinLogin extends Model
{
    use SoftDeletes;
    protected $connection = 'dbmarketcoins';
    protected $table = 'coin_logins';
    protected $softDelete = true;

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    public function coin()
    {
        return $this->belongsTo('App\Model\CoinDetail', 'coin_id');
    }
}
