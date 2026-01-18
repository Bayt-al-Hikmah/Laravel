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
**`Http\Resources\TaskResource.php`**
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
        'avatar' => $this->avatar,
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

    public function rules(): array{
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
        return $request->user();
    }

    public function rules(): array{
        return [
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
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
        return $request->user();
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
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreateTask extends FormRequest{

    public function authorize(): bool{
        return $request->user();
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
        return $request->user();
    }

    public function rules(): array{
        return [
            'state' => 'required|string',
        ];
    }
}
```
### Creating The Controllers
