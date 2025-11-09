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
## Working with Artisan Tinker
We built our to-do list app, connected it to a database, added authentication, and made it fully functional. But sometimes, we need to test a query, fix a record, or experiment with models without creating a new controller, view, or route every time.

For these situations, Laravel gives us a powerful interactive tool: Artisan Tinker.
### What is Artisan Tinker
Artisan Tinker is an interactive REPL (Read-Evaluate-Print Loop) that's pre-loaded with our project's settings, models, and all of its code. It allows us to run PHP code in the context of our Laravel app, making it perfect for experimenting with our database, models, and other components.

We can think of it as a playground where we can query, create, update, or delete data on the fly. It allows you to:
- Test Eloquent queries directly on your database.
- Create, read, update, or delete records easily.
- Debug model issues without running the server.
- Experiment with data or logic safely before writing actual controller code.
### Starting Tinker
To start Tinker, we first open the terminal, navigate to our project directory (e.g., `workshop3`), and run the following command:

```
php artisan tinker
```
This opens an interactive prompt (`>`) with full access to our Laravel project. Tinker is built on Psysh, which provides syntax highlighting and auto-completion out of the box.

Once inside, the shell gives us access to everything in our Laravel project:
- Our models (e.g., `Todo` from `App\Models`).
- Laravel's Eloquent ORM for database operations.
- Built-in facades like `Auth`, `DB`, and `Validator`.
- Any custom functions or services we have defined.
### Importing Models and Preparing to Work with Data
Before we can manipulate data, we need to import (or reference) the models we want to use.
```
> use App\Models\Todo;
> use App\Models\User;
```

Here, we're importing the `Todo` model and the built-in `User` model.

Tinker is smart. We can often skip the `use` statement and just use the full namespace, which it will auto-complete for us: `App\Models\Todo::all();`

If we need to work with other parts of Laravel, such as a Validator, we can import those too:
```
> use Illuminate\Support\Facades\Validator;
```
This setup allows us to test validation or other logic directly in the shell.
### Querying and Selecting Data
One of the most common uses of Tinker is to retrieve and inspect data from the database. Eloquent provides a "fluent" (chainable) and expressive way to query our models.
#### Basic Retrieval
To get all records from a model, use the `::all()` method:
```php
> $all_tasks = Todo::all();
```
This will return all of the tasks stored in the ``todo`` table in form of Eloquent Collection, which is like a super-powered array. We can iterate over it:
```php
> foreach ($all_tasks as $task) {
...     echo $task->title . " " . $task->done . "\n";
... }
Buy groceries 0
Finish report 1
```
Here we going through the ally and displaying only the title and the state of the task ``(0 = false, 1 = true)``
#### Getting a Single Record
We can select a single record using `::find()`, by providing the task’s **ID** (primary key):
```php
>>> $task = Todo::find(1);
>>> echo $task->title;
Buy groceries
```
The `::find()` method will return `null` if no record is found.
#### Filtering Data
We can filter results using `::where()`. To get the results, we chain `->get()`.
```php
> $done_tasks = Todo::where('done', true)->get();
```
Here we selecting the tasks that are done, We can also perform more complex filtering by chaining multiple `where` calls.
```php
// Get a specific user
>>> $user = App\Models\User::where('name', 'alice')->first();
>>> 
// Get tasks for that user that are not done
>>> $user_tasks = Todo::where('user_id', $user->id)
...                   ->where('done', false)
...                   ->get();
```
Here, we're filtering tasks that belong to 'alice' and are not yet done. We can also use other operators, for example date creating before a specific date
```php
>>> $recent_tasks = Todo::where('created_at', '>', '2025-10-01')->get();
```

### Accessing Related Data
Because our `Todo` model has a `belongsTo` relationship with `User`, we can access data from either direction.

From a task, we can access the user data:
```php
> $task = Todo::find(1);
> echo $task->user->name;
alice
```
We can also do the inverse From a user, we can access all their tasks 
```php
> $user = User::where('name', 'alice')->first();
> echo $user->tasks; // Accesses the 'tasks' as a Collection
=> Illuminate\Database\Eloquent\Collection {#7910, ... }
```
### Advanced Querying
We can also create more advanced queries.

