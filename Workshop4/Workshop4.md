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

## Project Configuration
In the previous sections, we built applications using Laravel's default settings. While these defaults are excellent for getting started, real-world projects often require more specific configurations.

We will see how to customize our Laravel project's settings to manage assets , connect to different databases, centralize views, and implement advanced URL routing with named routes and dynamic parameters.

Most project-wide configuration is handled in two places:
1. The `config/` directory contains files that define the structure of our application's configuration.
2. The `.env` (environment) file at our project's root contains the specific values for our current environment (development, production, etc.). Best practice is to never commit the `.env` file to source control.
### Asset Configuration
While "storage" files  are uploaded by users, "assets" are the files we provide as part of our application's design, such as CSS, JavaScript, and site logos.

Laravel provides a simple way to handle this, but the modern, recommended best practice is to use a build tool like Vite.
#### Modern Asset Bundling
By default, Laravel is configured to use Vite to compile our frontend assets. This is the modern, preferred approach as it allows us to use modern tools like SASS, TypeScript, and Vue/React, and it optimizes our files for production.

Our "source" asset files live in the `resources/` directory (e.g., `resources/css/app.css`, `resources/js/app.js`).
- **In development:** We run the Vite development server, which handles "hot module replacement" (HMR) for instant browser updates.
    ```shell
    npm run dev
    ```
- **For production:** We run the build command, which compiles, minifies, and versions our assets into a `public/build/` directory.
    ```
    npm run build
    ```
    

We then include these assets in our main layout file (e.g., `resources/views/layouts/app.blade.php`) using the `@vite()` Blade directive.

**Example**
```html
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    </body>
</html>
```
This `@vite` directive is the equivalent of configuring paths; it automatically figures out whether to load from the `npm run dev` server or from the production `public/build/` directory.

#### Simple Static Assets
If we are not using a build process, we can place simple, pre-compiled assets directly into the `public/` directory (e.g., `public/css/style.css`).

We can then link to them in your templates using the `asset()` helper, which generates the correct URL.
```html
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
```
The `asset()` helper defines the base URL path through which static files are accessed in the browser. For example, `style.css` will be available at: `http://127.0.0.1:8000/css/style.css`.
### Views Configuration
Similar to assets, Laravel simplifies template organization. By default, it looks for all your Blade templates inside a single, centralized `resources/views/` directory.

We can organize files within this directory using subfolders. For example, if we have a `blog` feature, we might create a file at: `resources/views/blog/gallery.blade.php`

The folder structure inside `resources/views/` creates a "namespace" for our views. When we want to return this view from a controller, we use dot notation to reference the path.
```php
// app/Http/Controllers/BlogController.php

public function showGallery()
{
    // This loads the file at:
    // resources/views/blog/gallery.blade.php
    return view('blog.gallery');
}
```
This centralized approach makes it very easy to share layouts and components.

#### Example
To illustrate this, let's make our `blog` view take its layout from a `base.blade.php` file inside this centralized folder. We'll create a single `resources/views/` folder with our layout and view files.
```
my_project/
├── app/
├── config/
├── public/
├── ...
└── resources/
    └── views/  <-- Our central folder
        ├── layouts/
        │   └── base.blade.php
        └── blog/
            └── post_list.blade.php
```
In a template like `resources/views/blog/post_list.blade.php`, we can now write:
```php
{{-- We use dot notation for the layouts.base path --}}
@extends('layouts.base')

@section('content')
    <p>This is the blog post list.</p>
@endsection
```
This works perfectly because all views are loaded from the same `resources/views/` root directory, making it simple and clean to share layouts.
### Database Configuration
By default, Laravel is configured to use values from our `.env` file. This file-based database setting is suitable for development. For production, we will use a more robust database like PostgreSQL or MySQL, and you can change this by simply updating our `.env` file.

All database options are defined in `config/database.php`, but the values used are pulled from our `.env` file.
#### SQLite Configuration
The default `.env` file often includes a SQLite configuration, which is great for getting started quickly.
```
# .env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/project/database/database.sqlite
```
- **`DB_CONNECTION`**: This tells Laravel to use its built-in driver for **SQLite**.
- **`DB_DATABASE`**: Unlike server-based databases, SQLite stores the entire database in a single file. This line must be an absolute path to where we want that file to live. We can use Laravel's `database_path('db.sqlite3')` helper in `config/database.php` to default this to the `database/` folder.
#### PostgreSQL Configuration
Before Laravel can communicate with a PostgreSQL database, our PHP environment needs the **`pdo_pgsql`** extension enabled in our `php.ini` file.

