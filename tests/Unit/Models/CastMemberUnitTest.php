<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CastMemberUnitTest extends TestCase
{
	//use DatabaseMigrations;

	private $cast_member;

	public static function setUpBeforeClass(
	)/* The :void return type declaration that should be here would cause a BC issue */ {
		parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
	}


	protected function setUp(): void {
		parent::setUp();
		$this->cast_member = new CastMember();
	}

	protected function tearDown(): void {
		parent::tearDown(); // TODO: Change the autogenerated stub
	}

	public static function tearDownAfterClass(
	)/* The :void return type declaration that should be here would cause a BC issue */ {
		parent::tearDownAfterClass(); // TODO: Change the autogenerated stub
	}


	/**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFillableAttribute()
    {
    	$fillable = ['name', 'type'];
        $this->assertEquals( $fillable, $this->cast_member->getFillable());
    }

    public function testIfUseTraits()
    {
    	$traits = [
    		SoftDeletes::class, Uuid::class
	    ];
    	$castMemberTraits = array_keys(class_uses(CastMember::class));
    	$this->assertEquals($traits, $castMemberTraits);
    }

    public function testCatsAttribute()
    {
    	$casts = ['id' => 'string', 'type' => 'integer'];
	    $this->assertEquals($casts, $this->cast_member->getCasts());
    }

	public function testIncrementingAttribute()
	{
		$cast_member = new CastMember();
		$this->assertFalse($cast_member->incrementing);
	}

	public function testDatesAttribute()
	{
		$dates = ['deleted_at', 'created_at', 'updated_at'];
		foreach ($dates as $date) {
			$this->assertContains($date, $this->cast_member->getDates());
		}
		$this->assertCount(count($dates), $this->cast_member->getDates());
	}
}
