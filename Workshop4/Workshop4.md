## Objectives
- Upload and manage files.
- Work with Project Configuration.
- Understand middleware and Providers.
- Implement Testing
## Upload and Manage Files Efficiently
### Introduction
In modern web applications, allowing users to upload files such as images, documents, or other media is a common requirement. Whether it’s profile pictures, project files, or shared resources, file uploads add interactivity and functionality to our app.   
However, handling file uploads in a web application introduces challenges like storage management, security, and performance optimization. 
### Configuration for File Uploads
To enable file uploads in a Laravel project, we need to configure our application's "filesystems." Laravel uses the concept of "disks", which are storage locations. These are defined in the `config/filesystems.php` file.

By default, Laravel provides a `public` disk. This disk is intended for files that should be publicly accessible, like user-uploaded images.
- Files on the `public` disk are stored in the `storage/app/public` directory.

To make those file accessible from the web, we create a symbolic link, we do that by running:
```shell
php artisan storage:link
```
After running this command, files stored in `storage/app/public` will be accessible via URLs that start with `/storage/`. For example, a file at `storage/app/public/myphoto.jpg` can be accessed at:
```
http://127.0.0.1:8000/storage/myphoto.jpg
```
### Building an Image Sharing App
Let's put this into practice, and create a simple Image Sharing App that allows users to upload and view images. Each uploaded image will include a title and display on a gallery page.
#### Creating a New Laravel Project
First, let's create a new project.
```shell
composer create-project laravel/laravel workshop4
cd workshop4
```
#### Create the Photo Model and Migration
After this, we create model and database migration so we can store and keep track of the uploaded images.
```shell
php artisan make:model Photo -m
```
we edit the migration file to define our `photos` table schema, we need tow main fields:
- `title` field is a `string` that stores a short descriptive name. 
- `image_path` field is also a `string` that will store the path to the uploaded file.

**`database/migrations/..._create_photos_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void{
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
Next, we update our `app/Models/Photo.php` model to allow "mass assignment" for our fields.   
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
Now, we move to build our controller logic, first we create `PhotoController` controller.
```shell
php artisan make:controller PhotoController
```
This creates the file `app/Http/Controllers/PhotoController.php`. The contoller will need three main methods.
- `gallery()`: Get all the images and pass them to the ciew.
- `create()`: Display the upload view where users can see the form to upload images.
- `store()`: Handel submited and uploaded images, First we validate the request, then we use `$request->file('image')->store(...)` to retrieve the uploaded file object (`$request->file('image')`), store it `->store('uploads', 'public')` under the `uploads` directory named `uploads` and use `public` disk which we configured to be `storage/app/public`, it automatically generates a unique, secure filename. and returns the relative path (e.g., `uploads/aB3xYqZ...jpg`). After that we add the record to the database table


**``app/Http/Controllers/PhotoController.php``**
```php
<?php
namespace App\Http\Controllers;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePhotoRequest;

class PhotoController extends Controller{
    public function gallery(){
        $photos = Photo::latest()->get();
        return view('gallery', ['photos' => $photos]);
    }

    public function create(){
        return view('upload');
    }

