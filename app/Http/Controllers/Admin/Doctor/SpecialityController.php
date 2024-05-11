<?php

namespace App\Http\Controllers\Admin\Doctor;

use Illuminate\Http\Request;
use App\Models\Doctor\Specialitie;
use App\Http\Controllers\Controller;

class SpecialityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // QUE EL FILTRO POR NOMBRE DE ROL
        $name = $request->search;

        $specialities = Specialitie::where("name","like","%".$name."%")->orderBy("id","desc")->get();

        return response()->json([
            "specialities" => $specialities->map(function($rol) {
                return [
                    "id" => $rol->id,
                    "name" => $rol->name,
                    "state" => $rol->state,
                    "created_at" => $rol->created_at->format("Y-m-d h:i:s")
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_specialitie = Specialitie::where("name",$request->name)->first();

        if($is_specialitie){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DE LA ESPECIALIDAD YA EXISTE"
            ]);
        }

        $specialitie = Specialitie::create($request->all());

        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $specialitie = Specialitie::findOrFail($id);
        return response()->json([
            "id" => $specialitie->id,
            "name" => $specialitie->name,
            "state" => $specialitie->state
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $is_specialitie = Specialitie::where("id","<>",$id)->where("name",$request->name)->first();

        if($is_specialitie){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DE LA ESPECIALIDAD YA EXISTE"
            ]);
        }

        $specialitie = Specialitie::findOrFail($id);
        $specialitie->update($request->all());
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $specialitie = Specialitie::findOrFail($id);
        $specialitie->delete();
        return response()->json([
            "message" => 200,
        ]);
    }
}
