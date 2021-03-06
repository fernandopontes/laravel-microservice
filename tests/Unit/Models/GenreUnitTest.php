<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenreUnitTest extends TestCase
{
	//use DatabaseMigrations;

	private $genre;

	public static function setUpBeforeClass(
	)/* The :void return type declaration that should be here would cause a BC issue */ {
		parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
	}


	protected function setUp(): void {
		parent::setUp();
		$this->genre = new Genre();
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
    	$fillable = ['name', 'is_active'];
        $this->assertEquals( $fillable, $this->genre->getFillable());
    }

    public function testIfUseTraits()
    {
    	$traits = [
    		SoftDeletes::class, Uuid::class
	    ];
    	$genreTraits = array_keys(class_uses(Genre::class));
    	$this->assertEquals($traits, $genreTraits);
    }

    public function testCatsAttribute()
    {
    	$casts = ['id' => 'string', 'is_active' => 'boolean'];
	    $this->assertEquals($casts, $this->genre->getCasts());
    }

	public function testIncrementingAttribute()
	{
		$genre = new Genre();
		$this->assertFalse($genre->incrementing);
	}

	public function testDatesAttribute()
	{
		$dates = ['deleted_at', 'created_at', 'updated_at'];
		foreach ($dates as $date) {
			$this->assertContains($date, $this->genre->getDates());
		}
		$this->assertCount(count($dates), $this->genre->getDates());
	}
}
