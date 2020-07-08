<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
	protected $paginationSize = 15;

	protected abstract function model();

	protected abstract function rulesStore();

	protected abstract function rulesUpdate();

	protected abstract function resource();

	protected abstract function resourceCollection();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    $data = ( ! $this->paginationSize) ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
    	$resourceCollectionClass = $this->resourceCollection();
    	$refClass = new \ReflectionClass($this->resourceCollection());
    	return $refClass->isSubclassOf(ResourceCollection::class)
		    ? new $resourceCollectionClass($data)
		    : $resourceCollectionClass::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$validatedData = $this->validate( $request, $this->rulesStore());
    	$obj = $this->model()::create($validatedData);
    	$obj->refresh();
    	$resource = $this->resource();
    	return new $resource($obj);
    }

    protected function findOrFail($id)
    {
    	$model = $this->model();
    	$keyName = (new $model)->getRouteKeyName();
    	return $this->model()::where($keyName, $id)->firstOrFail();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    	$obj = $this->findOrFail($id);
	    $resource = $this->resource();
	    return new $resource($obj);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
	    $obj = $this->findOrFail($id);
	    $validateData = $this->validate($request, $this->rulesUpdate());
	    $obj->update($validateData);
	    $resource = $this->resource();
	    return new $resource($obj);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $obj = $this->findOrFail($id);
       $obj->delete();
       return response()->noContent(); // 204
    }
}
