## Objectives
- Working with Databases and **Eloquent** Models
- Authentication and Session Management
- Working with **Artisan Tinker**
- Managing the Admin Panel 

## Databases and Eloquent Models
In our previous sessions, we built apps that displayed dynamic content. For example, in our `feedback` app, we accepted user input and stored it in the session.

This approach has a serious limitation: data stored in a session is volatile and temporary. As soon as the user's session expires or is cleared, all that information is lost.

To build real, reliable web applications, we need a way to store data permanently. This is where databases come in.
### Database
A database is a specialized, structured system for storing, managing, and retrieving information efficiently. Instead of vanishing when the user's session ends, a database saves our data permanently on disk, ensuring it's still there the next time our application starts.

Databases are essential because they:
- Store data persistently, it doesn't disappear when the app restarts.
- Organize data in a structured, reliable format.
- Allow us to query and filter data efficiently .
- Maintain data integrity and security, even when many users are reading and writing data at the same time.
### Types of Databases
Databases aren’t one-size-fits-all. They come in different types, depending on how they organize and manage data. The two main categories you’ll encounter are:
#### Relational Databases (SQL)
These are the most common type of database. They store data in tables made up of rows and columns, much like organized spreadsheets that can be linked together. Relational databases use a specialized language called SQL (Structured Query Language) to create, read, update, and delete data.

They’re ideal for applications where data relationships and structure are important.
- **Examples:** SQLite, PostgreSQL, MySQL, SQL Server.
#### Non-Relational Databases (NoSQL)
These databases are more flexible and store data in various formats such as documents (like JSON), key-value pairs, wide-column stores, or graphs. They’re often used for large-scale systems or unstructured data.
- **Examples:** MongoDB, Redis, Cassandra

For most web applications and especially for Laravel projects we use a relational database. Laravel is designed around the structured, table-based model, and its most powerful features are built to work seamlessly with it.
### Relational Databases Structure
A relational database stores data in tables. Each table represents a specific entity type (e.g., a `Customers` table or an `Orders` table). Each row represents a single record, and each column defines a property or field (e.g., `name`, `email`, `order_date`).
#### Primary Keys
To keep data organized, every table includes a primary key. A primary key is a special column (usually `id`) that uniquely identifies each record in a table.

- It prevents duplicate entries.    
- It allows the database to quickly find records.

**Example: Customers Table**

| **id (Primary Key)** | **name**    | **email**         |
| -------------------- | ----------- | ----------------- |
| 1                    | Alice Smith | alice@example.com |
| 2                    | Bob Johnson | bob@example.com   |

Here:
- Each row is one unique customer.
- The `id` column is the **primary key**.
### Foreign Keys and Relationships
Relational databases are powerful because they can define relationships between tables. This is done through foreign keys.

A foreign key is a column in one table that refers to the primary key of another table. This creates a connection between records.
### Common Types of Relationships
- One-to-One: Each record in Table A is linked to exactly one record in Table B.
    - **Example:** A `User` table and a `UserProfile` table.
- One-to-Many: A single record in one table can be linked to multiple records in another. This is the most common type.
    - **Example:** One `Customer` can have many `Orders`.    
- Many-to-Many: Multiple records in Table A can be linked to multiple records in Table B. This requires a third "junction" table to link them.
    - **Example:** One `Book` can have many `Authors`, and one `Author` can have many `Books`.
### Connecting Apps to Databases
Now that we understand what databases are, let’s talk about how our Laravel application can communicate with one.

In PHP, we can connect to a database using the built-in PDO (PHP Data Objects) extension. PDO provides a consistent way to talk to different databases (like `pdo_sqlite`, `pdo_mysql`, `pdo_pgsql`).

Once connected, we could write SQL queries directly inside our PHP code:
```php
// Connect to a database (or create it)
$pdo = new PDO("sqlite:example.db");

// Create a table
$pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT)");

// Insert data
$statement = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
$statement->execute(['Alice']);
```
This works, but there’s a big drawback:

We’re mixing raw SQL queries directly with PHP logic. This makes our code hard to maintain, debug, and scale. Even worse, if we decide to switch databases (say, from SQLite to PostgreSQL), we might need to rewrite our queries, since SQL syntax can vary slightly.

This approach doesn’t align with the Laravel philosophy of keeping code clean, expressive, and reusable. Luckily, Laravel gives us a much better solution: its built-in Object-Relational Mapper (ORM), called Eloquent.
### Understanding Eloquent (ORM) and Its Benefits
An Object-Relational Mapper (ORM) is a layer that bridges the gap between our application's objects (PHP classes) and the relational database's tables. It translates PHP operations into SQL queries automatically.

