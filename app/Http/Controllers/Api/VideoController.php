<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{

	private $rules;

	/**
	 * CastMemberController constructor.
	 *
	 */
	public function __construct() {
		$this->rules = [
			'title' => 'required|max:255',
			'description' => 'required',
			'year_launched' => 'required|date_format:Y',
			'opened' => 'boolean',
			'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
			'duration' => 'required|integer',
			'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
			'genres_id' => [
				'required',
			    'array',
				'exists:genres,id,deleted_at,NULL'
				],
			'video_file' => 'mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
			'trailer_file' => 'mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE,
			'thumb_file' => 'image|max:' . Video::THUMB_FILE_MAX_SIZE,
			'banner_file' => 'image|max:' . Video::BANNER_FILE_MAX_SIZE,
		];;
	}

	public function store( Request $request )
	{
		$this->addRuleIfGenreHasCategories($request);
		$validatedData = $this->validate( $request, $this->rulesStore());
		$obj = $this->model()::create($validatedData);

		//$self = $this;
		/**  @var Video $obj */
		/*$obj = \DB::transaction(function () use ($validatedData, $request, $self) {

			$self->handleRelations($obj,  $request);
			//throw new \Exception();
			return $obj;
		});*/

		$obj->refresh();
		return $obj;
	}

	public function update( Request $request, $id )
	{
		$obj = $this->findOrFail($id);
		$this->addRuleIfGenreHasCategories($request);
		$validatedData = $this->validate($request, $this->rulesUpdate());
		$obj->update( $validatedData );

		//$self = $this;
		/**  @var Video $obj */
		/*$obj = \DB::transaction(function () use ($validatedData, $request, $self, $obj) {

			$self->handleRelations($obj,  $request);
			return $obj;
		});*/

		return $obj;
	}

	protected function addRuleIfGenreHasCategories(Request $request)
	{
		$categoriesId = $request->get('categories_id');
		$categoriesId = is_array($categoriesId) ? $categoriesId : [];
		$this->rules['genres_id'][] = new GenresHasCategoriesRule(
			$categoriesId
		);
	}


	protected function model()
	{
		return Video::class;
	}

	protected function rulesStore()
	{
		return $this->rules;
	}

	protected function rulesUpdate()
	{
		return $this->rules;
	}
}
