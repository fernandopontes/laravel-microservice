<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
	use DatabaseMigrations, TestValidations, TestSaves;

	private $cast_member;

	protected function setUp(): void
	{
		parent::setUp();
		$this->cast_member = factory(CastMember::class)->create([
			'type' => CastMember::TYPE_DIRECTOR
		]);
	}

	public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response->assertStatus(200)
                 ->assertJson([$this->cast_member->toArray()]);
    }

	public function testInvalidationData()
	{
		$data = [
			'name' => '',
			'type' => ''
		];
		$this->assertInvalidationInStoreAction($data, 'required');
		$this->assertInvalidationInUpdateAction($data, 'required');

		$data = [
			'type' => 's',
		];
		$this->assertInvalidationInStoreAction($data, 'in');
		$this->assertInvalidationInUpdateAction($data, 'in');
	}

	public function testStore()
	{
		$data = [
			[
				'name' => 'test',
				'type' => CastMember::TYPE_DIRECTOR
			],
			[
				'name' => 'test',
				'type' => CastMember::TYPE_ACTOR
			]
		];

		foreach ($data as $key => $value)
		{
			$response = $this->assertStore($value, $value + ['deleted_at' => null]);
			$response->assertJsonStructure([
				'created_at', 'updated_at'
			]);
		}
	}

	public function testUpdate()
	{
		$data = [
			'name' => 'test',
			'type' => CastMember::TYPE_ACTOR
		];
		$response = $this->assertStore($data, $data + ['deleted_at' => null]);
		$response->assertJsonStructure([
			'created_at', 'updated_at'
		]);
	}

	public function testShow()
	{
		$response = $this->json('GET', route('cast_members.show', ['category' => $this->cast_member->id]));

		$response->assertStatus(200)
		         ->assertJson($this->cast_member->toArray());
	}

	public function testDestroy()
	{
		$response = $this->json(
			'DELETE',
			route('cast_members.destroy', ['category' => $this->cast_member->id]));
		$response->assertStatus(204);

		$this->assertNull(CastMember::find($this->cast_member->id));
	}

	protected function routeStore()
	{
		return route('cast_members.store');
	}

	protected function routeUpdate()
	{
		return route('cast_members.update', ['category' => $this->cast_member->id]);
	}

	protected function model()
	{
		return CastMember::class;
	}
}