Here are the key benefits of using Eloquent:
- Abstraction and Portability: We define our database structure using PHP migration files and interact with it using PHP model classes. The ORM generates the appropriate SQL. If we ever switch databases, we only need to change a configuration setting in our `.env` file, not rewrite our queries.
- Improved Productivity and Readability: Instead of writing raw SQL strings, we work entirely in PHP.
    - **SQL:** `SELECT * FROM users WHERE email = 'alice@example.com'`
    - **Eloquent:** `User::where('email', 'alice@example.com')->get();`
- Data Integrity and Security: The ORM manages relationships and automatically escapes inputs, protecting our app from SQL injection attacks.
- Team Consistency: By using the same data access patterns (Eloquent), all developers on a team can easily read and extend each other’s code.
    
In short, Laravel’s Eloquent ORM lets us focus on our application’s logic rather than the low-level details of database management.
### Working with Eloquent Models and Migrations
In Laravel, the database structure is defined in two key places:
1. Migrations: These are PHP files in the `database/migrations` folder. They are like version control for our database. Each migration file contains instructions to `create` or `modify` a database table.
    
2. Models: These are PHP classes in the `app/Models` folder. Each model class represents a table, allowing us to interact with that table using PHP methods (e.g., `Todo::all()`).
### Creating Our App
Let’s create a new `todo_list` app to put our model knowledge into practice. This app will let us view tasks and add new ones.

First, let’s create a new Laravel project named `workshop3`, then `cd` into it:
```shell
composer create-project laravel/laravel workshop3
cd workshop3
```
#### Creating The Model 
After we create our new projects lets create the model where we represent our database schema. we can create both the model and its migration file with one single Artisan command.
```shell
# '-m' tells artisan to also create a migration file
php artisan make:model Todo -m
```
This command creates two files for us:
1. **Model:** `app/Models/Todo.php`
2. **Migration:** `database/migrations/xxxx_xx_xx_xxxxxx_create_todos_table.php`

Now, let's edit the **migration file** to define the structure of our tasks.   
**`database/migrations/..._create_todos_table.php`:**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Was CharField
            $table->text('description'); // Was TextField
            $table->boolean('done')->default(false); // Was BooleanField
            $table->timestamps(); // Handles created_at and updated_at
        });
    }
    // ... down method ...
};
```
In this migration file, we create a class that contains an `up` method. This method runs when we want to create a new table in the database. Inside the `up` method, we use Laravel’s Schema builder to define the table name and the columns it should have, Each `$table->` line adds a new field to the table.

Here’s what each field does:
- **`$table->id()`**: Creates an auto-incrementing `id` primary key.
- **`$table->string('title')`**: A text field (VARCHAR) for the task name.
- **`$table->text('description')`**: A longer text area for details.
- **`$table->boolean('done')->default(false)`**: A boolean to track completion.
- **`$table->timestamps()`**: Automatically creates `created_at` and `updated_at` columns.
    

After defining the schema, we run the **`migrate`** command to create the table in our database.
```shell
php artisan migrate
```
This applies the migration, creating the `todos` table.
#### Configuring the Model 
Now that our database table exists, we'll configure the `Todo` model. We need to tell Eloquent which fields are "fillable", safe to be mass-assigned from a form. 

Open **`app/Models/Todo.php`** and add the `$fillable` property:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    
    protected $fillable = [
        'title',
        'description',
    ];
}
```
We only list `title` and `description` because we don't want the user to be able to set the `done` status when creating a task.
#### Creating the Validation
Now lets create **Form Request** class to auto-validates incoming requests.

