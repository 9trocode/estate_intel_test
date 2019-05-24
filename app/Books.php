<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    //
    public $timestamps=false;
    protected $table = "books";
    public static function tableName()
    {
        return with(new static)->getTable();
    }
    public static function FindByColumn($columnName)
    {
    }
//    protected $username = "userEmail";
//    protected $password = "userPassword";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id",
        "name",

    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
//        'password', 'token',
    ];


}
