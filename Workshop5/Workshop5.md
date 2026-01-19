## Objectives
- Understanding the shift from Server-Side Rendering to APIs.
- Building REST API with Laravel
## Shifting from Server-Side Rendering to APIs.
### Introduction
In our past workshops, we used server-side rendering. With this approach, each request returned an entire HTML page. The problem is that every time we triggered an action in the web app or navigated to a new URL, the server re-rendered a whole page. This means we ended up downloading the full HTML again, even when only a small part of the page had changed.  
This is referred to as a “Hotwire-like” approach, and while it works, it slow down the application. Often, we only need to retrieve a small piece of data and update a specific section of the page, rather than reloading everything.

To fix this, we can use an API to send and receive small chunks of data and update just the required parts of the interface.
### API
API (Application Programming Interface) is a layer that we add to our web app to connect the frontend with the backend. Our app uses the API to retrieve and send data to the server. The backend receives the data, saves the results, processes whatever is needed, and then returns the updated information to the frontend.   
APIs make it easier to extend our application and make it available on other platforms. For example, if we want to build a mobile application, we only need to create the user interface and connect it to our web server using the API. The same backend logic and data can be reused without any changes.

![](./api.png)


### Javascript Role
To use the API in our web application, we rely on JavaScript.  
JavaScript handles communication with the server by fetching data from the API and then dynamically updating the DOM to reflect that data, Instead of submitting a full form and reloading the page, we can let the user type in an input field, click a button, and then:
1. Catch the click event with JavaScript
2. Send a request to the API    
3. Receive the response from the server
4. Update the DOM using the data from the response


This way, only the necessary part of the page changes, and our app becomes much faster and smoother.
### REST API Architecture
There are many patterns to design APIs for our web apps, but the most common and beginner friendly one is the REST API.  
REST stands for Representational State Transfer. It is named this way because the server sends a representation of the requested resource usually as JSON, and the client is responsible for handling the state of the application on its side. 
### REST Main Properties
REST APIs are defined by several mandatory constraints that help achieve scalability, simplicity, and performance in a web service.
#### Stateless
Each request sent to the server must contain all the information needed to process it. The server does not store any information about previous requests. 
#### Client–Server Separation
The frontend and backend are separated, The frontend focuses only on the user interface and user experience, while the backend handles data storage and business logic. 
#### URLs Identify Resources
REST treats everything as a resource (users, tasks, posts, products, etc.), Each resource is identified by a clear and meaningful URL, for example:
- `/tasks`
- `/users/1`
#### Use of Standard HTTP Methods
REST relies on standard HTTP methods to describe actions instead of custom commands:
- ``GET`` Retrieve data
- ``POST`` Create new data
- ``PUT / PATCH`` Update existing data
- ``DELETE`` Remove data

By following these conventions, REST APIs remain predictable, easy to understand, and consistent across different applications.
## Building REST API with Laravel
Now that we understand how REST APIs work, we will apply these concepts by building a Task Management REST API.  
The API will be responsible for registering users, authenticating logins, updating user profiles, and displaying, editing, and deleting tasks associated with each user.
### Setting Our Envirenment
We start by creating new Laravel project using
```
composer create-project laravel/laravel workshop5
cd workshop5
```
### Installing API Support
Next we need to install the API support, we run the following command:
```shell
php artisan install:api
```
This command will set the API route, it will be in `routes/api.php`, and it will also install Laravel Sanctum which provide us with the basic setup for authentication.
### Creating The Migration File
We need two core database tables: the User table and the Task table.   
The User table represents application users and stores their basic information such as username, password, email, and avatar. The Task model represents tasks created by users, including details like task name, description, creation time, and current state (active or done).

We also add a one-to-many relationship between users and tasks:
- A user can have many tasks
- Each task belongs to exactly one user

By default php create base user table for us, we open the migration file and add to the ``users`` table the avatar column.  
**`database/migrations/XXXX_XX_XX_XXXXXX_create_users_table.php`**
```php 
// inside the up function we add edit the users table
Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable(); // we add this
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
```
After that we create migration file for the Task table.
```php
php artisan make:model Task -m
```
We edit our table definition so it include task name, state and foreign key that refer to the user.   
**`database/migrations/XXXX_XX_XX_XXXXXX_create_tasks_table.php`**
```php
Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('state')->nullable()->default('active');
            $table->foreignId('user_id') 
                    ->constrained()       
                    ->cascadeOnDelete();   
            $table->timestamps();
        });
```
### Editing the Models
Lets start by editing our User model and add avatar to the `$fillable` array, and create `tasks` method to define that user can have many taks and we can access the user tasks by using `->tasks`  
**``app/Models/User.php``**
```php
protected $fillable = [
        'name',
        'email',
        'password',
        'avatar', // we add this
    ];
    public function tasks(){
        return $this->hasMany(Task::class);
    }
```
Next we edit the `Task` model and we set the ``$fillable`` fields, and we define `user` method where we declare that each task belong  to one specific User. and we can access to that user using `$task->user`.    
**``app/Models/Task.php``**
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name',
        'state',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