Let's create one:
```shell
php artisan make:request StoreTodoRequest
```
This creates a new file at **`app/Http/Requests/StoreTodoRequest.php`**. Let's add our rules:
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }
}
```
This form request will ensure the `title` and `description` are provided and are valid strings.
#### Creating the Controller 
Now let's define the Controller that will handle the logic, connecting our templates, validation, and database.
```shell
php artisan make:controller TodoController
```
Open **`app/Http/Controllers/TodoController.php`** and add the following methods:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Todo; 
use App\Http\Requests\StoreTodoRequest; 

class TodoController extends Controller
{

    public function index()
    {
        $tasks = Todo::all(); 
        return view('todo_list.tasks', ['tasks' => $tasks]);
    }


    public function create()
    {
        return view('todo_list.add_task');
    }


    public function store(StoreTodoRequest $request)
    {
        $validatedData = $request->validated();
        Todo::create($validatedData);
        return redirect()->route('task_list');
    }
}
```
Our controller have three methods:   
- ``index``: this method get all the tasks from Todo model and pass them to our templat
- ``create``: this method return the form template where user can submit new task
- `store`: finally this method handel submitting the form, it use the `StoreTodoRequest`to validate the form data, then
	- it get the inputs fields values 
	- it use ``Todo:create`` to create new record in our table using the form data
	- finally it redirect the user to ``task_list`` endpoint to see all submitted tasks
#### Creating the Templates 
Now lets create templates for our app, we start by creating the base layout template.  

**`resources/views/layouts/base.blade.php`**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My To-Do List</title>
     <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>My Website</h1>
    <main>
        @yield('content')
    </main>
</body>
</html>
```
After that we create the template that will display our all the submitted tasks.

**`resources/views/todo_list/tasks.blade.php`**
```php
@extends('layouts.base')
@section('content')

<div class="container">
	<h1>My To-Do List</h1>
	<a href="{{ route('add_task') }}" class="btn">Add New Task</a>
	
	<div class="task-list">
		@forelse ($tasks as $task)
			<div class="@if ($task->done) done @endif">
				<strong>{{ $task->title }}</strong><br>
				{{ $task->description }}
			</div>
		@empty
			<div>No tasks added yet. Start by creating one!</div>
		@endforelse
	</div>
</div>
@endsection
```
Finally we create the template that display the add task form.   

**`resources/views/todo_list/add_task.blade.php`**
```php
@extends('layouts.base')
@section('content')

<div class="container">
	<h1>Add a New Task</h1>
	<form method="POST" action="{{ route('add_task') }}">
		@csrf <div>
			<label for="title">Title</label>
			<input type="text" name="title" id="title" value="{{ old('title') }}">
			@error('title')
				<div class="error-message">{{ $message }}</div>
			@enderror
		</div>
		
		<div>
			<label for="description">Description</label>
			<textarea name="description" id="description">{{ old('description') }}</textarea>
			@error('description')
				<div class="error-message">{{ $message }}</div>
			@enderror
		</div>

		<button type="submit" class="btn">Save Task</button>
	</form>
	<a href="{{ route('task_list') }}" class="btn back">Back to List</a>
</div>
@endsection
```
#### Adding the Stylesheet
We’ll use the stylesheet from the `materials` folder. Create a file called **`style.css`** inside `public/css/` and paste the styles from the `material/styles.css` file. The `asset()` helper in our Blade files will now find it.

#### Setting The Routes
Finally, we connect our controller methods to URLs in the main `routes/web.php` file.

**`routes/web.php`:**
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/todo', [TodoController::class, 'index'])->name('task_list');
Route::get('/todo/add', [TodoController::class, 'create'])->name('add_task');
Route::post('/todo/add', [TodoController::class, 'store']); 
```
Now, when we visit **`http://127.0.0.1:8000/todo`**, we will see our task list, and at **`http://127.0.0.1:8000/todo/add`**, we will be able to add new tasks with full validation
## Authentication and Session Management
Authentication is the process of verifying the identity of a user, device, or system. In web applications, it ensures that only authorized individuals can interact with certain parts of the app, such as viewing personal information or performing actions.

Without authentication, anyone could access or modify data, leading to security risks. Authentication typically involves credentials like usernames and passwords.
### Authentication in Laravel
Laravel provides a robust, built-in authentication system. However, instead of being a pre-installed set of components, Laravel offers optional Starter Kits (like **Breeze** and **Jetstream**) that scaffold a complete authentication system for us in minutes.

This system is flexible and handles user models, login/logout/registration controllers and views, password hashing, and permissions.

Laravel's authentication is based on sessions, which allow the server to remember users across requests. Since HTTP is stateless, sessions provide a way to maintain state, like keeping a user logged in.
#### Sessions in Laravel
How does a web application "remember" us between clicks? The web itself is stateless. To solve this, Laravel uses sessions. A session is a mechanism that allows Laravel to store user-specific data between requests essentially acting as temporary memory for the user.

For example, once a user logs in, Laravel stores their user ID in a session so the app can recognize them on every subsequent page.

Laravel handles session management seamlessly. Here are the core components:

