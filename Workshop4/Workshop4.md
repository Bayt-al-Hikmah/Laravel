## Objectives
- Upload and manage files.
- Work with settings files (`.env`) and project configuration (`config/filesystems.php`).
- Understand middleware and Providers.
- Implement testing and unit tests to ensure code quality.

## Upload and Manage Files Efficiently
### Introduction
In modern web applications, allowing users to upload files such as images, documents, or other media is a common requirement. Whether it’s profile pictures, project files, or shared resources, file uploads add interactivity and functionality to our app.

However, handling file uploads in a web application introduces challenges like storage management, security, and performance optimization. Laravel provides a robust and secure framework for managing file uploads through its powerful Filesystem abstraction, making it easy to integrate this feature.

We’ll explore how to configure Laravel to handle file uploads, discuss best practices for secure and efficient file management, and build a simple image-sharing app as an example. We’ll also cover how to manage URLs effectively using named routes and the `route()` helper function for cleaner, more maintainable code.

### Configuration for File Uploads
To enable file uploads in a Laravel project, we need to configure our application's "filesystems." Laravel uses the concept of "disks," which are storage locations. These are defined in the `config/filesystems.php` file.

By default, Laravel provides a `public` disk. This disk is intended for files that should be publicly accessible, like user-uploaded images.
- Files on the `public` disk are stored in the `storage/app/public` directory.
- To make them accessible from the web, we must create a symbolic link from `public/storage` to `storage/app/public`.

We can create this symbolic link by running a simple Artisan command:
```shell
php artisan storage:link
```
After running this command, files stored in `storage/app/public` will be accessible via URLs that start with `/storage/`. For example, a file at `storage/app/public/myphoto.jpg` can be accessed at:
```
http://127.0.0.1:8000/storage/myphoto.jpg
```
This setup is suitable for both development and production. In a production environment, our web server (like Nginx or Apache) should be configured to serve files from the `public` directory, and the symlink will ensure that requests to `/storage` are correctly routed to our uploaded files.

### Building an Image Sharing App
To put this into practice, we’ll create a simple Image Sharing App that allows users to upload and view images. Each uploaded image will include a title and display on a gallery page.
#### Creating a New Laravel Project
First, let's create a new project and move into its directory.
```shell
composer create-project laravel/laravel workshop4
cd workshop4
```
#### Create the Photo Model and Migration
Let's create a simple model and a database migration for our app to store the uploaded images.
```shell
php artisan make:model Photo -m
```
This command creates two files:
1. A model file at `app/Models/Photo.php`.
2. A migration file in the `database/migrations/` directory.

Now, let's edit the **migration file** to define our `photos` table schema:

**`database/migrations/..._create_photos_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('image_path'); // Stores the path to the file
            $table->timestamps(); // Creates uploaded_at and updated_at
        });
    }
    // ...
};
```
In this schema, we define three main fields. The **`title`** field is a `string` that stores a short descriptive name. The **`image_path`** field is also a `string` that will store the path to the uploaded file on our storage disk (e.g., `uploads/my-image.jpg`). 

Next, let's update our **`app/Models/Photo.php`** model to allow "mass assignment" for our fields. This is a security feature.

