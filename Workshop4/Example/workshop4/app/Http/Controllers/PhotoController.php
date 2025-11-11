<?php
namespace App\Http\Controllers;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePhotoRequest;

class PhotoController extends Controller
{

    public function gallery()
    {

        $photos = Photo::latest()->get();
        return view('gallery', ['photos' => $photos]);
    }

    public function create()
    {
        return view('upload');
    }

    public function store(StorePhotoRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('image')->store('uploads', 'public');

        Photo::create([
            'title' => $validated['title'],
            'image_path' => $path,
        ]);

        return redirect()->route('gallery')->with('success', 'Photo uploaded successfully!');
    }
}