After this, you just change the `DB_` variables inside the `.env` file to log in to your database:
```
# .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=workshop_db
DB_USERNAME=postgres
DB_PASSWORD=mypassword
```
- **`DB_CONNECTION`**: Tells Laravel which driver to use (`pgsql` for PostgreSQL).
- **`DB_DATABASE`**: The name of the specific database. We must create this database (named `workshop_db` in this example) inside our PostgreSQL server before we  run `php artisan migrate`.
- **`DB_USERNAME`**: The username to log in.
- **`DB_PASSWORD`**: The password for that user.
- **`DB_HOST`**: The address of our database server (`127.0.0.1` or `localhost` is common).
- **`DB_PORT`**: The network port. `5432` is the standard, default port for PostgreSQL.
#### MySQL Configuration
Similarly, to connect to MySQL, we must ensure the **`pdo_mysql`** PHP extension is enabled.

Then, update the `.env` file with our MySQL server's details.
```
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workshop_db
DB_USERNAME=root
DB_PASSWORD=mypassword
```
These settings function just like the ones for PostgreSQL, but are specific to MySQL (`DB_CONNECTION=mysql`, `DB_PORT=3306`).

Laravel’s ORM (Eloquent) abstracts away database-specific differences, allowing us to switch between databases with minimal changes to our code. After modifying the configuration, we can test the connection by running:
```
php artisan migrate
```
If everything is configured correctly, Laravel will create the necessary database tables.
### Using Named Routes for URL Organization
As our project grows, it's a bad practice to hard-code URLs in our templates and controllers. If a URL changes, we have to find and replace it everywhere.

To solve this, Laravel uses Named Routes. We give a specific route a unique name, and then reference that name.
#### In Our `routes/web.php` File:
We add a name to a route by chaining the `->name()` method.
```php
// routes/web.php
use App\Http\Controllers\PhotoController;

Route::get('/', [PhotoController::class, 'gallery'])->name('gallery');
Route::get('/upload', [PhotoController::class, 'create'])->name('upload.create');
Route::post('/upload', [PhotoController::class, 'store'])->name('upload.store');
```
To organize this, especially in large projects, we can group routes and give them a name prefix. This is the best-practice equivalent of "namespacing."
```php
// routes/web.php
use App\Http\Controllers\PhotoController;

Route::name('image_share.')->group(function () {
    Route::get('/', [PhotoController::class, 'gallery'])->name('gallery');
    Route::get('/upload', [PhotoController::class, 'create'])->name('upload.create');
});
```
Now, the names for these routes are `image_share.gallery` and `image_share.upload.create`.
#### Referencing Standard URLs
We refere to the routes from the templates using the route() helper with the route's name.
```php
<a href="{{ route('image_share.gallery') }}">View Gallery</a>

<a href="{{ route('image_share.upload.create') }}">Upload New Photo</a>
```
And if we are inside the controller we use the ``redirect()->route()`` helper.
```php
// app/Http/Controllers/SomeController.php
use Illuminate\Support\Facades\Redirect;

public function myView()
{
    // ...
    // The redirect helper is most common.
    // It understands the 'image_share.gallery' format.
    return redirect()->route('image_share.gallery');
    
    // Or
    
    // We can also use the route() helper explicitly to get the URL string
    // This is useful if we need to use the URL in another way
    $urlPath = route('image_share.gallery'); // This will return the string '/'
    return redirect($urlPath);
}
```

#### Referencing Dynamic URLs
This is where named routes are most powerful. Let's imagine our `image_share` app has a dynamic URL for a photo's detail page.

**`routes/web.php`:**
```php
// This URL expects a parameter named 'photo_id'
Route::get('photo/{photo_id}', [PhotoController::class, 'detail'])
     ->name('image_share.photo_detail');
```
To reference this URL, we must provide a value for `photo_id`.

In Templates  we pass the parameters to the `route()` helper as a second argument, an array.
```php
@foreach ($photos as $photo)
    <a href="{{ route('image_share.photo_detail', ['photo_id' => $photo->id]) }}">
        View Photo {{ $photo->title }}
    </a>
@endforeach
```
If the parameter name (`photo_id`) matches the variable (`$photo->id`) and is the only parameter, Laravel is smart enough to let you pass it directly.
```php
@foreach ($photos as $photo)
    {{-- This simpler version also works --}}
    <a href="{{ route('image_share.photo_detail', $photo->id) }}">
        View Photo {{ $photo->title }}
    </a>
@endforeach
```
If we want to reference the url from Controllers, we pass the dynamic data as a second argument to `route()`.
```php
// app/Http/Controllers/PhotoController.php
use Illuminate\Support\Facades\Redirect;

public function photoUploadSuccess($new_photo_id)
{
  
    // --- Method 1: The redirect() shortcut 
    return redirect()->route('image_share.photo_detail', ['photo_id' => $new_photo_id]);
    
    
    // --- Method 2: Using the route() 
    $url = route('image_share.photo_detail', ['photo_id' => $new_photo_id]);
    // $url is now '/photo/5'
    return redirect($url);
}
```