For instance, we can use `->orderBy()` to sort our results, and `->select()` to get only specific columns.
```php
> $sorted_tasks = Todo::orderBy('created_at', 'desc')->get(); 

// Just specific fields
> $task_dicts = Todo::select('title', 'done')->get();
=> Illuminate\Database\Eloquent\Collection {#7915, ... }
```
Finally we can count records or check existence:
```php
>>> $task_count = Todo::where('user_id', $user->id)->count();
=> 3
>>> $has_tasks = Todo::where('user_id', $user->id)->exists();
=> true
```

### Creating New Records
Tinker isn't just for reading data; we can create new records directly.
#### Create, Set, and Save
With the Tinker shell we can easly create a new object from the `Todo` model, set its properties, and then use the `->save()` method.
```php
> $user = User::where('name', 'alice')->first();
> $new_task = new Todo;
> $new_task->title = 'Clean the house';
> $new_task->description = 'Vacuum and dust';
> $new_task->user_id = $user->id;
> $new_task->save(); // Commit to the database
=> true
```
This creates a `Todo` object in memory and saves it to the database.
#### Using the `::create()` Method
There is other way to do it and by using the `::create()` method. This is a shortcut.
```php
> $user = User::where('name', 'alice')->first();
> $user->tasks()->create([
    'title' => 'Wash the car',
    'description' => 'I should wash the car',
    'done' => false
]);
=> App\Models\Todo {#7920, ... }
```
**Note:** This method requires us to have the fields (like `title`, `user_id`) listed in the `$fillable` array in our `App\Models\Todo` model for mass-assignment protection.

#### Bulk Creation
If we need to add several tasks at once, we can use the `::insert()` method for a highly efficient bulk insertion.
```php
> $user = App\Models\User::first();
> $tasks = [
...     ['title' => 'Task 1','description' => 'Task 1 description','updated_at' => now() , 'created_at' => now(),  'user_id' => $user->id],
...     ['title' => 'Task 2','description' => 'Task 2 description' ,'updated_at' => now() , 'created_at' => now(), , 'user_id' => $user->id]
... ];
> Todo::insert($tasks);
=> true
```
**Warning:** `::insert()` is very fast but bypasses Eloquent. It does _not_ automatically add timestamps (`created_at`, `updated_at`) or fire model events. We must add timestamps manually.


