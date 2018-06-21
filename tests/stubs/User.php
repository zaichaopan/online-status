<?php
use Illuminate\Database\Eloquent\Model;
use Zaichaopan\OnlineStatus\HasOnlineStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class User extends Model implements Authenticatable
{
    use AuthenticableTrait,
        HasOnlineStatus;

    protected $connection = 'testbench';
}
