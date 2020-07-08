<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
	use DatabaseMigrations, TestValidations, TestSaves, TestResources;

	private $category;
	private $serializedFields = [
		'id',
		'name',
		'description',
		'is_active',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected function setUp(): void
	{
		parent::setUp();
		$this->category = factory(Category::class)->create();
	}

	public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
	            ->assertJson([
	            	'meta' => ['per_page' =>  15]
	            ])
                ->assertJsonStructure([
                	'data' => [
                		'*' => $this->serializedFields
	                ],
	                'links' => [],
	                'meta' => [],
                ]);

	    $resource = CategoryResource::collection(collect([$this->category]));
	    $this->assertResource($response, $resource);
    }

	public function testShow()
	{
		$response = $this->get(route('categories.show', ['category' => $this->category->id]));

		$response->assertStatus(200)
		         ->assertJsonStructure([
		         	'data' => $this->serializedFields
		         ]);

		$id = $response->json('data.id');
		$resource = new CategoryResource(Category::find($id));
		$this->assertResource($response, $resource);
	}

	public function testInvalidationData()
	{
		$data = [
			'name' => ''
		];
		$this->assertInvalidationInStoreAction($data, 'required');
		$this->assertInvalidationInUpdateAction($data, 'required');

		//$response = $this->json('POST', route('categories.store'), []);
		//$this->assertInvalidationRequired($response);


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

		/*$response = $this->json('POST', route('categories.store'), [
			'name' => str_repeat('a', 256),
			'is_active' => 'true'
		]);
		$this->assertInvalidationMax($response);
		$this->assertInvalidationBoolean($response);*/

		/*
		$response = $this->json('PUT', route('categories.update', ['category' => $this->category->id]), []);
		$this->assertInvalidationRequired($response);

		$response = $this->json(
			'PUT',
			route('categories.update', ['category' => $this->category->id]),
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
		$data = [
			'name' => 'test'
		];
		$response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
		$response->assertJsonStructure([
			'data' => $this->serializedFields
		]);

		$data = [
			'name' => 'test',
			'description' => 'description',
			'is_active' => false
		];
		$this->assertStore($data, $data + ['description' => 'description', 'is_active' => false]);

		$id = $response->json('data.id');
		$resource = new CategoryResource(Category::find($id));
		$this->assertResource($response, $resource);

		/*$response = $this->json('POST', route('categories.store'), [
			'name' => 'test'
		]);

		$id = $response->json( 'id');
		$category = Category::find($id);

		$response
			->assertStatus(201)
			->assertJson($category->toArray());
		$this->assertTrue($response->json('is_active'));
		$this->assertNull($response->json('description'));

		$response = $this->json('POST', route('categories.store'), [
			'name' => 'test',
			'description' => 'description',
			'is_active' => false
		]);

		$response->assertJsonFragment([
			'is_active' => false,
			'description' => 'description'
		]);*/
	}

	public function testUpdate()
	{
		$data = [
			'name' => 'test',
			'description' => 'test',
			'is_active' => true
		];

		$response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
		$response->assertJsonStructure([
			'data' => $this->serializedFields
		]);

		$id = $response->json('data.id');
		$resource = new CategoryResource(Category::find($id));
		$this->assertResource($response, $resource);

		$data = [
			'name' => 'test',
			'description' => ''
		];

		$this->assertUpdate($data, array_merge($data, ['description' => null]));

		$data['description'] = 'test';

		$this->assertUpdate($data, array_merge($data, ['description' => 'test']));

		$data['description'] = null;

		$this->assertUpdate($data, array_merge($data, ['description' => null]));

		/*$response = $this->json(
			'PUT',
			route('categories.update', ['category' => $category->id]),
			[
				'name' => 'test',
				'description' => 'test',
				'is_active' => true
		]);

		$id = $response->json( 'id');
		$category = Category::find($id);

		$response
			->assertStatus(200)
			->assertJson($category->toArray())
			->assertJsonFragment([
				'description' => 'test',
				'is_active' => true
			]);

		$response = $this->json(
			'PUT',
			route('categories.update', ['category' => $category->id]),
			[
				'name' => 'test',
				'description' => '',
			]);

		$response->assertJsonFragment([
				'description' => null,
			]);*/
	}

	public function testDestroy()
	{
		$response = $this->json(
			'DELETE',
			route('categories.destroy', ['category' => $this->category->id]));
		$response->assertStatus(204);

		$this->assertNull(Category::find($this->category->id));
	}

	protected function routeStore()
	{
		return route('categories.store');
	}

	protected function routeUpdate()
	{
		return route('categories.update', ['category' => $this->category->id]);
	}

	protected function model()
	{
		return Category::class;
	}
}