### Updating Data
Once we have data, we can easily modify it in Tinker.
#### Updating a Single Instance
To update one record, we fetch the object, change its properties, and then save the changes.
```php
> $task = Todo::find(1);
> $task->done = true;
> $task->description = 'Updated description';
> $task->save();
=> true
```
This method is perfect for updating individual records.
#### Bulk Updates
If we want to update several records at once (e.g., mark all of a user's tasks as done), we use the `->update()` method on a query.
```php
> Todo::where('user_id', $user->id)->where('done', false)->update(['done' => true]);
=> 2 // Returns the number of updated rows
```
This command applies the update directly in the database without loading the objects into memory.
### Deleting Data
Tinker also allows us to safely remove data. When deleting, Eloquent automatically respects model relationships.
#### Deleting a Single Instance
To delete a specific record, first retrieve it, then call the `->delete()` method.
```php
> $task = Todo::find(1);
> $task->delete();
=> true
```
This removes the object from the database permanently.
#### Deleting Multiple Records
If you want to delete several records at once, use `->delete()` directly on a query.
```php
>>> Todo::where('done', true)->delete();
=> 3 // Deletes all completed tasks and returns the count
```
#### Deleting All Records
To remove every record from a table, the fastest and most efficient way is `::truncate()`.
```php
> Todo::truncate();
```
 **Warning:** We should be extremely careful with this command. It's the equivalent of `TRUNCATE TABLE todos` in SQL. It's instant, permanent, and there is no undo.
### Working with Validation and Other Code
Beyond manipulating data, Tinker allows us to experiment with other components of our application.
#### Testing Validation Logic
We can test how our validation logic behaves by using the `Validator` facade.
```php
> use Illuminate\Support\Facades\Validator;
> $data = ['title' => 'Invalid', 'description' => ''];
> $rules = ['description' => 'required'];
> $validator = Validator::make($data, $rules);
> $validator->fails();
=> true
> echo $validator->errors();
{"description":["The description field is required."]}
```
Here for example we created our input data we set the `description` to be empty, after that we rules for our validator we set `description` as required, then we create new validator, using our data and rules, we checked if it fail using the fail methos, it returned true, that mean our data didn't pass the validator rule, we can display the errors then to see what wrong.  
This is very useful for testing validation rules before putting them in a Form Request.
#### Working with Authentication
We can also simulate authentication processes using the `Auth` facade.
```php
> use Illuminate\Support\Facades\Auth;
> $credentials = ['email' => 'alice@example.com', 'password' => 'password123'];
> Auth::attempt($credentials);
=> true // if successful
```
This checks if the provided credentials are correct, just like Laravel's login controller.
#### Running Custom Queries or Raw SQL
While Eloquent is powerful, sometimes we need to execute raw SQL queries.
```php
> use Illuminate\Support\Facades\DB;
> $tasks = DB::select('SELECT * FROM todos WHERE done = 1');
> print_r($tasks);
```
This gives us low-level control over our database when needed.
#### Debugging Configuration
We can also access and inspect our project’s configuration using the `config()` helper.
```php
> config('database.default');
=> "sqlite"
> config('database.connections.sqlite');
=> [ ... ]
```
This is helpful for verifying database connections or other `.env` settings.
### Exiting and Cleaning Up
Once we are done, we can exit the shell in one of two ways:
- Type `exit` and press Enter
- Or simply press Ctrl + D
#### Important Reminder
When we use **Tinker**, every operation (`save()`, `create()`, `update()`, `delete()`, `truncate()`) directly affects our real database.

If we are using our local development database, these changes are permanent.
```php
> App\Models\Todo::truncate();
```
For example this command will permanently remove all tasks from our database instantly. There’s no confirmation.   

To avoid accidental data loss, the safest approach is to use a test database.

We can use an In-Memory Database In our `.env` file, we can set:
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```
Now, when you run `php artisan tinker`, our database will be in-memory, it will be empty every time we start Tinker.

Or we can use a Test File, we can create a test database file:
```
DB_CONNECTION=sqlite
DB_DATABASE=database/test_db.sqlite3
```
Then `touch database/test_db.sqlite3`. When we are done, we can delete this file.
    
The most common practice for Laravel developers is to use `migrate:fresh`, which completely wipes and rebuilds the database. After exiting Tinker, we can always get a clean start by running:    
```php
php artisan migrate:fresh
```
This command drops all tables and re-runs all our migrations, giving you a clean slate.
## Managing the Admin Panel
We've built our to-do list app, connected it to a database, and added authentication. But what if we need a quick, user-friendly way to manage our data without writing custom controllers and views every time? As developers, we might want to add users, edit tasks, or delete records directly through a web interface.

Laravel provide us with **Filament** to handel this. Filament is a TALL-stack (Tailwind, Alpine.js, Laravel, Livewire) package that provides an automatic administrative interface for managing our models. It’s a ready-made dashboard for CRUD (Create, Read, Update, Delete) operations on our database, complete with search, filtering, and deep customization.
### What is the Filament Admin Panel?
The Filament Admin Panel is a web-based interface that is generated from our Eloquent models. It's designed for site administrators and developers to handle tasks like adding new records, editing existing ones, or moderating content.

Filament is secure, deeply customizable, and integrates seamlessly with Laravel's authentication and authorization (Policy) systems.

It allows us to:
- View and search through lists of records.
- Add, edit, or delete data with powerful, auto-generated forms.
- Manage complex relationships (e.g., linking books to authors).
- Customize displays, filters, and actions.
- Handle user accounts and permissions out of the box.
### Setting Up the Admin Panel
Filament is not included in Laravel by default; we must install it.

First, install Filament using Composer:
```shell
composer require filament/filament
```
Then, we run the install command. This will set up the main configuration file (the "Panel Provider") and publish necessary assets.
```shell
php artisan filament:install --panels
```
This creates a new file at `app/Providers/Filament/AdminPanelProvider.php`. This file is the "heart" of our admin panel.

By default, the admin panel is now available at `/admin`.

If there was some errors this mean filament don't match the php instalation, we can edit the `ini.php` file to fix this, we remove`;` from the following config
```
extension=zip
extension=intl
```
### Creating a Super User
To access the admin panel, we need a user. Filament hooks into Laravel's standard `User` model. The easiest way to create a "superuser" is to use Filament's built-in command:
```shell
php artisan filament:user
```
We will be prompted to enter a name, email, and password. This command will create a new user and (if our User model is set up for it) mark them as an "admin" who can access the panel.   
Once done, we can start start the server:
```shell
php artisan serve
```
Now, if we visit **`http://127.0.0.1:8000/admin/`** in our browser and log in with our new superuser credentials, we will see the basic admin dashboard.

Out of the box, it will be empty. Filament doesn't show any models until we explicitly create a resource for them.
### Creating Resources
In Filament, we generate a "Resource" class for each model we want to manage. A Resource is a PHP class that defines the entire admin interface for one model (the list, create, edit, and delete pages).

For our `todo_list` app, we open our terminal and run:
```shell
php artisan make:filament-resource Todo
```
We chose the title name and for other config we select no, this command creates a new `app/Filament/Resources/` directory and, inside it, a `TodoResource.php` file, along with its related page files.

If we reload the admin site, we will now see "Todos" listed in the sidebar. Filament has automatically scanned our `Todo` model and database table to create a list page with search and "Create" / "Edit" buttons.
### Customizing the Admin Interface
The default admin is functional, but we can customize it to be much more powerful. Let's use a new, more complex example (a `library`) to see Filament's power.

First, let's create the app and models.
```shell
php artisan make:model Publisher -m
php artisan make:model Author -m
php artisan make:model Book -m
```
#### Creating the Models
We will create three Eloquent models: **Publisher**, **Author**, and **Book**.
- A **publisher** has a `name` and `address`, and it can publish **one or many books** → **one-to-many** relationship.
- An **author** has a `first_name` and `last_name`, and can be associated with **many books** → **many-to-many** relationship.
- A **book** belongs to a publisher and can have multiple authors. It has:
    - `id`
    - `publisher_id`
    - `publish_date`
    - `available` (availability status)

**`app/Models/Publisher.php`**:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    protected $fillable = ['name', 'address'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
```
The **Publisher** model defines a one-to-many relationship using `hasMany()`, meaning a single publisher can publish multiple books.

**`app/Models/Author.php`**:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    protected $fillable = ['first_name', 'last_name'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
```
The **Author** model uses `belongsToMany()` to represent a many-to-many relationship, since an author can write multiple books and a book can have multiple authors.

**`app/Models/Book.php`**:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    protected $fillable = ['title', 'publisher_id', 'publish_date', 'available'];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }
}
```

The **Book** model defines two relationships:
- `belongsTo(Publisher::class)` a book belongs to one publisher
- `belongsToMany(Author::class)` a book can have multiple authors

#### Editing the Migration file
We also need to edit the migration files so we set the structure of our tables

**`database\migrations\x_x_x_create_publishers_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->timestamps();
        });
    }

    public function down(): void

    {
        Schema::dropIfExists('publishers');
    }
};
```
Here we defined the structure of the `publishers` table in the database. In the `up()` method, it creates the table with four columns: an auto-incrementing `id`, `name` and `address` as string fields, and `timestamps` to track when each record is created or updated. The `down()` method reverses this action by dropping the `publishers` table if it exists, allowing the migration to be rolled back safely.

**`database\migrations\x_x_x_create_authors_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();

        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }

};
```
Same as before we defined the structure of the `authors` table in the database. In the `up()` method, it creates the table with four columns: an auto-incrementing `id`, `first_name` and `last_name` as string fields, and `timestamps` to track when each record is created or updated. The `down()` method reverses this action by dropping the `authors` table if it exists, allowing the migration to be rolled back safely.

**`database\migrations\x_x_x_create_books_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->foreignId('publisher_id')->constrained()->onDelete('cascade');
            $table->date('publish_date');
            $table->boolean('available');
            $table->timestamps();
        });
        
        Schema::create('author_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

        });
    }

    public function down(): void

    {
        Schema::dropIfExists('books');
    }

};
```
Finally  we created two tables in this migration.
- The **`books`** table stores information about each book, including an auto-incrementing `id`, `title` (up to 200 characters), a foreign key `publisher_id` referencing the `publishers` table (with cascade on delete), `publish_date` as a date, `available` as a boolean, and `timestamps` to track creation and updates.
    
- The **`author_book`** table is a junction (pivot) table to handle the many-to-many relationship between authors and books. It contains an auto-incrementing `id`, `author_id` and `book_id` as foreign keys, both with cascade on delete, ensuring that related records are removed if an author or book is deleted.

After defining our models we need to run  `php artisan migrate` to apply them on our database
#### Creating Resources for the Models
To make these models appear in the admin, we generate a resource for each:
```shell
php artisan make:filament-resource Author
php artisan make:filament-resource Publisher
php artisan make:filament-resource Book
```
Now, if we refresh the admin panel, we will see Books, Authors, and Publishers listed. Clicking on any of them will allow us to add, edit, or delete records.   
However, by default Filament does not automatically generate forms for the fields, so records might be created empty. We must customize the resources.
#### Editing the Authors Interface 
While the default works, we can customize it. we do that by editing the `ModelForm.php` file.
for example lets edit the author so we can see a form when creating new author

**`app\Filament\Resources\Authors\Schemas\AuthorForm.php`**
```php
<?php