```
### Creating Resources
Resources serve as a transformation layer that sits between our Eloquent models and the JSON responses that our API returns to users. Instead of returning a raw database object directly to the user which might expose sensitive data or look messy we pass it through a Resource to format it exactly how we want.  
Lets create resouce for Task and User model
```shell
php artisan make:resource TaskResource
php artisan make:resource UserResource
```
We start by editing the task resource.  
**`Http\Resources\TaskResource.php`**
```php
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource{

    public function toArray(Request $request): array{
        return [
        'id' => $this->id,
        'name' => $this->name,
        'state' => $this->state,
        'created_at' => $this->created_at,
        'user_id' => $this->user_id,
    ];
    }
}
```
After that we define the User Resouces.    
**`Http\Resources\UserResource.php`**
```php
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource{
    public function toArray(Request $request): array
    {
        return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'avatar' => 'storage/'. $this->avatar,
    ];
    }
}
```
### Creating The Request Validator
We need to create Request validator to validate the data sent by the users when they register, login, add task or update their profile.    
We will create six request validator 
- `LoginRequest`: validate login request.
- `RegisterRequest`: validate register request.
- `UpdateProfile`: validate updating profile request.
- `UpdatePassword`: validate updating password request.
- `CreateTask`: validate creating task request
- `UpdateTask`: validate updating task request.
```shell
php artisan make:request LoginRequest
php artisan make:request RegisterRequest
php artisan make:request UpdateProfile
php artisan make:request UpdatePassword
php artisan make:request CreateTask
php artisan make:request UpdateTask
```
We start with `LoginRequest` for the `authorize` method we return true as we allow anyone to try to login, and for the rules we need email and passord we set them to be required and string.  
**`Http/Requests/LoginRequest.php`**
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest{
   
    public function authorize(): bool{
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|max:255',
            'password' => 'required|string',
        ];
    }
}
```
We move to the `RegisterRequest` same as before we set `authorize` to return true, for the validation rules we set the name, email, password and avatar.   
**`Http/Requests/RegisterRequest.php`**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest{

    public function authorize(): bool{
        return true;
    }
    
    public function rules(): array{
        return [
            'name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image'
        ];
    }
}
```
Next, we go to `UpdateProfile` here only logged-in user can edit their profile so we edit `authorize` to return `$request->user()` which evaluated as true if user is logged in else it return false, After that we set rules to validate the email, username and avatar.   
**`Http/Requests/UpdateProfile.php`**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest{

    public function authorize(): bool{
        return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'name' => 'required|string',
            'email' => 'required|email',
            'avatar' => 'nullable|image'
        ];
    }
}
```
The `UpdatePassword` should also allow only logged-in user to edit their password, and for rules we return only password.    
**`Http/Requests/UpdatePassword.php`**
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePassword extends FormRequest{

    public function authorize(): bool{
        return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'password' => 'required|string|min:6',
        ];
    }
}
```
Finally we set the ``CreateTask`` and ``UpdateTask`` Request validator, both require user to be logged-in to perform task, and for rules ``CreateTask`` need task name only while the ``UpdateTask`` need task state.  
**`Http/Requests/CreateTask.php`**
```php
<?php
n<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreateTask extends FormRequest{

    public function authorize(): bool{
        return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'name' => 'required|string',
        ];
    }
}
```
**`Http/Requests/UpdateTask.php`**
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTask extends FormRequest{

    public function authorize(): bool{
       return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'state' => 'required|string',
        ];
    }
}

```
### Creating The Controllers
We defined our Models and Resources now we need to create the controllers, to handel our app logic, we will need three controllers 
- `authcontroller`: Handel Login and Register logic
- `usercontroller`: Handel Updating User Profile, And User Password Logic
- `taskcontroller`: Handel Adding, Updating and Deleting Tasks
```shell
php artisan make:controller authcontroller
php artisan make:controller usercontroller
php artisan make:controller taskcontroller
``` 
We start with `authcontroller` it should have two method. `login` to log user in and `register` to register and create new user.
```php
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class authcontroller extends Controller {
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('uploads', 'public');
            $validated['avatar'] = $path;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $validated['avatar'] ?? null,
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(LoginRequest $request){
        $validated = $request->validated();

        if (Auth::attempt($validated)) {
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
        ], 200);
    }

    return response()->json([
        'message' => 'The provided credentials do not match our records.',
    ], 401);
    }
}
```
After that we move to the `taskcontroller` where we define four methods.
- `index` return all user task 
- `store` create new task
- `update` edit a specific task state
- `delete` remove a specific task state