    public function store(StorePhotoRequest $request){
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
#### Creating the Request Validator
We need the validator `StorePhotoRequest`  Form Request 
```shell
php artisan make:request StorePhotoRequest
```
We return ``true`` from ``authorize`` method (we not applying authentication for this app), and we set and return our input validations inside the `rules` method.

**`app/Http/Requests/StorePhotoRequest.php`**
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest{
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
#### Creating the Views
Finally we create the Blade templates in the `resources/views/` directory.  
We start with  view that display images, the image URL is generated using `{{ asset('storage/' . $photo->image_path) }}`. 

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
We create other Blade template which display upload images form, for the style we will use the ``style.css`` file that exist inside ``metarials`` folder.  
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
#### Configuring the Routes
Now everything is set we configure the routes in the `routes/web.php` file.   
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
Now, if we run the development server:
```shell
php artisan storage:link
php artisan serve
```
And open the browser at `http://127.0.0.1:8000/`, we’ll see the photo gallery page. From there, we can click on “Upload New Photo” to go to the upload form, select an image, and submit it.   
After uploading, Laravel will automatically save the image inside the `storage/app/public/uploads/` folder, create the database record, and it will instantly appear in the gallery view.
## Project Configuration
In the previous workshops, we built applications using Laravel's default settings. While these defaults are excellent for getting started, real-world projects often require more specific configurations.  
The project configurations are handled in two places:
1. The `config/` directory contains files that define the structure of our application's configuration.
2. The `.env` (environment) file at our project's root contains the specific values for our current environment (development, production, etc.). 
#### Asset Configuration
By default, Laravel is configured to use Vite to compile our frontend assets. This is the modern, preferred approach as it allows us to use modern tools like SASS, TypeScript, and Vue/React, and it optimizes our files for production.  
Our "source" asset files live in the `resources/` directory (e.g., `resources/css/app.css`, `resources/js/app.js`).
- In development: We run the Vite development server, which handles "hot module replacement" (HMR) for instant browser updates.
```shell
npm run dev
```
- For production: We run the build command, which compiles, minifies, and versions our assets into a `public/build/` directory.
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
### Views Configuration
Similar to assets, Laravel simplifies template organization. By default, it looks for all our Blade templates inside a single, centralized `resources/views/` directory.  
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
### Database Configuration
All database options are defined in `config/database.php`, but the values used are pulled from our `.env` file.
#### SQLite Configuration
The default `.env` file often includes a SQLite configuration, which is great for getting started quickly.
```
# .env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/project/database/database.sqlite
```
- `DB_CONNECTION`: This tells Laravel to use its built-in driver for SQLite.
- `DB_DATABASE`: This line is the absolute path to the database file. We can use Laravel's `database_path('db.sqlite3')` helper in `config/database.php` to default this to the `database/` folder.
#### PostgreSQL Configuration
Before Laravel can communicate with a PostgreSQL database, our PHP environment needs the `pdo_pgsql` extension enabled in our `php.ini` file.  
After this, we just change the `DB_` variables inside the `.env` file to log in to our database:
```
# .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=workshop_db
DB_USERNAME=postgres
DB_PASSWORD=mypassword
```
- `DB_CONNECTION`: Tells Laravel which driver to use (`pgsql` for PostgreSQL).
- `DB_DATABASE`: The name of the specific database. We must create this database (named `workshop_db` in this example) inside our PostgreSQL server before we  run `php artisan migrate`.
- `DB_USERNAME`: The username to log in.
- `DB_PASSWORD`: The password for that user.
- `DB_HOST`: The address of our database server (`127.0.0.1` or `localhost` is common).
- `DB_PORT`: The network port. `5432` is the standard, default port for PostgreSQL.
#### MySQL Configuration
Similarly, to connect to MySQL, we must ensure the `pdo_mysql` PHP extension is enabled.  
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
Route::post('/upload', [PhotoController::class, 'store'])->name('upload.store');
```
We can also group routes and give them a name prefix.
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
We refere to the routes from the templates using the ``route()`` helper with the route's name.
```php
<a href="{{ route('image_share.gallery') }}">View Gallery</a>
<a href="{{ route('image_share.upload.create') }}">Upload New Photo</a>
```
And if we are inside the controller we use the ``redirect()->route()`` helper.
#### Referencing Dynamic URLs
Let's imagine our `image_share` app has a dynamic URL for a photo's detail page.  
**`routes/web.php`:**
```php
Route::get('photo/{photo_id}', [PhotoController::class, 'detail'])
     ->name('image_share.photo_detail');
```
To reference this URL, we must provide a value for `photo_id`.   
In Templates we pass the parameters to the `route()` helper as a second argument, an array.
```php
@foreach ($photos as $photo)
    <a href="{{ route('image_share.photo_detail', ['photo_id' => $photo->id]) }}">
        View Photo {{ $photo->title }}
    </a>
@endforeach
```
If we want to reference the url from Controllers, we array  as a second argument to `route()`.
```php
use Illuminate\Support\Facades\Redirect;

public function photoUploadSuccess($new_photo_id){
    return redirect()->route('image_share.photo_detail', ['photo_id' => $new_photo_id]);
}
```
## Providers
Before our web application can even think about handling a request, it needs to be "booted." This means loading configuration files, registering services, and preparing all the different parts of the framework. In Laravel, the central place to do all this is a Service Provider.   
All of our application's service providers are configured in the `providers` array in our `config/app.php` file.
### How Providers Work
Service provider, has to main methods: `register()` and `boot()`. 
#### The `register()` Method
The `register` method is called first on all providers. it job is to bind things into the service container. we should never try to use another service inside the `register` method. 
#### The `boot()` Method
The `boot` method is called after all other providers have been registered, this method is where we can safely do almost anything, By the time `boot` is called, we can be certain that all other services have been registered and are available to be used.   
This is the correct place to:
- Register event listeners.
- Add custom validation rules.
- Register route middleware aliases.
- Load custom route files.
- Register view composers (to share data with Blade views).
### Creating a Custom Provider
We can easily create our own service provider using an Artisan command:
```
php artisan make:provider AnalyticsServiceProvider
```
This will create a new file at `app/Providers/AnalyticsServiceProvider.php`. Then we should add it to the  `providers` array in `config/app.php` so Laravel will load it.
### Example Use Case
Let's imagine we have a custom analytics service that we want to use in multiple controllers. This service needs an API key from our `.env` file to work.   
We want to be able to "type-hint" this service in any controller, and have Laravel automatically create it for us with the API key already included.
#### The Service Class
First, we'd have our simple service class.

**`app/Services/AnalyticsService.php`**
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    protected $apiKey;

    // The constructor requires an API key to be passed in
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function trackEvent(string $name, array $data = [])
    {
        // In a real app, this would send a request to an external API
        // For this example, we'll just log it.
        Log::info("ANALYTICS EVENT: {$name}", [
            'data' => $data,
            'key_used' => $this->apiKey, // Proves our key was passed
        ]);
    }
}
```
#### The Configuration
We add our API key to the `config/services.php` file, which reads from `.env`.

**`config/services.php`**
```php
<?php

return [
    // ... other services
    
    'analytics' => [
        'key' => env('ANALYTICS_API_KEY'),
    ],
];
```
#### The Provider
Now, in the `AnalyticsServiceProvider` we just created, we use the `register` method to tell Laravel how to build our `AnalyticsService` whenever we ask for it.

**`app/Providers/AnalyticsServiceProvider.php`**
```php
<?php

namespace App\Providers;

use App\Services\AnalyticsService;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // We use 'singleton' because we only need ONE instance
        // of our AnalyticsService for the entire request.
        $this->app->singleton(AnalyticsService::class, function ($app) {
            
            // Get the API key from the config file
            $apiKey = config('services.analytics.key');

            // Create and return the new service instance,
            // passing in the key.
            return new AnalyticsService($apiKey);
        });
    }
}
```
#### Using the Service 
After adding our provider to `config/app.php`, we can now use dependency injection in any controller.

**`app/Http/Controllers/UserController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService; // Import our service
use App\Models\User;

class UserController extends Controller
{
    // Because we type-hint 'AnalyticsService', Laravel will
    // automatically run our provider's logic and inject
    // the fully-configured service for us.
    public function store(Request $request, AnalyticsService $analytics)
    {
        $user = User::create($request->all());

        // Now we can just use it!
        $analytics->trackEvent('user_registered', ['id' => $user->id]);

        return redirect()->route('dashboard');
    }
}
```
We never have to write `new AnalyticsService(...)` in our controller. The service provider handles all the setup, keeping our controller clean and making our `AnalyticsService` easy to manage and use anywhere.
## Middlewares:
### Introduction
Sometimes in our web application, we need to process requests and responses globally before they reach the controller or after they leave it. Laravel provides a powerful mechanism for this through middleware.  
Middleware are hooks that sit between the web server and our controller. Each middleware component is a lightweight layer that can inspect or modify the HTTP request before it reaches our application's logic, or process the response before it is returned to the client.  
When a request comes in, it passes through each middleware layer (from top to bottom) before it reaches our controller. After our controller produces a response, that response passes back through the layers (from bottom to top) before being sent to the browser.
### How Middleware Works
We can visualize middleware as a series of layers, like an onio, with our controller at the very center. Every request and response must pass through these layers.  
This process happens in two distinct phases:
1. Request Phase: The request travels inward through each middleware layer before it reaches the controller.
2. Response Phase: The response travels outward through each layer (in reverse order) before it’s sent back to the client.
#### The Flow of Data
This flow is critical to understand. A request passes _down_ through the list, and the response passes _up_.
```
Client
   │
   │ Request (Top-Down)
   ↓
[ Middleware 1 ]
   ↓
[ Middleware 2 ]
   ↓
[ Middleware 3 ]
   ↓
( Your Controller )
   ↑
[ Middleware 3 ]
   ↑
[ Middleware 2 ]
   ↑
[ Middleware 1 ]
   ↑
   │ Response (Bottom-Up)
   │
Client
```
#### Order Is Critical
We register and configure middleware in the `bootstrap/app.php` file using the `withMiddleware` method.  
The order in which middleware is defined is still not arbitrary; it defines the execution order.
```php
// bootstrap/app.php

->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    
    // This is for GLOBAL middleware that runs on every request
    $middleware->use([
        // \App\Http\Middleware\AnotherGlobalMiddleware::class,
    ]);

    // This is for MIDDLEWARE GROUPS
    $middleware->group('web', [
        // \App\Http\Middleware\MyCustomWebMiddleware::class,
    ]);

    // This is for MIDDLEWARE ALIASES (named routes)
    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    ]);
})
```
There is three way to register middlewar
- `$middleware->use()`: apply the middlewar to all our routes and controllers.
- `$middleware->group()`: apply the middlewar to specific group.
- `$middleware->alias()`: configure middleware to specific named routes. 

The `StartSession` middleware must run before the `ShareErrorsFromSession` middleware. The request must first have the session loaded (top-down) before the error middleware can use that session to share errors with your views.
#### Capabilities of Middleware
During this two-way journey, each middleware layer has the power to:
- Inspect and modify the incoming `Request` object before it reaches the controller.
- Process and modify the outgoing `Response` object after the controller has finished.
- Handle exceptions that might be raised.
- Execute custom logic, such as performing authentication checks, logging, adding security headers, or managing sessions.
- Short-circuit the request entirely and return its own response (e.g., for authentication or IP blocking).
### Creating Custom Middleware
Wen can easily create our own middleware class using:
```
php artisan make:middleware LoggingMiddleware
```
This creates a new file at `app/Http/Middleware/LoggingMiddleware.php`. All middleware classes have a `handle` method which have two parameter:
- `$request` is the incoming HTTP request.
- `$next` is a `Closure` an anonymous function that represents the next layer of the onion. Calling `$next($request)` will pass the request to the next middleware or, eventually, to the controller.

In our middlewarewe can run our logic after and before running the controller:
- We run instructions before the controller  by adding our logic before `$response = $next($request)`.
- We run instructions after the controller by adding our logic after `$response = $next($request)`.
#### Example 1: Logging Request and Response
Let's make a middleware that logs the HTTP method and path, as well as the response status code after the controller runs. This is an "after" middleware, as it acts on the response.
**`app/Http/Middleware/LoggingMiddleware.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import the Log facade

class LoggingMiddleware{
    public function handle(Request $request, Closure $next){
        $response = $next($request);
        Log::info(
          "[Response] Status Code: {$response->getStatusCode()}"
        );
        return $response;
    }
}
```

Now to use the middleware we register it in `bootstrap/app.php`. This middleware will run for the For a web routes, we  append it to the `web` middleware group:
**`bootstrap/app.php`**

```php
->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    
    // Add our new middleware to the 'web' group
    $middleware->appendToGroup('web', [
        \App\Http\Middleware\LoggingMiddleware::class,
    ]);

})
```
Now when we visit any web page, our `storage/logs/laravel.log` file will get messages like:
```
[2025-11-10 10:30:01] local.INFO: [Response] Status Code: 200
```
#### Example 2: Blocking a Specific IP
Sometimes, we may want to block access from certain IP addresses. This is a "before" middleware, as it can stop the request before it ever reaches the controller.  
Let's create the middleware:
```
php artisan make:middleware BlockIpMiddleware
```

**`app/Http/Middleware/BlockIpMiddleware.php`**
```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Abort; // Use Abort facade

class BlockIpMiddleware{
    public function handle(Request $request, Closure $next){
        $blockedIps = ['127.0.0.2']; // You can add more IPs here
        
        // Get the user's IP address
        $clientIp = $request->ip();

        // Check if the user's IP is in the blocked list
        if (in_array($clientIp, $blockedIps)) {
            Abort(403, "Access denied from your IP address.");
        }

        // Otherwise, continue normally
        return $next($request);
    }
}
```
We then register this in `bootstrap/app.php`. Since this is a security-related middleware, we should prepend it to the `web` group to ensure it runs before anything else.

**`bootstrap/app.php`**
```php
->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    
    // Prepend our IP blocker to the 'web' group
    $middleware->prependToGroup('web', [
        \App\Http\Middleware\BlockIpMiddleware::class,
    ]);

})
```
Now, any request from the IP `127.0.0.2` will be immediately stopped and shown a "403 Forbidden" error page.
## Implement Testing
Testing is a crucial part of software development that ensures our code behaves as expected, catches bugs early, and maintains reliability as our project evolves. In Laravel, testing helps verify that our controllers, models, services, and other components work correctly.
### Testing Our Programs and Errors
Before diving into automated tests, it's important to manually verify our code and handle common errors. This hands-on approach helps us understand issues quickly during development.
#### Verifying and Fixing by Ourselves
Manual testing involves running our application and checking its behavior step by step. Start by using Laravel's development server (`php artisan serve`) and interact with our app.
- Test core functionalities: For example, in our image-sharing app, we upload a photo, check if it appears in the gallery, and verify the image URL works.
- Simulate user inputs: Try valid and invalid data.
- Check edge cases: What happens with large files, empty titles, or concurrent uploads?
- Use Laravel's debug mode: With `APP_DEBUG=true` in our `.env` file, Laravel shows detailed error pages via its built-in tool, Ignition.

If we encounter bugs, fix them iteratively:
- Read the error messages on the Ignition page carefully; they often point to the exact file and line of code.
- Use helper functions like `dd()` (die and dump) or `dump()` in our code to inspect variables.
- Restart the server after changes (if needed, though often it's not) and retest.
#### Handling Common Errors
Laravel provides built-in, graceful error handling, allowing us to customize responses for common HTTP errors like 404 (Page Not Found) or 500 (Server Error). This improves user experience by showing friendly pages instead of raw errors.  
To enable custom error pages, we simply need to create Blade template files in the `resources/views/errors/` directory. Then we set `APP_DEBUG=false` ,with this Laravel will automatically find and render these views.

**Custom 404 Page**: Create `resources/views/errors/404.blade.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>Sorry, the page you're looking for doesn't exist.</p>
    {{-- Assuming you have a named route for your gallery --}}
    <a href="{{ route('gallery.index') }}">Back to Gallery</a>
</body>
</html>
```
**Custom 500 Page**: Create `resources/views/errors/500.blade.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Error</title>
</head>
<body>
    <h1>500 - Server Error</h1>
    <p>Oops! Something went wrong on our end. Please try again later.</p>
    <a href="{{ route('gallery.index') }}">Back to Gallery</a>
</body>
</html>
```
That's it. Laravel's framework handles the rest.

If we need to add custom logic to our error handling, we can do so in the `App/Exceptions/Handler.php` file. We can modify the `register` method to render custom views or perform actions based on the exception type.
```PHP
// app/Exceptions/Handler.php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler{

    public function register(): void
    {
        // other codes

        // We set costume response for 404 Error
        $this->renderable(function (NotFoundHttpException $e, $request) {
            // You can pass custom data to your 404 view
            return response()->view('errors.404', [
                'error_message' => 'The page you are looking for was not found.'
            ], 404);
        });
        // We set costume response for 500 Error
        $this->renderable(function (\Exception $e, $request) {
            // Handle 500 errors
            if (!config('app.debug')) {
                return response()->view('errors.500', [
                    'error_message' => 'Something went wrong on our end. Please try again later.'
                ], 500);
            }
        });
    }
}
```
Now, our `404.blade.php` and `500.blade.php` views can use the variables passed from these functions (like `{{ $error_message }}`), giving us full flexibility.
### The Need for Automated Testing
Even after manual fixes and error handling, we still need to ensure our apps work as expected over time. Bugs can creep in from code changes, and in group projects, one person's commit might break another's feature. Automated tests run quickly and repeatedly, catching issues early and ensuring the project remains stable.  
Laravel's testing framework is built on top of ``PHPUnit`` and provides a rich, fluent API for writing tests. This is essential for regression testing ensuring new changes don't break existing functionality.
### Feature and Unit Tests in Laravel
Laravel separates tests into two main categories, which we will find in our `tests/` directory:
- Unit Tests (`tests/Unit`): These tests focus on small, isolated parts of our code. They do not boot the full Laravel application, making them extremely fast.
- Feature Tests (`tests/Feature`): These tests check larger pieces of functionality, like a full HTTP request cycle. They boot the entire application, allowing us to test controllers, database interactions, and the responses sent to the user.

By default, Laravel uses an in-memory SQLite database (`:memory:`) or a configurable test database (`phpunit.xml` controls this) to keep tests fast and isolated from our real data.
#### Creating Test
We use the Artisan command to create a new test file.
```shell
# Create a Feature test (goes in tests/Feature)
php artisan make:test GalleryTest
# Create a Unit test (goes in tests/Unit)
php artisan make:test PhotoModelTest --unit
```
#### Setting The Test
Let's set the `PhotoModelTest.php` to test saving image record functinality, we will use assertion methods like `$this->assertEquals()` and `$this->assertNotNull()` to check expected vs. actual results.  
We also  use the `RefreshDatabase` trait to ensures that our database is completely reset to its original state before each test. 
```php
// tests/Unit/PhotoModelTest.php
namespace Tests\Unit;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase; // Good for models too!
use Tests\TestCase;

class PhotoModelTest extends TestCase{
    use RefreshDatabase;
    public function a_photo_can_be_created_with_a_title(): void
    {
        $photo = Photo::factory()->create(['title' => 'Test Photo']);
        $this->assertEquals('Test Photo', $photo->title);
        $this->assertNotNull($photo->created_at); 
    }
}
```
After that we set the `GalleryTest.php`.we write test for our models and routes/controllers.
We create two methods:
- `the_gallery_page_shows_photos` inside this method we create image record and see if it displayed inside out gallery. we use `$this->get()` / `$this->post()` to simulate an HTTP request to your application. This is how we test controllers and routes. we also use ``$response->assertSee(text)`` which ensures the given text appears in the HTTP response, and ``$response->assertStatus(200)`` to check checks the HTTP response code.
- `uploading_a_photo_requires_a_title` inside this method we try to create invalid record in our database and see if we will get error by using  ``$response->assertSessionHasErrors()`.

**``tests/Feature/GalleryTest.php``**
```php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Photo;
use Tests\TestCase;

class GalleryTest extends TestCase{
    use RefreshDatabase;

    public function the_gallery_page_shows_photos(): void{
        $photo = Photo::factory()->create(['title' => 'Test Photo']);
        $response = $this->get(route('gallery.index'));
        $response->assertStatus(200); 
        $response->assertSee('Test Photo');
    }

    public function uploading_a_photo_requires_a_title(): void{
        $response = $this->post(route('photos.store'), [
            'title' => '', // Empty title
            'image' => 'not-a-real-image.jpg' // Placeholder
        ]);
        $response->assertSessionHasErrors('title');
    }
}
```
To run our tests, use the Artisan command:
```shell
# Run all tests in the project
php artisan test

# Run only the tests in a specific file
php artisan test --filter GalleryTest

# Run only tests in the 'Unit' group
php artisan test --testsuite=Unit
```
Laravel will provide a clean, colorful report of passes, failures, or errors. Aim for high test coverage to catch issues automatically.
### Advanced Testing with Laravel Dusk
For more comprehensive testing, especially user interactions (e.g., clicking buttons, filling forms with JavaScript), we use Laravel Dusk. Dusk provides an expressive, easy-to-use browser automation and testing API. It automates a real Chrome browser to simulate user behavior, ensuring the app works end-to-end.
#### Setting Up Dusk
We first install it
```shell
composer require --dev laravel/dusk
php artisan dusk:install
```
Now. Dusk is ready. and created a `tests/Browser` directory for our Dusk tests. It automatically manages its own ChromeDriver.   
Now we create our Dusk test using:
```shell
php artisan dusk:make UploadPhotoTest
```
Now let's create test to simulate user uploading image, our test will composed of the following.
- `DuskTestCase`: This class starts a full test server and launches a real Chrome browser.
- `$this->browse()`: All Dusk tests are wrapped in this method, which provides a `$browser` instance.
    - `->visit()` navigates to a page.
    - `->type('name', 'value')` fills a form field.
    - `->attach('name', 'path/to/file')` uploads a file.
    - `->press('Button Text')` clicks a button or link.
    - `->assertPathIs()` and `->assertSee()` check the result.
- `storage_path()`: We use a real test image file, which we can store in our project and reference with a helper function.
```php
// tests/Browser/UploadPhotoTest.php
namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User; // If you need to log in

class UploadPhotoTest extends DuskTestCase{
    use DatabaseMigrations; 

    public function a_user_can_upload_a_photo(): void{
        $this->browse(function (Browser $browser) {
            $browser->visit(route('photos.create'))
                    ->type('title', 'Dusk Test Photo') 
                    ->attach('image', storage_path('app/public/test.jpg')) 
                    ->press('Upload')
                    ->assertPathIs('/gallery')
                    ->assertSee('Dusk Test Photo');
        });
    }
}
```
Finally we can run our Dusk tests, by using the `dusk` Artisan command:
```shell
php artisan dusk
```
