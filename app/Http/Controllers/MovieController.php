<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MovieController extends Controller
{
    public function index() 
    {
        $data = Movie::all();
        return response()->json([
            'message'   => 'success',
            'data'      => $data
        ]);
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'    => 'required|max:150|unique:movies,title',
            'rating'    => 'required|numeric',
            'description' => 'required',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $mv = new Movie();
            $mv->title = $request->title;
            $mv->description = $request->description;
            $mv->rating = $request->rating;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(app()->basePath('public/uploads'), $imageName);
                $mv->image = $imageName;
            }
    
            $mv->save();

            DB::commit();
            return response()->json([
                'message'   => 'success',
                'data'      => $mv
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            abort('402', $th->getMessage());
        }
    }

    public function show($id) 
    {
        $data = Movie::find($id);
        if (empty($data)) {
            abort('404', 'Data not found!');
        }

        return response()->json([
            'message'   => 'success',
            'data'      => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title'    => 'required|max:150|unique:movies,title,' . $id,
            'rating'    => 'required|numeric',
            'description' => 'required',
            'image' => 'nullable'
        ]);

        DB::beginTransaction();
        try {
            $mv = Movie::find($id);
            if (empty($mv)) {
                abort('404', 'Data not found!');
            }

            $mv->title = $request->title;
            $mv->description = $request->description;
            $mv->rating = $request->rating;
            $mv->image = $request->image;
            $mv->update();

            DB::commit();
            return response()->json([
                'message'   => 'success',
                'data'      => $mv
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            abort('402', $th->getMessage());
        }
    }

    public function destroy($id) 
    {
        $data = Movie::find($id);
        if (empty($data)) {
            abort('404', 'Data not found!');
        }
        $data->delete();
        return response()->json([
            'message'   => 'success',
        ]);
    }
}