```php
<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateTask;
use App\Http\Requests\CreateTask;
use App\Http\Controllers\Controller;

class taskcontroller extends Controller{
    public function index(Request $request){
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return TaskResource::collection($request->user()->tasks);
    }

    public function store(CreateTask $request){
        $validated = $request->validated();
        $task = $request->user()->tasks()->create($validated);
        return new TaskResource($task);
    }

    public function update(UpdateTask $request, $task_id){
        $validated = $request->validated();
        $task = Task::findOrFail($task_id);
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update(['state' =>$validated['state']]);
         return  response()->json(['message' => 'Task Updated'], 201);
    }

    public function destroy(Request $request,  $task_id){
        $task = Task::findOrFail($task_id);
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
```
Finally we set the `usercontroller` it got three methods.
- `index` return the user information.
- `update_profile` update user profile.
- `update_password` update user password.

```php
<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use Illuminate\Support\Facades\Hash;

class usercontroller extends Controller{
    public function index(Request $request){
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return new UserResource($request->user());
    }

    public function update_profile(UpdateProfile $request){
        $validated = $request->validated();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('uploads', 'public');
            $validated['avatar'] = $path;
        }
        $request->user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $request->user()->avatar,
        ]);
        return response()->json(['message' => 'Profile Updated'], 201);
    }

    public function update_password(UpdatePassword $request){
    $validated = $request->validated();
    $password = Hash::make($validated['password']);

    $request->user()->update([
        'password' => Hash::make($password),
    ]);

    return response()->json(['message' => 'Password updated successfully']);
    }
}
```
### Configuring The routes
Finally we add our controllers to the ``routes/api.php`` route file. so our app can serve them.
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authcontroller;
use App\Http\Controllers\taskcontroller;
use App\Http\Controllers\usercontroller;

Route::post('/auth/register', [authcontroller::class, 'register']);
Route::post('/auth/login', [authcontroller::class, 'login']);

Route::get('/tasks', [taskcontroller::class, 'index']);
Route::post('/tasks', [taskcontroller::class, 'store']);
Route::put('/tasks/{task_id}', [taskcontroller::class, 'update']);
Route::delete('/tasks/{task_id}', [taskcontroller::class, 'destroy']);

Route::get('/user', [usercontroller::class, 'index']);
Route::patch('/user', [usercontroller::class, 'update_password']);
Route::put('/user', [usercontroller::class, 'update_profile']);
```
And we set the `routes/web.php` file to serve the view that represent our app.
```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
```
Adding restfull configuration to our `bootstrap/app.php`, we set the configuration for `restfull` api.
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi(); // we add this
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```
### Creating The Interface
Now that our API is fully functional, we need a user interface to interact with it. Instead of the server rendering HTML pages for every route, we will serve a single HTML file (Single Page Application approach) and use JavaScript to fetch data from our API and update the DOM dynamically.

#### The View and Style
We created a simple interface with two main sections: a Login section and a Dashboard section. Initially, the dashboard is hidden. After the user successfully logs in, the login section will be hidden, and the dashboard will be displayed.

We can find the HTML template and styling files inside the ``materials`` folder. The ``index.blade.php`` file should be moved to the ``resources/views`` folder and the ``style.css`` file should be moved to the ``public/css`` folder.

#### Client-Side Logic (JavaScript)
This is the most important part. The JavaScript file acts as the bridge between HTML events (such as clicks) and the Laravel REST API.

The code listens for form submissions and button clicks, then makes API calls using fetch to the corresponding endpoints. For example, when a user logs in, it sends a POST request to ``/api/auth/login``, stores the session, and updates the view to display the user’s tasks. Similarly, task actions like creating, updating, or deleting a task are sent to the ``/api/tasks`` endpoints, and the page updates dynamically without reloading.  
The file is currently in the materials folder. We should move it to the ``public/js`` folder so it can be served as a static asset by Laravel.

