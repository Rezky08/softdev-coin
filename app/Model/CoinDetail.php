<?php

namespace App\Model;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticable;

class CoinDetail extends Authenticable
{
    use Notifiable, HasApiTokens, SoftDeletes;
    protected $connection = 'dbmarketcoins';
    protected $table = 'coin_details';
    protected $softDelete = true;
    protected $hidden = ['updated_at', 'deleted_at'];
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    public function login()
    {
        return $this->hasOne('App\Model\CoinLogin', 'coin_id');
    }
    public function loginLog()
    {
        return $this->hasOne('App\Model\CoinLoginLog', 'coin_id');
    }
    public function balance()
    {
        return $this->hasOne('App\Model\CoinBalance', 'coin_id');
    }
    public function security()
    {
        return $this->hasOne('App\Model\CoinSecurity', 'coin_id');
    }
    public function transaction()
    {
        return $this->belongsTo('App\Model\CoinTransaction', 'coin_id');
    }
}
