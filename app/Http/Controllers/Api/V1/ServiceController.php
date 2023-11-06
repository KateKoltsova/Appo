<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = explode('?',$request->getRequestUri());
        if (isset($query[1])){
            $query = explode('&',$query[1]);
            foreach ($query as $value){
                $value = explode('category=', $value);
                $filters[] = $value[1];
            }
            $services = Service::whereIN('category',$filters)->get();
        } else {
            $services = Service::all();
        }
        $services = $services->toArray();
        foreach ($services as $service) {
            $response[$service['category']][] = $service['title'];
        }
        return response()->json(['data' => $response]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