#### Ramark 
When sending request to retrive data we need to add specific header to our ``fetch`` function.  
```js
headers:{
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // this most exist
}
```
And for our view we must add the following meta element to load the csrf token. 
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```
### Running The server
Finally we can run our migrations and start our server
```shell
php artisan migrate:fresh
php artisan storage:link
php artisan serve
```
### Token-Based Authentication in Laravel
In the current Task Manager API, we use session to manage authentication. This approach is effective for traditional web applications where the server and client are closely tied, and the browser handles session cookies automatically.    
However, modern APIs often require authentication that is stateless and can be easily used by various clients (mobile apps, other servers, JavaScript frontends). This is where Token-Based Authentication comes in.
#### How Tokens Work with Sanctum
Instead of the server storing a session ID in a file or database to match a cookie, the server issues a Plain Text Token.
1. **Client Logs In:** The user sends credentials to `/api/login`.
2. **Server Generates Token:** Laravel verifies the user and creates a Personal Access.
3. **Client Stores Token:** The frontend saves this string.
4. **API Access:** For every request to protected routes (like `/api/tasks`), the client sends the token in the `Authorization` header as a `Bearer` token.
5. **Server Verification:** Sanctum validates the token and attaches the authenticated user to the request.
#### Implementing Token Authentication
First we start by editing our User model
```php
use Laravel\Sanctum\HasApiTokens; we add this
// other code
use HasFactory, HasApiTokens /* add this*/, Notifiable; // inside the User class we add HasApiTokens
// other code
```
adding this will allow use to call ``$user->createToken('token-name')`` in our controllers. to generates token. and when user sends a request to our API with that token in the header, Laravel uses this trait to look up the token in the database and figure out which user it belongs to.

After that we edit our ``usercontroller`` we edit the log in check we use `Hash` and we generate and return token instead of saving user on session, and we create logout function
```php
    public function login(LoginRequest $request){
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
            'message' => 'Invalid credentials',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
        ], 200);
    }

// this for logging out
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out. Token revoked.'
        ], 200);
    }
```
Finally we edit the ``api.php`` file we use `Route::middleware('auth:sanctum')->group` middleware for the routes that need authentication
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authcontroller;
use App\Http\Controllers\taskcontroller;
use App\Http\Controllers\usercontroller;

Route::post('/auth/register', [authcontroller::class, 'register']);
Route::post('/auth/login', [authcontroller::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [taskcontroller::class, 'index']);
    Route::post('/tasks', [taskcontroller::class, 'store']);
    Route::put('/tasks/{task_id}', [taskcontroller::class, 'update']);
    Route::delete('/tasks/{task_id}', [taskcontroller::class, 'destroy']);

    Route::get('/user', [usercontroller::class, 'index']);
    Route::patch('/user', [usercontroller::class, 'update_password']);
    Route::put('/user', [usercontroller::class, 'update_profile']);
    Route::get('/auth/logout', [authcontroller::class, 'logout']);
});
```
#### Edditing bootstrap
We edit the ``bootstrap/app.js``, and remove the middlewar so our app become fully stateless
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

```
#### Editing the Javascript
Now we update our JavaScript to work with JWT authentication. When a user logs in, the backend returns a token, which we store in the browser using:
```js
localStorage.setItem('token', data.access_token);
```
For every subsequent API request, we need to include this token in the Authorization header so the backend can verify the user. This is done by adding:
```js
'Accept': 'application/json',
'Authorization': `Bearer ${localStorage.getItem('token')}` 
```
This ensures that only authenticated users can access protected endpoints.
### API Rate Limiting
As our API gains more users, we need to protect it from abuse, excessive load, and denial-of-service (DoS) attacks. Rate Limiting is the practice of restricting the number of API requests a user (or IP address) can make within a specific time window.
#### Implementing Rate Limiting
To protect our Laravel application from abuse and excessive requests, we implement rate limiting. Rate limiting helps prevent brute-force attacks, reduces server load, and improves overall API reliability.  

#### Installing Redis

Redis (Remote Dictionary Server) is a very fast, in-memory data store. It is commonly used for caching, sessions, queues, and rate limiting. Because Redis stores data in memory, it is significantly faster than traditional databases, making it ideal for tracking API requests in real time.  
Redis is used with rate limiting plugin to persist rate-limit data. This allows rate limits to remain consistent even if the server restarts or runs across multiple instances.  
We install it as following

- Ubuntu / Debian:

```
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

- macOS (Homebrew):

```
brew install redis
brew services start redis
```

