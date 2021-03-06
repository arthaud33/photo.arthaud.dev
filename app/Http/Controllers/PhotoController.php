<?php

namespace App\Http\Controllers;

use App\Gallery;
use App\Photo;
use JWTAuth;
use Validator;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


class PhotoController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function index(Request $request) {
        $photos = photo::all();
        return response()->json(compact('photos'), 200);
    }





    public function show($slug) {

        if (! $photo = photo::firstWhere('slug', $slug))
        {
            abort(404, 'photo');
        }

        return view('photo.show', compact('photo'));
    }




    public function create(Request $request) {
        $galleries = gallery::select('name', 'slug')->get();
        return view('photo.create', compact('galleries', 'request'));
    }




    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'gallery' => 'required|string|max:255',
            'background' => 'required|string|max:255',
            'name' => 'nullable|unique:photos|string|max:255',
            'date' => 'nullable|max:255',
        ]);
        // return redirect('/')->with('status', 'danger')->with('content', 'Gallerie introuvable (lien invalide)');

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        if (! $gallery = gallery::where('slug', $request->gallery)->first() )
        {
            abort(404, 'gallery');
        }

        $imageName = time().'.'.request()->img->getClientOriginalExtension();
        request()->img->move(public_path('img'), $imageName);

        $photo = photo::create([
            'slug'  => time(),
            'gallery' => $request->get('gallery'),
            'gallery_name' => $gallery->name,
            'background' => $request->get('background'),
            'src' => '/img/'.$imageName,
            'name' => $request->get('name'),
            'date' => $request->get('date'),
            'text' => $request->get('text'),
        ]);
        return redirect('/'.$request->get('gallery'))->with('status', 'success')->with('content', 'Photo ajoutée');
    }



    public function edit($slug) {

        if (! $photo = photo::where('slug', $slug)->first())
        {
            abort(404, 'photo');
        }
        return view('photo.edit', compact('photo'));
    }





    public function update(Request $request, $slug) {

        if (! $photo = photo::where('slug', $slug)->first())
        {
            abort(404, 'photo');
        }

        $validator = Validator::make($request->all(), [
            'img' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
            'background' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'date' => 'nullable|max:255',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json(compact('error'), 400);
        }
        if($request->img) {
            $imageName = time().'.'.request()->img->getClientOriginalExtension();
            File::delete(public_path().$photo->src);
            error_log(public_path().$photo->src);
            request()->img->move(public_path('img'), $imageName);

            $photo->src = '/img/'.$imageName;
            $photo->background = $request->get('background');
            $photo->name = $request->get('name');
            $photo->date = $request->get('date');
            $photo->text = $request->get('text');
            $photo->save();
        } else {
            $photo->background = $request->get('background');
            $photo->name = $request->get('name');
            $photo->date = $request->get('date');
            $photo->text = $request->get('text');
            $photo->save();
        }

        return redirect('/photo/'.$photo->slug)->with('status', 'success')->with('content', 'Photo modifiée');
    }





    public function destroy(Request $request, $slug)
    {
        
        if (! $photo = photo::where('slug', $slug)->first())
        {
            abort(404, 'photo');
        }
        File::delete(public_path().$photo->src);
        $photo->delete();
        return redirect('/'.$photo->gallery)->with('status', 'success')->with('content', 'Photo supprimée');
    }



}
