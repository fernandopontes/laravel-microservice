<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
	protected abstract function model();

	protected abstract function rulesStore();

	protected abstract function rulesUpdate();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	return $this->model()::all();
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
    	return $obj;
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
        return $obj;
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
	    return $obj;
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