namespace App\Filament\Resources\Authors\Schemas;
namespace App\Filament\Resources\Books\Schemas;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(200),
                    
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(200),
            ]);
    }
}
```
Here we editted the configue method so now it return two components of type TextInput both of them are required and take as max 200 character

Now we can create our authors and edit them but when we display all the list of authors we see empty fields, we need to edit that on `AuthorsTable.php`

**`app\Filament\Resources\Authors\Tables\AuthorsTable.php`**
```php
<?php

namespace App\Filament\Resources\Authors\Tables;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
              ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```
In this `AuthorsTable` configuration, we define which columns, filters, and actions appear in the admin table for authors.
- **Columns:**
    - We start with the `id` column and make it sortable, so authors can be ordered by their ID.
    - Next, we add the `first_name` column, give it a label "First Name", make it sortable, and also searchable. This allows users to sort authors by first name or quickly find an author by typing their name.
    - Similarly, the `last_name` column is labeled "Last Name", sortable, and searchable, enabling sorting and searching by last name.
#### Editing the Books Interface 
Lets edit not the books interface so we can create and edit books we edit the `BookForm.php`

**`app\Filament\Resources\Books\Schemas\BookForm.php`**
```php
<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(200),
                    
                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->required(),
                    
                Select::make('authors')
                    ->relationship('authors', 'first_name')
                    ->multiple()
                    ->preload(),

                DatePicker::make('publish_date'),
                Toggle::make('available')
                    ->required(),
            ]);
    }
}
```
This `BookForm` defines the following five fields for creating or editing a book:
1. **`TextInput::make('title')`**
    - This is a standard text input field for the book's title.
    - `->required()`: The user cannot submit the form without filling in a title.
    - `->maxLength(200)`: The user cannot type more than 200 characters.
2. **`Select::make('publisher_id')`**
    - This is a dropdown menu to select the book's publisher.
    - `->relationship('publisher', 'name')`: It automatically gets its options from the `publisher` relationship on your `Book` model, using the `name` column for the display text.
    - `->searchable()`: The user can type into the dropdown to find a publisher quickly.
    - `->required()`: The user must select a publisher.
3. **`Select::make('authors')`**
    - This is another dropdown, this time for the book's author(s).
    - `->relationship('authors', 'first_name')`: It gets its options from the `authors` relationship.
    - `->multiple()`: This allows the user to select _more than one_ author for a single book.
    - `->preload()`: This loads all authors as soon as the page loads.
4. **`DatePicker::make('publish_date')`**
    - This provides a simple calendar popup for the user to select the book's publication date.
5. **`Toggle::make('available')`**
    - This is a simple on/off "switch" component.
    - `->required()`: The user must set this to either "on" or "off."

But as before when we load our books we get empty fields, we fix this by editing `BooksTable.php`

**`app\Filament\Resources\Books\Tables\BooksTable.php`**
```php
<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Table;