**`app/Models/Photo.php`**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image_path'];
}
```
Finally we run our migration to create the table in the database.
```shell
php artisan migrate
```
#### Creating the Controller
Now, let's create a controller to handle the logic for displaying the gallery and uploading new photos.
```shell
php artisan make:controller PhotoController
```
This creates the file `app/Http/Controllers/PhotoController.php`. Let's add our methods.

**``app/Http/Controllers/PhotoController.php``**
```php
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
```
We created three methods for ou controller:
- `gallery()`: get all the images and pass them to the galery template so we display them
- `create()`: this display the upload template where users can see the form to upload images
- `store()`: this method do the most of the buisness here how it work
	1. **`validate()`**: We `StorePhotoRequest` request to validate the data submited by users, Laravel automatically redirects the user back to the form with error messages.
	    
	2. **`$request->file('image')->store(...)`**: This is the core file upload logic.
	    - `$request->file('image')` retrieves the uploaded file object.
	    - `->store('uploads', 'public')` tells Laravel to:
	        - Save the file in a directory named `uploads`.
	        - Use the `public` disk which we configured to be `storage/app/public`.
	        - It automatically generates a unique, secure filename.
	        - It returns the relative path (e.g., `uploads/aB3xYqZ...jpg`).
	3. **`Photo::create(...)`**: We create a new `Photo` model and save it to the database, storing the `title` and the `image_path` returned by the `store` method.
	4. **`redirect()->route('gallery')`**: We redirect the user back to the gallery page using its **route name**, which we will define next.
#### Creating the Request Validator
We need to create `StorePhotoRequest`  Form Request so we can use it, we do that by creating new Form Request .
```
php artisan make:request StorePhotoRequest
```
After that we edit the new file `app/Http/Requests/StorePhotoRequest.php` to add our rules:

**`app/Http/Requests/StorePhotoRequest.php`**
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ];
    }
}
```
This class will automatically validate incoming requests, ensuring the `title` is present and the `image` is a valid image file under 2MB.

#### Creating the Templates
We need now templates to display for the user we create the Blade templates in the `resources/views/` directory.

**`resources/views/gallery.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Gallery</title>
    {{-- We'll assume a CSS file is in public/css/style.css --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Photo Gallery</h1>
    <a href="{{ route('upload.create') }}" class="btn">Upload New Photo</a>

    <div class="gallery">
    
        @forelse ($photos as $photo)
            <div class="photo-card">
                <img src="{{ asset('storage/' . $photo->image_path) }}" alt="{{ $photo->title }}">
                <p>{{ $photo->title }}</p>
                <small>Uploaded at: {{ $photo->created_at->format('Y-m-d H:i') }}</small>
            </div>
        @empty
            <p>No photos uploaded yet.</p>
        @endforelse
    </div>
</body>
</html>
```
 The image URL is generated using `{{ asset('storage/' . $photo->image_path) }}`. The `asset()` helper generates a full URL, and we prepend `storage/` because that is the public-facing path created by our `storage:link` command.
 
**`resources/views/upload.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Photo</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Upload a New Photo</h1>

    <form method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}">
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" name="image" id="image">
            @error('image')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">Upload</button>
    </form>

    <a href="{{ route('gallery') }}" class="btn">Back to Gallery</a>
</body>
</html>
```
This template will handel uploading files, for the style we will use the ``style.css`` file that exist inside ``metarials`` folder.
#### Configuring the Routes
Finally, we need to set up the URL configurations in our `routes/web.php` file so Laravel knows how to route requests to our controller methods.

**`routes/web.php`**
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoController;

// The root path (/) will show the gallery
Route::get('/', [PhotoController::class, 'gallery'])->name('gallery');

// The /upload path will show the upload form
Route::get('/upload', [PhotoController::class, 'create'])->name('upload.create');

// Posting to /upload will handle the file storage
Route::post('/upload', [PhotoController::class, 'store'])->name('upload.store');
```
This file defines three routes:
1. A `GET` route for `/` that maps to the `gallery` method and is named **`gallery`**.
2. A `GET` route for `/upload` that maps to the `create` method and is named **`upload.create`**.
3. A `POST` route for `/upload` that maps to the `store` method and is named **`upload.store`**.

These route names (`->name(...)`) are what allow us to use the `route('gallery')` and `route('upload.create')` helpers in our Blade templates.

Now, if we run the development server:
```shell
php artisan storage:link
php artisan serve
```
And open the browser at **`http://127.0.0.1:8000/`**, we’ll see the **photo gallery page**. From there, we can click on **“Upload New Photo”** to go to the upload form, select an image, and submit it.

After uploading, Laravel will automatically save the image inside the **`storage/app/public/uploads/`** folder, create the database record, and it will instantly appear in the gallery view.