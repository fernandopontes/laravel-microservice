<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenreTest extends TestCase
{
	use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
    	$genre = Genre::create([
    		'name' => 'test1'
	    ]);

    	$genres = Genre::all();
    	$this->assertCount(1, $genres);

    	$genreKey = array_keys($genres->first()->getAttributes());

    	$this->assertEqualsCanonicalizing([
    		'id',
		    'name',
		    'is_active',
		    'created_at',
		    'updated_at',
		    'deleted_at',
	    ], $genreKey);
    }

    public function testCreate()
    {
    	$genre = Genre::create([
    		'name' => 'test1'
	    ]);
    	$genre->refresh();

    	$this->assertEquals(36, strlen($genre->id));
    	$this->assertEquals('test1', $genre->name);
    	$this->assertTrue((bool)$genre->is_active);
    }

    public function testUpdate()
    {
	    /** @var Genre $genre */
	    $genre = factory(Genre::class)->create([
		    'is_active' => false
        ]);

	    $date = [
		    'name' => 'test_name_updated',
		    'is_active' => true,
	    ];

	    $genre->update($date);

	    foreach ($date as $key => $value) {
	    	$this->assertEquals($value, $genre->{$key});
	    }
    }

    public function testDelete()
    {
    	/** @var Genre $genre */
    	$genre = factory(Genre::class)->create();
    	$genre->delete();
    	$this->assertNull(Genre::find($genre->id));

    	$genre->restore();
    	$this->assertNotNull(Genre::find($genre->id));
    }
}
