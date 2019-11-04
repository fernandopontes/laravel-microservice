<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
	use DatabaseMigrations, TestValidations, TestSaves;

	private $genre;

	protected function setUp(): void
	{
		parent::setUp();
		$this->genre = factory(Genre::class)->create();
	}


	public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
                 ->assertJson([$this->genre->toArray()]);
    }

	public function testShow()
	{
		$response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

		$response->assertStatus(200)
		         ->assertJson($this->genre->toArray());
	}

	public function testInvalidationData()
	{
		$data = [
			'name' => '',
			'categories_id' => ''
		];
		$this->assertInvalidationInStoreAction($data, 'required');
		$this->assertInvalidationInUpdateAction($data, 'required');

		$data = [
			'name' => str_repeat('a', 256),
		];
		$this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
		$this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

		$data = [
			'is_active' => 'true'
		];
		$this->assertInvalidationInStoreAction($data, 'boolean');
		$this->assertInvalidationInUpdateAction($data, 'boolean');

		$data = [
			'categories_id' => 'a'
		];
		$this->assertInvalidationInStoreAction($data, 'array');
		$this->assertInvalidationInUpdateAction($data, 'array');

		$data = [
			'categories_id' => [100]
		];
		$this->assertInvalidationInStoreAction($data, 'exists');
		$this->assertInvalidationInUpdateAction($data, 'exists');

		$category = factory(Category::class)->create();
		$category->delete();
		$data = [
			'categories_id' => [$category->id]
		];
		$this->assertInvalidationInStoreAction($data, 'exists');
		$this->assertInvalidationInUpdateAction($data, 'exists');

		/*$response = $this->json('POST', route('genres.store'), []);
		$this->assertInvalidationRequired($response);

		$response = $this->json('POST', route('genres.store'), [
			'name' => str_repeat('a', 256),
			'is_active' => 'true'
		]);
		$this->assertInvalidationMax($response);
		$this->assertInvalidationBoolean($response);

		$genre = factory(Genre::class)->create();
		$response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);
		$this->assertInvalidationRequired($response);

		$response = $this->json(
			'PUT',
			route('genres.update', ['genre' => $genre->id]),
			[
				'name' => str_repeat('a', 256),
				'is_active' => 'true'
			]
		);

		$this->assertInvalidationMax($response);
		$this->assertInvalidationBoolean($response);*/
	}

	protected function assertInvalidationRequired(TestResponse $response)
	{
		$this->assertInvalidationFields($response, ['name'], 'required', []);
		$response->assertJsonMissingValidationErrors(['is_active']);
	}

	protected function assertInvalidationMax(TestResponse $response)
	{
		$this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
	}

	protected function assertInvalidationBoolean(TestResponse $response)
	{
		$this->assertInvalidationFields($response, ['is_active'], 'boolean', []);
	}

	public function testStore()
	{
		$categoryId = factory(Category::class)->create()->id;
		$data = [
			'name' => 'test'
		];
		$response = $this->assertStore(
			$data + ['categories_id' => [$categoryId]],
			$data + ['is_active' => true, 'deleted_at' => null]
		);
		$response->assertJsonStructure([
			'created_at',
			'updated_at'
		]);

		$this->assertHasCategory($response->json('id'), $categoryId);

		$data = [
			'name' => 'test',
			'is_active' => false
		];
		$this->assertStore(
			$data + ['categories_id' => [$categoryId]],
			$data + ['is_active' => false]
		);

		/*$response = $this->json('POST', route('genres.store'), [
			'name' => 'test'
		]);

		$id = $response->json( 'id');
		$genre = Genre::find($id);

		$response
			->assertStatus(201)
			->assertJson($genre->toArray());
		$this->assertTrue($response->json('is_active'));

		$response = $this->json('POST', route('genres.store'), [
			'name' => 'test',
			'is_active' => false
		]);

		$response->assertJsonFragment([
			'is_active' => false
		]);*/
	}

	public function testUpdate()
	{
		$categoryId = factory(Category::class)->create()->id;
		$data = [
			'name' => 'test',
			'is_active' => true
		];

		$response = $this->assertUpdate(
			$data + ['categories_id' => [$categoryId]],
			$data + ['deleted_at' => null]);
		$response->assertJsonStructure([
			'created_at',
			'updated_at'
		]);

		$this->assertHasCategory($response->json('id'), $categoryId);

		$data['name'] = 'test';

		$this->assertUpdate($data, array_merge($data, ['name' => 'test']));

		$data['is_active'] = true;

		$this->assertUpdate($data, array_merge($data, ['is_active' => true]));

		/*$genre = factory(Genre::class)->create([
			'is_active' => false
		]);
		$response = $this->json(
			'PUT',
			route('genres.update', ['genre' => $genre->id]),
			[
				'name' => 'test',
				'is_active' => true
		]);

		$id = $response->json( 'id');
		$genre = Genre::find($id);

		$response
			->assertStatus(200)
			->assertJson($genre->toArray())
			->assertJsonFragment([
				'is_active' => true
			]);

		$response = $this->json(
			'PUT',
			route('genres.update', ['genre' => $genre->id]),
			[
				'name' => 'test',
			]);*/
	}

	protected function assertHasCategory($genreId, $categoryId)
	{
		$this->assertDatabaseHas('category_genre', [
			'genre_id' => $genreId,
			'category_id' => $categoryId
		]);
	}

	public function testDestroy()
	{
		$response = $this->json(
			'DELETE',
			route('genres.destroy', ['genre' => $this->genre->id]));
		$response->assertStatus(204);

		$this->assertNull(Genre::find($this->genre->id));
	}

	public function testRollbackStore()
	{
		$controller = \Mockery::mock(GenreController::class)
			->makePartial()
			->shouldAllowMockingProtectedMethods();

		$controller
			->shouldReceive('validate')
			->withAnyArgs()
			->andReturn([
				'name' => 'test',
			]);

		$controller
			->shouldReceive('rulesStore')
			->withAnyArgs()
			->andReturn([]);

		$controller
			->shouldReceive('handleRelations')
			->once()
			->andThrow(new TestException());

		$request = \Mockery::mock(Request::class);

		$hasError = false;
		try {
			$controller->store($request);
		}
		catch (TestException $exception)
		{
			$this->assertCount(1, Genre::all());
			$hasError = true;
		}

		$this->assertTrue($hasError);
	}

	public function testRollbackUpdate()
	{
		$controller = \Mockery::mock(GenreController::class)
		                      ->makePartial()
		                      ->shouldAllowMockingProtectedMethods();

		$controller
			->shouldReceive('findOrFail')
			->withAnyArgs()
			->andReturn($this->genre);

		$controller
			->shouldReceive('validate')
			->withAnyArgs()
			->andReturn([
				'name' => 'test',
			]);

		$controller
			->shouldReceive('rulesUpdate')
			->withAnyArgs()
			->andReturn([]);

		$controller
			->shouldReceive('handleRelations')
			->once()
			->andThrow(new TestException());

		$request = \Mockery::mock(Request::class);

		$hasError = false;
		try {
			$controller->update($request, 1);
		}
		catch (TestException $exception)
		{
			$this->assertCount(1, Genre::all());
			$hasError = true;
		}

		$this->assertTrue($hasError);
	}

	public function testSyncCategories()
	{
		$categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

		$sendData = [
			'name' => 'test',
			'categories_id' => [$categoriesId[0]]
		];

		$response = $this->json('POST', $this->routeStore(), $sendData);

		$this->assertDatabaseHas('category_genre', [
			'category_id' => $categoriesId[0],
			'genre_id' => $response->json('id')
		]);

		$sendData = [
			'name' => 'test',
			'categories_id' => [$categoriesId[1], $categoriesId[2]]
		];

		$response = $this->json(
			'POST',
			route('genres.update', ['genre' => $response->json('id')]),
			$sendData
		);

		$this->assertDatabaseMissing('category_genre', [
			'category_id' => $categoriesId[0],
			'genre_id' => $response->json('id')
		]);

		$this->assertDatabaseHas('category_genre', [
			'category_id' => $categoriesId[1],
			'genre_id' => $response->json('id')
		]);

		$this->assertDatabaseHas('category_genre', [
			'category_id' => $categoriesId[2],
			'genre_id' => $response->json('id')
		]);
	}

	protected function routeStore()
	{
		return route('genres.store');
	}

	protected function routeUpdate()
	{
		return route('genres.update', ['genre' => $this->genre->id]);
	}

	protected function model()
	{
		return Genre::class;
	}
}
