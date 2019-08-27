<?php
/**
 * Created by PhpStorm.
 * User: fernandodasilvapontes
 * Date: 27/08/19
 * Time: 10:34
 */

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid as uuid4Alias;

trait Uuid {
	public static function boot() {
		parent::boot();
		static::creating(function ($obj) {
			$obj->id = uuid4Alias::uuid4();
		});
	}
}