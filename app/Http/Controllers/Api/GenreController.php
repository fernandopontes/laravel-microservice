<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
	private $rules =[
		'name' => 'required|max:255',
		'is_active' => 'boolean',
		'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
	];

	public function store( Request $request )
	{
		$validatedData = $this->validate( $request, $this->rulesStore());
		$self = $this;
		$obj = \DB::transaction(function () use ($validatedData, $request, $self) {
			$obj = $this->model()::create($validatedData);
			$self->handleRelations($obj,  $request);
			//throw new \Exception();
			return $obj;
		});

		$obj->refresh();
		return $obj;
	}

	public function update( Request $request, $id )
	{
		$obj = $this->findOrFail($id);
		$validatedData = $this->validate($request, $this->rulesUpdate());
		$self = $this;
		\DB::transaction(function () use ($self, $request, $obj, $validatedData) {
			$obj->update( $validatedData );
			$self->handleRelations($obj,  $request);
		});

		return $obj;
	}

	protected function handleRelations($genre, Request $request)
	{
		$genre->categories()->sync($request->get('categories_id'));
	}

	protected function model()
	{
		return Genre::class;
	}

	protected function rulesStore()
	{
		return $this->rules;
	}

	protected function rulesUpdate()
	{
		return $this->rules;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function index()
	{
		return Genre::all();
	}*/

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	/*public function store(Request $request)
	{
		$this->validate( $request, $this->rules);
		$genre = Genre::create($request->all());
		$genre->refresh();
		return $genre;
	}*/

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Genre  $genre
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function show(Genre $genre)
	{
		return $genre;
	}*/

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Genre  $genre
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function update(Request $request, Genre $genre)
	{
		$this->validate($request, $this->rules);
		$genre->update($request->all());
		return $genre;
	}*/

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Genre  $genre
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function destroy(Genre $genre)
	{
		$genre->delete();
		return response()->noContent(); // 204
	}*/
}
