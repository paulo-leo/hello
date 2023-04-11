<?php
namespace App\Models;

use Kernel\MVC\Model;

class UserModel extends Model
{
    //protected $connection = 'wordpress';
    protected $prefix = 'np';
    protected $timestamps = true;
    protected $trash = false; 
    protected $fillable = [
       'role','name','email',
       'email_verified_at',
       'password','birth_date'
    ];

    protected $attributes = [
       'role'=>'reader',  
       'status'=>'active',
       'email_verified_at'=>null
    ];


    public function carros()
    {
      return $this->hasMany(CarroModel::class,'user_id');
    }

    public function moto()
    {
      return $this->hasOne(CarroModel::class,'user_id');
    }
}