#### Session Middleware
In modern Laravel , the session middleware (`\Illuminate\Session\Middleware\StartSession::class`) is enabled by default for all "web" requests, This middleware automatically starts, loads, and saves session data for each incoming web request.    
#### Session Drivers
The "driver" determines where our session data is actually stored. This is set in our `.env` file with the `SESSION_DRIVER` key.
- **`file`**: Stores session data in files within `storage/framework/sessions`. This is simple and works well for development.
- **`database`**: Stores sessions in a database table. This is a robust option for multi-server production environments. to use this, you must first run `php artisan session:table` (to create the migration) and then `php artisan migrate`.
- **`redis`** or **`memcached`**: Uses a fast, in-memory cache. This is the highest-performance option, ideal for high-traffic applications.
- **`cookie`**: Stores the entire session payload in a secure, encrypted cookie. This is stateless for our server but has cookie size limitations.
#### Authentication & Security
We don't need to manually check session data to see if a user is logged in. Laravel's authentication system does this for us.
The `\Illuminate\Auth\Middleware\Authenticate::class` middleware (also part of the default web stack) automatically:
1. Checks the session for an authenticated user's ID.
2. Fetches that user from the database.
3. Attaches the `User` object to the request, making it available everywhere (e.g., via `Auth::user()` or `request()->user()`).
### Updating Our To-Do List App
Our current to-do app has a major issue: all users share the same task list, and anyone can submit a task this not good because users can't keep track on their own tasks and hacker can explot this to subit random stuffs without leaving trace, To make our app secure, we need to update it so that each user only sees and manages their own tasks.    
To achieve this, we’ll:
1. Install a Starter Kit (Laravel Breeze) to handle registration, login, and logout.
2. Update the `Todo` model and migration to include a `user_id` reference.
3. Modify our routes and controller so that each user only interacts with their personal to-do list.
#### Adding User Authentication 
Before we restrict tasks, we need users to be able to register, log in, and log out. Instead of manually creating forms and controllers, the idiomatic Laravel way is to use a starter kit. We'll use Breeze.

We first Install Breeze using Composer:
```shell
composer require laravel/breeze --dev
```
Next, run the `breeze:install` command. This is what adds all the routes, controllers, and view files to your project.
```shell
php artisan breeze:install
#When prompted, choose "Blade" then 0 for dark mode and 1 for test unit (we will work test more in next workshop)
```
After that we install the front-end dependencies and build our assets:
```shell
npm install
npm run build
```
Finally we run the migration to updates our database with table that `breeze` use
```shell
php artisan migrate
```
Now, if we run our application, we can create an account, log in, and log out. When a user logs in or registers, they are automatically redirected to the dashboard. All of this functionality is generated for us by Breeze.

Breeze automatically provides:
- **Controllers** in `app/Http/Controllers/Auth/` for authentication actions like login and registration.
- **Views** (Blade templates) in `resources/views/auth/` for the login, registration, and related pages.
- **Routes** in the `routes/auth.php` file to handle authentication endpoints.
### Update The todo_list Feature
#### Updating the Todo Model
ow that authentication is set up, let’s update our `todo_list` feature. The first step is to link each task to a specific user. Since we cannot modify the original `todos` migration file after it has already been executed Laravel will not run it again we need to create a new migration that adds the `user_id` column
```shell
php artisan make:migration add_user_id_to_todos_table --table=todos
```
The `--table=todos` option tells Laravel that this migration will modify the existing `todos` table instead of creating a new one. This way, Laravel knows to add new columns to the current table rather than generate a fresh schema.   
Now lets edit the new migration file and add to it the for foreign key to users.
```php
 <?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('todos', function (Blueprint $table) {
               
                $table->foreignId('user_id') 
                      ->constrained()       
                      ->cascadeOnDelete();   
            });
        }
    };
```
In this migration, we are updating the existing `todos` table by adding a new `user_id` column. This column will act as a foreign key, linking each task to the user who created it. The `foreignId('user_id')` method creates the column, and `constrained()` automatically sets up the foreign key relationship with the `users` table, assuming the default `id` primary key. Finally, `cascadeOnDelete()` ensures that if a user is deleted from the system, all of their associated tasks will be removed as well. This allows us to properly connect todos to specific users and maintain clean, consistent data in our database.

Finally we need to update the `Todo` model to define the relationship between a task and its owner. We add a `user()` method inside the `Todo` model that returns `$this->belongsTo(User::class)`. This tells Laravel that each todo item belongs to a specific user. With this in place, we can easily access the user who created a task by calling `$todo->user`, and Eloquent will automatically handle the relationship for us.