- Windows Redis is not officially supported on Windows, but we can use **Redis for Windows** provided by the community [Redis for Windows](https://github.com/tporadowski/redis/releases).

After that we install the redis package

```
composer require predis/predis
```
#### Editing The .env File
Next, we update the .env file to tell Laravel to use Redis for cache and throttling:
```
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```
#### Configure Rate Limiters
We define our limits in `app/Providers/AppServiceProvider.php` using the `RateLimiter` facade.
```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
}
```
- **perMinute(60):** The max number of requests allowed.
- **by():** Identifies the user by their ID or IP address.

#### Applying and Overriding Rate Limits
To apply the limit, we add the `throttle` middleware to the middleware list in our routes in `routes/api.php`.
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authcontroller;
use App\Http\Controllers\taskcontroller;
use App\Http\Controllers\usercontroller;

Route::post('/auth/register', [authcontroller::class, 'register']);
Route::post('/auth/login', [authcontroller::class, 'login']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/tasks', [taskcontroller::class, 'index']);
    Route::post('/tasks', [taskcontroller::class, 'store']);
    Route::put('/tasks/{task_id}', [taskcontroller::class, 'update']);
    Route::delete('/tasks/{task_id}', [taskcontroller::class, 'destroy']);

    Route::get('/user', [usercontroller::class, 'index']);
    Route::patch('/user', [usercontroller::class, 'update_password']);
    Route::put('/user', [usercontroller::class, 'update_profile']);
    Route::get('/auth/logout', [authcontroller::class, 'logout']);
});
```
We can define rate limit on the routes middleware level by using `throttle:max_attempts,decay_minutes`. in the  middleware function

```php
Route::middleware('throttle:1000,1')->group(function () {
    Route::post('/auth/register', [authcontroller::class, 'register']);
    Route::post('/auth/login', [authcontroller::class, 'login']);
});

```
To skip rate limiting for a specific route, we simply don't  apply the `throttle` middleware to that route definition.

### Query Parameters and Pagination in Laravel

#### Query Parameters
Sometimes we need to apply filters to our data for example, allowing a user to search for a task by name. In Laravel, we can access these parameters using the `request()` helper or the `Request` object.    
To filter tasks by a query parameter named `name` (e.g., `/tasks?name=clean`), you would use:
```php
$name = request()->query('name');
$tasks = Task::where('name', 'like', "%$name%")->get();
```
#### Pagination
Pagination divides data into manageable chunks instead of returning thousands of records at once. This significantly improves performance and reduces memory usage.   
In Laravel, pagination is incredibly simple because the Eloquent builder handles the math and the SQL "limit/offset" logic for you.
#### Basic Pagination (Length Aware)
We can use `paginate(10)` to return 10 items from the database. and devide our data into chunks of pages of 10 elements.
```php
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; // add this to import

// Inside your Controller
public function index(Request $request){
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return TaskResource::collection($request->user()->tasks()->paginate(10));
    }
```

**The JSON response will be structured as follows:**
```json
{
    "data": [
        { "id": 1, "name": "Task 1" },
	    { "id": 2, "name": "Task 2" }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/tasks?page=1",
        "last": "http://127.0.0.1:8000/api/tasks?page=2",
        "prev": null,
        "next": "http://127.0.0.1:8000/api/tasks?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 2,
        "links": [
            {
            "url": null,"label": "&laquo; Previous","page": null,"active": false
            },
            {
            "url": "http://127.0.0.1:8000/api/tasks?page=1","label": "1","page": 1,"active": true
            },
            {
            "url": "http://127.0.0.1:8000/api/tasks?page=2","label": "2","page": 2,"active": false
            },
            {
            "url": "http://127.0.0.1:8000/api/tasks?page=2","label": "Next &raquo;","page": 2,"active": false
            }
        ],
        "path": "http://127.0.0.1:8000/api/tasks",
        "per_page": 10,
        "to": 10,
        "total": 12
    }
}
```
We can retrive the next chunk of data using
```js
await fetch(`${API_BASE}/tasks?page=2`, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`  },
    });
```
#### Simple Pagination
If we don't need to show the exact page numbers , we can use `simplePaginate`. This is more efficient because it doesn't execute a "count" query to find out the total number of pages.
```php
return TaskResource::collection($request->user()->tasks()->simplePaginate(10));
```
The JSON will only contain `next_page_url` and `prev_page_url`, but not the `total` or `last_page` count.
#### Customizing Pagination
We can customize the parameter name (default is `page`) or the per-page limit dynamically:
```php
// Changing the parameter name to 'p' instead of 'page'
return TaskResource::collection($request->user()->tasks()->paginate(10, ['*'], 'p'));

// Allow the client to define the limit via ?limit=50
$limit = request()->query('limit', 10); // 10 is the default
$tasks = TaskResource::collection($request->user()->tasks()->paginate($limit));
```