class BooksTable
{
    public static function configure(Table $table): Table

    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                    
                TextColumn::make('publisher.name')
                    ->sortable(),
                TagsColumn::make('authors.first_name')
            ->label('Authors'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
            ])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```
In this `BooksTable` configuration, we define which columns  appear in the admin table for books.
- We start with the `title` column and make it `searchable`, allowing users to quickly find a book by typing its title.
- Next, we add the `publisher.name` column. This uses the `publisher` relationship to display the publisher's `name` and makes the column `sortable`.
- We then add a `TagsColumn` for `authors.first_name`. This displays each related author's first name as a separate "tag" or "pill" and gives the column a clean `label` of "Authors".
- We add the `created_at` column. It's formatted as a `dateTime`, is `sortable`, and is `toggleable`, meaning it's hidden by default (`isToggledHiddenByDefault: true`) but can be shown by the user.
- The `updated_at` column is added with the exact same settings: formatted as a `dateTime`, `sortable`, and hidden by default.
#### Editing The Publisher
Finally, just like we did for the other modules, we set up the Publisher form and table, allowing us to create, edit, and display publisher records in the admin panel.
### Relation Managers
One of Filament's most powerful features is the ability to manage related data directly from a parent model's edit page. For example, when editing a `Publisher`, it's highly efficient to see, add, and edit all of that publisher's `books` on the same screen. Filament achieves this using Relation Managers,

Enabling this is straightforward. we first generate the necessary class using an Artisan command. By running
```shell
php artisan make:filament-relation-manager PublisherResource books title
```
Here we are telling Filament to create a new manager for the `PublisherResource` that will manage the `books` relationship, using the `title` column as a key field in its table. This command generates a new file located at `app/Filament/Resources/PublisherResource/RelationManagers/BooksRelationManager.php`

Once the file is created, the final step is to register it within the parent resource. We simply open the `app/Filament/Resources/PublisherResource.php` file and add our new `BooksRelationManager::class` to the `getRelations()` method. With this in place, navigating to any publisher's edit page in the admin panel will now display a complete table of all their associated books at the bottom, complete with its own "Create," "Edit," and "Delete" actions.

**`app/Filament/Resources/PublisherResource.php`**:
```php
<?php
namespace App\Filament\Resources;
// ...
// 1. Import the new Relation Manager
use App\Filament\Resources\PublisherResource\RelationManagers\BooksRelationManager;

class PublisherResource extends Resource
{
	// ... all the other methods (form, table) ...

	// 2. Add this method
	public static function getRelations(): array
	{
		return [
			BooksRelationManager::class,
		];
	}
}
```
### Customizing the Admin Site Itself
Beyond managing individual models, Filament provides extensive control over the entire admin interface, allowing us to tailor its branding, colors, and behavior. This global customization is centralized in a single file created during installation, the **`AdminPanelProvider.php`**, which is typically located in `app/Providers/Filament`. Inside this class, we modify the main `Panel` object within its `panel()` method.   
```php
<?php
namespace App\Providers\Filament;
use Filament\Panel;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Library Management System') 
            ->title('Library Admin') 
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // ...
            ;
    }
}
```
By chaining different methods onto the `$panel` variable, we can easily personalize the admin experience. For example, we can use the `brandName()` method to set a custom header for our admin panel (like "Library Management System") and the `title()` method to change the browser tab's title. We can even redefine the entire color scheme by passing a new `primary` color, such as `Color::Amber`, to the `colors()` method. These simple, chainable commands give us a powerful way to ensure the admin panel matches our application's identity.