**``app/Models/Todo.php``**
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class Todo extends Model
{
	protected $fillable = ['title', 'description'];

	// Add this method to define the relationship
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
```
#### Updating the Todo Model
Now that each todo is linked to a user, we need to define the opposite side of the relationship inside the `User` model. Since a single user can have many todo items, we add a `tasks()` method that returns `$this->hasMany(Todo::class)`. This is the Laravel equivalent of Django’s `related_name="tasks"`. With this method in place, we can easily retrieve all tasks that belong to a specific user by calling `$user->tasks`. Laravel will automatically handle the relationship and fetch the associated todos from the database.

**``app/Models/User.php``**
```php
 <?php
    namespace App\Models;
    // ...
    use Illuminate\Database\Eloquent\Relations\HasMany; // Import this
    
    class User extends Authenticatable
    {
        // ... other User model code ...
    
        // Add this method
        public function tasks(): HasMany
        {
            return $this->hasMany(Todo::class);
        }
    }
```
### Updating the Controllers
The final thing to do now is editing our `TodoController` to only show the logged-in user's tasks.

**`app/Http/Controllers/TodoController.php`**
```php
<?php
namespace App\Http\Controllers;

use App\Models\Todo;
use App\Http\Requests\StoreTodoRequest;
use Illuminate\Http\Request; // We need this for the auth() helper

class TodoController extends Controller
{

	public function index(Request $request)
	{
		$tasks = $request->user()->tasks()->get();
		return view('todo_list.tasks', ['tasks' => $tasks]);
	}


	public function create()
	{
		return view('todo_list.add_task');
	}

	public function store(StoreTodoRequest $request)
	{
		$request->user()->tasks()->create($request->validated());

		return redirect()->route('task_list');
	}
}
```
We updated the `index` method so that it only returns tasks that belong to the currently authenticated user. Instead of fetching all todos from the database, we call `$request->user()->tasks()->get()`. This uses the relationship we defined earlier to automatically retrieve only the tasks associated with the logged-in user. We then pass those tasks to the view so each user only sees their own list.   

We also updated the `store` method to attach new tasks to the authenticated user. Instead of manually adding a `user_id`, we use `$request->user()->tasks()->create($request->validated())`. This tells Laravel to create the task through the user's relationship, which automatically fills in the correct `user_id`. It also ensures the request data is validated before saving. After storing the new task, we redirect the user back to the task list page.
### Securing Routes
Finally now lets apply the `auth` middleware to our `todo` routes. We do that in the **`routes/web.php`** file. Breeze has already created a middleware group for us. We just need to move our `todo` routes inside it.

**`routes/web.php`**
```php
<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController; // Make sure this is imported
use Illuminate\Support\Facades\Route;

// ...

Route::get('/dashboard', function () {
	return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// This is the group we want!
Route::middleware('auth')->group(function () {
	Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

	// --- ADD OUR TODO ROUTES IN HERE ---
	Route::get('/todo', [TodoController::class, 'index'])->name('task_list');
	Route::get('/todo/add', [TodoController::class, 'create'])->name('add_task');
	Route::post('/todo/add', [TodoController::class, 'store']);
});

require __DIR__.'/auth.php';
```   
The `/dashboard` route uses the `auth` and `verified` middleware, which means the user must be logged in and have a verified email to view the dashboard.   

For the todo and profile routes, we group them under `Route::middleware('auth')->group(...)`. This ensures that all routes inside the group including viewing, creating, and storing todos, as well as editing the profile are only accessible to logged-in users

Now, any user trying to visit `/todo` will be automatically redirected to the login page (`/login`), which was created by Breeze.
### Running the App
Everything ready, we can now apply our database migration using
```shell
php artisan migrate:fresh
```
This command will drop all tables in our database and then run every migration from the beginning, giving us a clean start with the new model structure.

After that we start our server by using
```shell
php artisan serve
```
If a user visits the `/todo` endpoint without being logged in, they will be redirected to the login page. After logging in or registering, they are automatically redirected to the dashboard. From there, they can log out or visit `/profile` to edit their password or delete their account these templates and functionality are automatically generated by Breeze.   
Once logged in, the user is authenticated and can access the todo section: they can visit `/todo` to see all their tasks or go to `/todo/add` to create a new task. This flow ensures that only authenticated users can manage their tasks while providing a smooth, secure user experience.