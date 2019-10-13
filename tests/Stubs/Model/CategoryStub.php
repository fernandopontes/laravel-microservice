<?php

namespace Tests\Stubs\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CategoryStub extends Model
{
	protected $table = 'category_stubs';
    protected $fillable = ['name', 'description', 'is_active', 'deleted_at'];

    public static function createTable()
    {
	    \Schema::create('category_stubs', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('name');
		    $table->text('description')->nullable();
		    $table->boolean('is_active')->default(1);
		    $table->softDeletes();
		    $table->timestamps();
	    });
    }

	public static function dropTable()
	{
		\Schema::dropIfExists('category_stubs');
	}
}
