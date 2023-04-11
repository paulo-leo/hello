<?php

namespace App\Migrations;

use Kernel\Schema\Migration;

class c2023_04_08_181038_Users extends Migration
{
    protected $table = 'np_users'; 

    /************************
     **  Cria a migração   **
     **                    **
     **  @return void      **
     ************************/
    public function up()
    {
        $this->column->increments();
        $this->column->string('name',150);
        $this->column->string('email');
        $this->column->string('password',100);
        $this->column->string('status',30)->default('awaiting_admin_approval');
        $this->column->string('role',30)->default('subscriber');
        $this->column->string('avatar')->null(); 
        $this->column->date('birth_date')->null();
        $this->column->datetime('updated_at')->null();
        $this->column->datetime('deleted_at')->null();
        $this->column->datetime('email_verified_at')->null();
        $this->column->createdAt();
    }

    /************************
     ** Reverte a migração **
     **                    **
     ** @return void       **
     ************************/
    public function down()
    {
        $this->dropIfExists($this->table);
    }
}
