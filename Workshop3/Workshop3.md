## Objectives
- Working with Databases and Eloquent Models
- Authentication and Session Management
- Working with Artisan Tinker
- Managing the Admin Panel 

## Databases and Eloquent Models
In our previous workshop, we used session to store data, this approach has a serious limitation: data stored in a session is volatile and temporary. As soon as the user's session expires or is cleared, all that information is lost.  
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
- Examples: SQLite, PostgreSQL, MySQL, SQL Server.
#### Non-Relational Databases (NoSQL)
These databases are more flexible and store data in various formats such as documents (like JSON), key-value pairs, wide-column stores, or graphs. They’re often used for large-scale systems or unstructured data.
- Examples: MongoDB, Redis, Cassandra

For most web applications and especially for Laravel projects we use a relational database..
### Relational Databases Structure
A relational database stores data in tables. Each table represents a specific entity type (e.g., a `Customers` table or an `Orders` table). Each row represents a single record, and each column defines a property or field (e.g., `name`, `email`, `order_date`).
### DataBase Keys 
#### Primary Keys
A primary key is a special column (usually `id`) that uniquely identifies each record in a table.
- It prevents duplicate entries.    
- It allows the database to quickly find records.

**Example: Customers Table**

| **id (Primary Key)** | **name**    | **email**         |
| -------------------- | ----------- | ----------------- |
| 1                    | Alice Smith | alice@example.com |
| 2                    | Bob Johnson | bob@example.com   |

Here:
- Each row represent unique customer.
- The `id` column is the primary key.
#### Foreign Keys and Relationships
Relational databases are powerful because they can define relationships between tables linking related data without duplicating it. This is done through foreign keys.      
A foreign key is a column in one table that refers to the primary key of another table. This creates a connection between records and ensures consistency for example, preventing the deletion of a customer who still has existing orders.  
### Common Types of Relationships
#### One-to-One   
Each record in Table A is linked to exactly one record in Table B, and vice versa, this is useful when splitting related data into separate tables.    
Example: A `Users` table and a `UserProfiles` table, where each profile corresponds to exactly one user via a foreign key referencing the user’s `id`.    
#### One-to-Many 
A single record in one table can be linked to multiple records in another, but each of those records refers back to only one parent, this is the most common type of relationship  for example, one customer can place many orders.    
Example: Orders Table

| id (Primary Key)  | order_date | total_amount  | customer_id (Foreign Key) |
| ----------------- | ---------- | ------------- | ------------------------- |
| 101               | 2025-10-25 | 45.99         | 1                         |
| 102               | 2025-10-26 | 29.50         | 1                         |
| 103               | 2025-10-27 | 100.00        | 2                         |

- The `customer_id` column is a foreign key referencing the `id` in the `Customers` table.
- This forms a one-to-many relationship: Customer 1 has two orders, while Customer 2 has one.

#### Many-to-Many 
In this relationship, multiple records in Table A can be linked to multiple records in Table B, to manage this, a third table often called a junction or association table is used to store the connections.    
Example: In a library system, one book can have multiple authors, and one author can write multiple books.    
A `BookAuthors` table would contain pairs of foreign keys linking books and authors:

| book_id (FK) | author_id (FK) |
| ------------ | -------------- |
| 10           | 3              |
| 10           | 5              |
| 12           | 3              |

This structure keeps the data organized and avoids duplication while preserving relationships.
### Connecting Apps to Databases
Now that we understand what databases are, let’s see how our Laravel application can communicate with one.  
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
Laravel gives us a much better solution: its built-in Object-Relational Mapper (ORM), called Eloquent.
### Using Object-Relational Mapper (ORM)
An Object-Relational Mapper (ORM) is a layer that bridges the gap between our application's objects (PHP classes) and the relational database's tables. It translates PHP operations into SQL queries automatically.  
Here are the key benefits of using ORM:
- Abstraction and Portability: We define our database structure using PHP migration files and interact with it using PHP model classes. The ORM generates the appropriate SQL. If we ever switch databases, we only need to change a configuration setting in our `.env` file, not rewrite our queries.
- Improved Productivity and Readability: Instead of writing raw SQL strings, we work entirely in PHP.
    - SQL: `SELECT * FROM users WHERE email = 'alice@example.com'`
    - Eloquent ORM: `User::where('email', 'alice@example.com')->get();`
- Data Integrity and Security: The ORM manages relationships and automatically escapes inputs, protecting our app from SQL injection attacks.
- Team Consistency: By using the same data access patterns (Eloquent), all developers on a team can easily read and extend each other’s code.

### Working with Eloquent Models and Migrations
In Laravel we will use th Eloquent ORM, To work with it we need to:
1. Make Migrations Files which act like version control for our database we define them inside the `database/migrations` folder. Each migration file contains instructions to `create` or `modify` a database table.
2. Make Models Classes, they are PHP classes represents a table, we define them inside  in the `app/Models` folder. We use them to interact with that table using PHP methods (e.g., `Todo::all()`).
### Creating Our App
Let’s create a new `todo_list` app to put our model knowledge into practice. This app will let us view tasks and add new ones.  
First, we create a new Laravel project named `workshop3`:
```shell
composer create-project laravel/laravel workshop3
cd workshop3
```
#### Creating The Model 
Now, we create the model that will represent our database schema. we use single command to create both the model and its migration file.
```shell
# '-m' tells artisan to also create a migration file
php artisan make:model Todo -m
```
This command creates two files for us:
1. Model: `app/Models/Todo.php`
2. Migration: `database/migrations/xxxx_xx_xx_xxxxxx_create_todos_table.php`

After creating the model, we must configure the migration file located in ``database/migrations/xxxx_xx_xx_xxxxxx_create_todos_table.php``. This file acts as version control for our database, allowing us to define and modify our table structure programmatically.  
The migration file returns an anonymous class that extends the ``Migration`` class. It contains two primary methods: ``up()`` and ``down()``.  

The ``up`` method is executed when we run ``php artisan migrate``. It is used to add new tables, columns, or indexes to our database.
Inside this method, we use the ``Schema::create`` facade to build the table. It accepts two arguments:
- The table name: In our case, 'todos'.
- A Closure: This function receives a ``Blueprint $table`` object, which we use to define the columns.

Inside the blueprint closure, we define our table schema using various column types:

- ``$table->id()``: Creates an auto-incrementing id (BIGINT) as the primary key.
- ``$table->string('title')``: Creates a VARCHAR column named title for short text.
- ``$table->text('description')``: Creates a TEXT column named description for longer content.
- ``$table->boolean('done')->default(false)``: Creates a boolean column named done that defaults to false (0) for new tasks.
- ``$table->timestamps()``: A helper method that adds ``created_at`` and ``updated_at`` (TIMESTAMP) columns automatically.

the ``down`` method does the opposite of ``up``. It typically contains ``Schema::dropIfExists('todos')``, which allows us to roll back (undo) the migration if needed.
we do that using `php artisan migrate:rollback` which undo the last migration, if we want more we can use `php artisan migrate:rollback --step=2` which undo the past to migration, to undo everything we use `php artisan migrate:reset`

With this in mind, let's edit out file now.   
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
After defining the schema, we run the `php artisan migrate` command to create the table in our database.
```shell
php artisan migrate
```
This applies the migration, creating the `todos` table.
#### Configuring the Model 
Our next step is to configure the ``Todo`` model, we must tell ``Eloquent`` which fields are "fillable," meaning they are safe to be mass-assigned from a form.

Inside the``app/Models/Todo.php`` file we add to our class the ``$fillable`` property. This property is array of the columns we want to mark as fillable, in our case, we only need title and description.
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
#### Creating the Validation
We need Form Request class to auto-validates incoming request, we create it using:
```shell
php artisan make:request StoreTodoRequest
```
This creates a new file at ``app/Http/Requests/StoreTodoRequest.php``. Let’s edit it as follows:
- Update the ``authorize`` method: Return ``true`` from this method, as we are not implementing authorization logic yet.
- Update the ``rules`` method: Return an array containing the validation rules for title and description. Both fields should be required and must be strings. Additionally, the title should have a maximum limit of 255 characters.
```php
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }

    public function rules(): array{
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }
}
```
#### Creating the Controller 
We define Controller to handle the app logic.
```shell
php artisan make:controller TodoController
```
This command will create `app/Http/Controllers/TodoController.php` file, this file contain empty `TodoController` class, inside it we create three methods:
- `index`: This method get all the tasks from Todo model using `Todo::all()` and pass them to our view.
- `create`: this method return the form view.
- `store`: finally this method handel submitting the form, it use the `StoreTodoRequest` to validate the form data, then
	- it get the inputs fields values. 
	- it use ``Todo:create`` to create new record in our table.
	- finally it redirect the user to ``task_list`` endpoint to see all submitted tasks.

```php
<?php
namespace App\Http\Controllers;
use App\Models\Todo; 
use App\Http\Requests\StoreTodoRequest; 

class TodoController extends Controller{

    public function index(){
        $tasks = Todo::all(); 
        return view('todo_list.tasks', ['tasks' => $tasks]);
    }

    public function create(){
        return view('todo_list.add_task');
    }

    public function store(StoreTodoRequest $request){
        $validatedData = $request->validated();
        Todo::create($validatedData);
        return redirect()->route('task_list');
    }
}
```
#### Setting The Routes
After we finish the controller methods, we connect them to URLs in the main `routes/web.php` file.   
**`routes/web.php`:**
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

Route::get('/', function () {
    return view('welcome');
});

## add this
Route::get('/todo', [TodoController::class, 'index'])->name('task_list');
Route::get('/todo/add', [TodoController::class, 'create'])->name('add_task');
Route::post('/todo/add', [TodoController::class, 'store']); 
```
#### Creating the Viewss 
Finally, we create views for our app, we start by creating the base layout view.    
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
Next we create the view that will display our all the submitted tasks.   
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
Finally the view that display the add task form.      
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
We’ll use the stylesheet from the `materials` folder. Create a file called `style.css` inside `public/css/` and paste the styles from the `material/styles.css` file. The `asset()` helper in our Blade files will now find it.
#### Running The Application
Now we can run our application using 
```shell
php artisan serve
```
When we visit `http://127.0.0.1:8000/todo`, we will see our task list, and at `http://127.0.0.1:8000/todo/add`, we will be able to add new tasks with full validation
## Authentication, Authorization and Session Management
Authentication is the process of verifying the identity of a user, device, or system before granting access to resources or data, with authentication we verify who the user is. In other hand Authorization is the process of verifying if a user is authorizad to do a specific action, with authorization we verify what user authorized to do.    
Without authentication, anyone could access or modify data, leading to security risks like unauthorized edits, data breaches, or misuse.   
Authentication typically involves credentials like usernames and passwords, but it can also include more advanced methods such as two-factor authentication (2FA), biometrics, or token-based systems.
### Sessions
How does a website "remember" who you are from one click to the next? By default, the web is "stateless," meaning every time you click a link or refresh a page, the server treats you like a total stranger who just arrived for the first time.   
To solve this, We use sessions. Session act like a temporary locker assigned to you. When you first visit the site, the server hands you a unique "locker key" stored in your browser as a cookie. As long as you hold that key, the server can look inside your specific locker to retrieve your information like your name or your shopping cart every time you move to a new page.  
For example, once you log in, Laravel stores your id in a session. This allows the application to recognize you instantly on every subsequent page without asking you to login again.

### Updating Our To-Do List App
Our current to-do app has a major issue: all users share the same task list, and anyone can submit a task this not good because users can't keep track on their own tasks and hacker can exploit this to submit random stuffs without leaving trace, To make our app secure, we need to update it so that each user only sees and manages their own tasks.    
#### Adding User Authentication 
Before we restrict tasks, we need users to be able to register, log in, and log out. Instead of manually creating forms and controllers, We will use a starter kit that handel all the authentication buissness for us. We'll use Breeze.   
First we install it using Composer:
```shell
composer require laravel/breeze --dev
```
Next, we run the `breeze:install` command, to add all the routes, controllers, and view files to our project.
```shell
php artisan breeze:install
# When prompted, choose "Blade" then 0 for dark mode and 1 for test unit
```
Breeze use Vite as front-end framework so we need to install the front-end dependencies, we do it using:
```shell
npm install
```
After that, we build our assets, so they can be served
```shell
npm run build
```
Finally we run the migration to updates our database with table that `breeze` use
```shell
php artisan migrate
```
Now, if we run our application, we can create an account, log in, and log out. When a user logs in or registers, they are automatically redirected to the dashboard. All of this functionality is generated for us by Breeze.

Breeze automatically provides:
- Controllers: in `app/Http/Controllers/Auth/` for authentication actions like login and registration.
- Views: in `resources/views/auth/` for the login, registration, and related pages.
- Routes: in the `routes/auth.php` file to handle authentication endpoints.
### Update The todo_list Feature
#### Updating the Todo Model
Now that authentication is set up, let’s update our `todo_list` feature. The first step is to link each task to a specific user. Since we cannot modify the original `todos` migration file since it has already been executed Laravel will not run it again, we need to create a new migration that adds the `user_id` column.  
First we run the command to create new migration:
```shell
php artisan make:migration add_user_id_to_todos_table --table=todos
```
The `--table=todos` option tells Laravel that this migration will modify the existing `todos` table instead of creating a new one.    
Now lets edit the new migration file and add to it the for foreign key to users.

We declare new column using `$table->foreignId()` we name it `user_id`, and we use `constrained()` to automatically sets up the foreign key relationship with the `users` table, and, `cascadeOnDelete()` ensures that if a user is deleted from the system, all of their associated tasks will be removed as well.
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
Finally we update the `Todo` model to define the relationship between a task and its owner. We add a `user()` method inside the `Todo` model that returns `$this->belongsTo(User::class)`. This tells Laravel that each todo item belongs to a specific user. With this in place, we can easily access the user who created a task by calling `$todo->user`, and Eloquent will automatically handle the relationship for us.

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
#### Updating the User Model
Now that each todo is linked to a user, we need to define the opposite side of the relationship inside the `User` model. Since a single user can have many todo items, we create  `tasks()` method that returns `$this->hasMany(Todo::class)`, with this we can return all tasks linked to specific user by calling `$user->tasks`. 

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
We also edit our `TodoController` to only show the logged-in user's tasks.   
We updated the `index` method, we call `$request->user()->tasks()->get()` to automatically retrieve only the tasks associated with the logged-in user.  

We also updated the `store` method to attach new tasks to the authenticated user. We use `$request->user()->tasks()->create($request->validated())`. This tells Laravel to create the task through the user's relationship, which automatically fills in the correct `user_id`.   
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
### Securing Routes
Finally we need tp apply the `auth` middleware to our `todo` routes. We do that in the `routes/web.php` file. Breeze has already created a middleware group for us. We just need to move our `todo` routes inside it.

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

For the todo and profile routes, we group them under `Route::middleware('auth')->group(...)`. All routes inside the group are only accessible to logged-in users. If user isn't logged in he will be automatically redirected to the login page (`/login`).
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
If a user visits the `/todo` endpoint without being logged in, they will be redirected to the login page. After logging in or registering, they are automatically redirected to the dashboard. From there, they can log out or visit `/profile` to edit their password or delete their account these views and functionality are automatically generated by Breeze.    
Once logged in, the user is authenticated and can access the todo section: they can visit `/todo` to see all their tasks or go to `/todo/add` to create a new task.
### Editing The Breeze Auth 
We can Edit The Breeze auto generated authentication resource, we can edit the User model, the migration files and the views
#### Editing the Migration File
To edit our user table we go to `database/migrations/xxxx_xx_xx_xxxx_create_users_table.php`, we can see Breeze defined three table for us, ``users`` which represent our users, ``sessions`` to save session, and finally ``password_reset_tokens``.

The ``users`` table is the heart of our authentication system. It stores the credentials and basic information for everyone who registers on our application.
- ``id()``: Creates an auto-incrementing primary key.
- ``email_verified_at``: Stores the date/time the user clicked their verification link. If it's NULL, the user hasn't verified their email yet.
- ``rememberToken()``: Adds a remember_token column. This is used for the "Remember Me" checkbox on login pages so the user doesn't get logged out when they close their browser.
- ``timestamps()``: Automatically creates created_at and updated_at columns
```php
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
```
The ``sessions`` is used if we choose the database driver for session management (instead of files or cookies). It tracks active users currently browsing our site.

- ``id``: Unlike the users table, this ID is usually a long, unique string (the Session ID).
- ``user_id``: This is a Foreign Key. It links the session to a specific person in the users table.
- ``ip_address``: Stores the user's IP. It’s 45 characters long to support both IPv4 and IPv6 formats.
- ``payload``: This is where the actual session data is stored (like shopping cart items or temporary form data) in a serialized format.
- ``last_activity``: A Unix timestamp showing when the user last refreshed a page. This allows Laravel to "garbage collect" or delete sessions that have been inactive for too long.
```php
Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
```
Finally the ``password_reset_tokens`` table is a temporary storage area used to handle the security handshake when a user forgets their password.

- ``email``: This is the primary key. Using the email as the key ensures that an email address can only have one active reset token at a time; a new request simply overwrites the old one.
- ``token``: This stores the unique, hashed string sent to the user's email. When the user clicks the reset link, Laravel matches this value against the URL to verify their identity.
- ``created_at``: A timestamp used to determine if the token has expired. By default, Laravel checks this to ensure the link is still valid (usually within 60 minutes).
```php
Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
```
#### Accessing the Model
We can also access and edit the model that interact with our table, we find it in the `app/Models/User.php` file,Breeze aleardy set for us:
- ``$fillable``: It listed the columns that we permit to be updated or created at once .

- ``$hidden``: This list protects sensitive data. When the Model is converted to JSON, these attributes will be excluded. This prevents our users' hashed passwords or "remember me" tokens from being exposed to the public.

- ``casts()``: This method ensures that data is automatically converted to a specific format when we retrieve it.

    - ``email_verified_at``: Converts a database string into a Carbon/PHP datetime object, making it easy to format or compare dates.
    - ``password``: Uses the hashed cast to ensure that any string assigned to this attribute is automatically encrypted before it hits the database.
```php
protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
   
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
```
#### Accessing the Request Validator
Breeze also Create for us two request validator:
- `Http/Requests/ProfileUpdateRequest.php`: this define the validator for update profile request,Breeze set rules for the email and the name.
- ``Http/Requests/Auth/LoginRequest.php``: this define the validator for login request, Breeze set rules for email and passowrd, and additional helper methods.
    - ``authenticate()``: This method attempts to log the user in. If the credentials (email and password) are wrong, it increments the "failed attempt" counter and throws a validation error.
    - ``ensureIsNotRateLimited()``: This is a security shield. It checks if the user has failed to log in too many times (usually 5 attempts). If they have, it locks them out for a specific amount of time to prevent Brute Force attacks.
    - ``throttleKey()``: This generates a unique identifier for the user making the request, usually combining their email address and IP address. This ensures that if one person is trying to hack an account, they get blocked without blocking other legitimate users on the same network.
#### Controllers 
Breeze provides pre-built controllers that handle the logic between our routes and views. We can modify these to add custom logic.
- ``Http/Controllers/Auth/...``: This directory contains specialized controllers for authentication, such as AuthenticatedSessionController (Login), RegisteredUserController (Registration), and PasswordController.
- ``Http/Controllers/ProfileController.php``: This handles the user's profile settings, including updating their information or deleting their account.
#### Routes
The routes for authentication are kept separate from our main web routes.
- ``routes/auth.php``: This file contains all the routes for logging in, registering, resetting passwords, and email verification.

#### Views (Templates)
Breeze uses Blade components to build the user interface. If you want to change the look and feel of the login page, the dashboard, or the profile forms, you will find them here:
- ``resources/views/auth/``: Contains the templates for login, registration, and password reset pages.
- ``resources/views/components/``: Contains the components that other templates use.
- ``resources/views/profile/``: Contains the templates for the profile edit and delete forms.
- ``resources/views/layouts/``: Contains the base layouts (like app.blade.php and guest.blade.php) that define the overall structure (navigation, CSS, and JS) of your pages.
## Working with Artisan Tinker
Sometimes, we need to test a query, fix a record, or experiment with models without creating a new controller, view, or route, For these situations, Laravel gives us a powerful interactive tool: Artisan.
### What is Artisan Tinker
Artisan Tinker is an interactive REPL (Read-Evaluate-Print Loop) that's pre-loaded with our project's settings, models, and all of its code. It allows us to run PHP code in the context of our Laravel app, making it perfect for experimenting with our database, models, and other components.

We can think of it as a playground where we can query, create, update, or delete data on the fly. It allows us to:
- Test Eloquent queries directly on our database.
- Create, read, update, or delete records easily.
- Debug model issues without running the server.
- Experiment with data or logic safely before writing actual controller code.
### Starting Tinker
To start Tinker, we first open the terminal, navigate to our project directory (e.g., `workshop3`), and run the following command:
```shell
php artisan tinker
```
This opens an interactive prompt (`>`) with full access to our Laravel project. Tinker is built on Psysh, which provides syntax highlighting and auto-completion out of the box.  
Once inside, the shell gives us access to everything in our Laravel project:
- Our models (e.g., `Todo` from `App\Models`).
- Laravel's Eloquent ORM for database operations.
- Built-in facades like `Auth`, `DB`, and `Validator`.
- Any custom functions or services we have defined.
### Importing Models and Preparing to Work with Data
Before we can manipulate data, we need to import the models we want to use.
```shell
> use App\Models\Todo;
> use App\Models\User;
```
We can often skip the `use` statement and just use the full namespace, which it will auto-complete for us: `App\Models\Todo::all();`  
If we need to work with other parts of Laravel, such as a Validator, we can import those too:
```shell
> use Illuminate\Support\Facades\Validator;
```
### Querying and Selecting Data
#### Basic Retrieval
To get all records from a model, we use the `::all()` method:
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
Here we going through the array and displaying only the title and the state of the task ``(0 = false, 1 = true)``
#### Getting a Single Record
We can select a single record using `::find()`, by providing the task’s ID:
```php
>>> $task = Todo::find(1);
>>> echo $task->title;
Buy groceries
```
The `::find()` method will return `null` if no record is found.
#### Filtering Data
We can filter results using `::where()`. chainned with `->get()` to return the result.
```php
> $done_tasks = Todo::where('done', true)->get();
```
We can also perform more complex filtering by chaining multiple `where` calls.
```php
// Get a specific user
>>> $user = App\Models\User::where('name', 'alice')->first();
>>> 
// Get tasks for that user that are not done
>>> $user_tasks = Todo::where('user_id', $user->id)
...                   ->where('done', false)
...                   ->get();
```
Here, we're filtering tasks that belong to 'alice' and are not done.   
We can also use other operators, for example date creating before a specific date
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
We can use `->orderBy()` to sort our results, and `->select()` to get only specific columns.
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
There is other way to create record, by using the `::create()` method.
```php
> $user = User::where('name', 'alice')->first();
> $user->tasks()->create([
    'title' => 'Wash the car',
    'description' => 'I should wash the car',
    'done' => false
]);
=> App\Models\Todo {#7920, ... }
```
This method requires us to have the fields (like `title`, `user_id`) listed in the `$fillable` array in our `App\Models\Todo` model for mass-assignment protection.

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
`::insert()` is very fast but bypasses Eloquent. It does not automatically add timestamps (`created_at`, `updated_at`) or fire model events. We must add timestamps manually.
### Updating Data
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
If we want to update several records at once, we use the `->update()` method on a query.
```php
> Todo::where('user_id', $user->id)->where('done', false)->update(['done' => true]);
=> 2 // Returns the number of updated rows
```
This command applies the update directly in the database without loading the objects into memory.
### Deleting Data
#### Deleting a Single Instance
To delete a specific record, first retrieve it, then call the `->delete()` method.
```php
> $task = Todo::find(1);
> $task->delete();
=> true
```
This removes the object from the database permanently.
#### Deleting Multiple Records
We can delete several records at once, by using `->delete()` directly on a query.
```php
>>> Todo::where('done', true)->delete();
=> 3 // Deletes all completed tasks and returns the count
```
#### Deleting All Records
To remove every record from a table, the fastest and most efficient way is `::truncate()`.
```php
> Todo::truncate();
```
We should be extremely careful with this command. It's the equivalent of `TRUNCATE TABLE todos` in SQL. It's instant, permanent, and there is no undo.
#### Running Custom Queries or Raw SQL
While Eloquent is powerful, sometimes we need to execute raw SQL queries, we can do that by using DB facade, for example we can use `DB::select()` to retrive data
```php
> use Illuminate\Support\Facades\DB;
> $tasks = DB::select('SELECT * FROM todos WHERE done = 1');
> print_r($tasks);
```
This gives us low-level control over our database when needed.
### Working with Validation 
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
Here for example we created our input data we set `description` to be empty, after that we create rules array for our validator we set `description` as required, then we create new validator, using our data and rules, we checked if it fail using the `fails()` method, it returned true, which mean our data didn't pass the validator rules.   
#### Working with Authentication
We can also simulate authentication processes using the `Auth` facade.
```php
> use Illuminate\Support\Facades\Auth;
> $credentials = ['email' => 'alice@example.com', 'password' => 'password123'];
> Auth::attempt($credentials);
=> true // if successful
```
This checks if the provided credentials are correct, just like Laravel's login controller.

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
Once we are done, we can exit the shell by typing `exit` and press Enter, or simply press Ctrl + D
#### Important Reminder
When we use Tinker, every operation (`save()`, `create()`, `update()`, `delete()`, `truncate()`) directly affects our real database.  
If we are using our local development database, these changes are permanent, to avoid accidental data loss, the safest approach is to use a test database.  
We can use an In-Memory Database In our `.env` file, we can set:
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```
Now, when we run `php artisan tinker`, our database will be in-memory, it will be empty every time we start Tinker.
We can also use a test database file: 
In our `.env` file, we set:
```
DB_CONNECTION=sqlite
DB_DATABASE=database/test_db.sqlite3
```
Then `touch database/test_db.sqlite3`. When we are done, we delete this file.
## Managing the Admin Panel
Filament is a TALL-stack (Tailwind, Alpine.js, Laravel, Livewire) package that provides an automatic administrative interface for managing our models. It’s a ready-made dashboard for CRUD (Create, Read, Update, Delete) operations on our database, complete with search, filtering, and deep customization.    
The Filament Admin Panel is generated from our Eloquent models.   
It allows us to:
- View and search through lists of records.
- Add, edit, or delete data with powerful, auto-generated forms.
- Manage complex relationships (e.g., linking books to authors).
- Customize displays, filters, and actions.
- Handle user accounts and permissions out of the box.
### Setting Up the Admin Panel
First, we install Filament using Composer:
```shell
composer require filament/filament
```
After this we install `filament` on our web app
```shell
php artisan filament:install --panels
```
By default, the admin panel is now available at `/admin`.  
If there was some errors this mean filament don't match the php instalation, we can edit the `ini.php` file to fix this, we remove`;` from the following config
```
extension=zip
extension=intl
```
### Creating a  User
To access the admin panel, we need a user. Filament hooks into Laravel's standard `User` model. we create user with Filament's built-in command:
```shell
php artisan filament:user
```
This command will create a new user and  mark them as an "admin" who can access the panel.   
Once done, we can start start the server:
```shell
php artisan serve
```
Now, if we visit `http://127.0.0.1:8000/admin/` in our browser and log in with our new superuser credentials, we will see the basic admin dashboard.
### Creating Resources
Filament admin dashboard is empty by default it doesn't show any models until we explicitly create a resource for them, to fix this we create resources for our models.  
A Resource is a PHP class that defines the entire admin interface for one model (the list, create, edit, and delete pages).

Let's start with the Todo resouce, we generate is using the following command:
```shell
php artisan make:filament-resource Todo
```
This command creates a new `app/Filament/Resources/Todos` directory and, inside it files that configure the panel that manage  our Todo.   
If we reload the admin site, we will now see "Todos" listed in the sidebar. 
### Customizing the Admin Interface
The default admin is functional, but we can customize it to be much more powerful. Let's use a new, more complex example a `library` to see Filament's power.

First, let's create the app and models, we will need three models `Publisher`, `Author` and `Book`.  
```shell
php artisan make:model Publisher -m
php artisan make:model Author -m
php artisan make:model Book -m
```
#### Setting the Models
Now we set the models:
- A publisher has a `name` and `address`, and it can publish one or many books, one-to-many relationship.
- An author has a `first_name` and `last_name`, and can be associated with many books many-to-many relationship.
- A book belongs to a publisher and can have multiple authors. It has:
    - `id`
    - `publisher_id`
    - `publish_date`
    - `available` (availability status)

We start with the Publisher model inside it we define one-to-many relationship using `hasMany()`, meaning a single publisher can publish multiple books.   
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
The Author model uses `belongsToMany()` to represent a many-to-many relationship, since an author can write multiple books and a book can have multiple authors.   
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
Finally, the Book model defines two relationships:
- `belongsTo(Publisher::class)` a book belongs to one publisher
- `belongsToMany(Author::class)` a book can have multiple authors

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
#### Editing the Migration file
We also edit the migration files so we set the structure of our tables.  
**`database\migrations\x_x_x_create_publishers_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->timestamps();
        });
    }

    public function down(): void{
        Schema::dropIfExists('publishers');
    }
};
```
**`database\migrations\x_x_x_create_authors_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();

        });
    }
    
    public function down(): void{
        Schema::dropIfExists('authors');
    }

};
```

**`database\migrations\x_x_x_create_books_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
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

    public function down(): void{
        Schema::dropIfExists('books');
    }

};
```
Here we created two tables.
- The `books` table stores information about each book, including an auto-incrementing `id`, `title` (up to 200 characters), a foreign key `publisher_id` referencing the `publishers` table (with cascade on delete), `publish_date` as a date, `available` as a boolean, and `timestamps` to track creation and updates.
- The `author_book` table is a junction (pivot) table to handle the many-to-many relationship between authors and books. It contains an auto-incrementing `id`, `author_id` and `book_id` as foreign keys, both with cascade on delete, ensuring that related records are removed if an author or book is deleted.

After defining our models we need to run `php artisan migrate` to apply them on our database
#### Creating Resources for the Models
To make these models appear in the admin, we generate a resource for each:
```shell
php artisan make:filament-resource Author
php artisan make:filament-resource Publisher
php artisan make:filament-resource Book
```
Now, if we refresh the admin panel, we will see Books, Authors, and Publishers listed. Filament does not automatically generate forms for the fields. We must customize the resources.
#### Editing the Authors Interface 
We edit the `ModelForm.php` file inside the `Schemas` directory, This file responsible for the Form that handel creating and editing records.
Let's edit the `Authors\Schemas\AuthorForm.php` we set the configue method to return two components of type ``TextInput`` both of them are required and take as max 200 character.     
**`app\Filament\Resources\Authors\Schemas\AuthorForm.php`**
```php
<?php
namespace App\Filament\Resources\Authors\Schemas;
namespace App\Filament\Resources\Books\Schemas;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuthorForm{
    public static function configure(Schema $schema): Schema{
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
After that we need to set how our admin panel display the list of authors for this we use `Tables/AuthorsTable.php` file.    
We define which columns, filters, and actions appear in the admin table for authors.
- ``columns``: This defines the actual data displayed.
    - ``id``: We use ``sortable()`` here so that the admin can order authors by their database ID.
    - ``first_name``: We give this a custom label('First Name') for better readability. It is both ``sortable()`` and ``searchable()``, meaning we can click the header to sort A-Z or use the top search bar to find a specific first name.
    - ``last_name``: Similar to the first name, this is labeled, sortable, and searchable. This allows for flexible searching.

- ``filters``: This is a list of specific criteria used to narrow down the table (for example, a dropdown to show only authors with a specific status).
- ``recordActions``: These are buttons that appear on each individual row.
    - ``EditAction``: This provides a button on every row that takes the admin directly to the edit page for that specific author.

- ``toolbarActions``: these appear when an admin selects one or more checkboxes on the left side of the table.
    - ``BulkActionGroup``: This wraps multiple actions into a single "Bulk Actions" dropdown menu to keep the interface clean.
    - ``DeleteBulkAction``: This allows the admin to delete multiple authors at once rather than clicking "delete" on every single row individually.


**`app\Filament\Resources\Authors\Tables\AuthorsTable.php`**
```php
<?php

namespace App\Filament\Resources\Authors\Tables;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AuthorsTable{
    public static function configure(Table $table): Table{
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
#### Editing the Books Interface 
Lets edit not the books interface so we can create and edit books we edit the `BookForm.php`

1. `TextInput::make('title')`
    - This is a standard text input field for the book's title.
    - `->required()`: The user cannot submit the form without filling in a title.
    - `->maxLength(200)`: The user cannot type more than 200 characters.
2. `Select::make('publisher_id')`
    - This is a dropdown menu to select the book's publisher.
    - `->relationship('publisher', 'name')`: It automatically gets its options from the `publisher` relationship on your `Book` model, using the `name` column for the display text.
    - `->searchable()`: The user can type into the dropdown to find a publisher quickly.
    - `->required()`: The user must select a publisher.
3. `Select::make('authors')`
    - This is another dropdown, this time for the book's author(s).
    - `->relationship('authors', 'first_name')`: It gets its options from the `authors` relationship.
    - `->multiple()`: This allows the user to select _more than one_ author for a single book.
    - `->preload()`: This loads all authors as soon as the page loads.
4. `DatePicker::make('publish_date')`
    - This provides a simple calendar popup for the user to select the book's publication date.
5. `Toggle::make('available')`
    - This is a simple on/off "switch" component.
    - `->required()`: The user must set this to either "on" or "off."


**`app\Filament\Resources\Books\Schemas\BookForm.php`**
```php
<?php
namespace App\Filament\Resources\Books\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;

class BookForm{
    public static function configure(Schema $schema): Schema{
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
We also set fix the `BooksTable.php` file.   
- We start with the `title` column and make it `searchable`, allowing users to quickly find a book by typing its title.
- Next, we add the `publisher.name` column. This uses the `publisher` relationship to display the publisher's `name` and makes the column `sortable`.
- We then add a `TagsColumn` for `authors.first_name`. This displays each related author's first name as a separate "tag" or "pill" and gives the column a clean `label` of "Authors".
- We add the `created_at` column. It's formatted as a `dateTime`, is `sortable`, and is `toggleable`, meaning it's hidden by default (`isToggledHiddenByDefault: true`) but can be shown by the user.
- The `updated_at` column is added with the exact same settings: formatted as a `dateTime`, `sortable`, and hidden by default.
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

class BooksTable{
    public static function configure(Table $table): Table{
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
#### Editing The Publisher
Finally, just like we did for the other modules, we set up the Publisher form and table.
### Relation Managers
One of Filament's most powerful features is the ability to manage related data directly from a parent model's edit page.  
For example, when editing a `Publisher`, it's highly efficient to see, add, and edit all of that publisher's `books` on the same screen. Filament achieves this using Relation Managers.  
To enable this we first generate the necessary class using an Artisan command. By running
```shell
php artisan make:filament-relation-manager PublisherResource books title
```
Here we create a new manager for the `PublisherResource` that will manage the `books` relationship, using the `title` column as a key field in its table. This command generates a new file located at `app/Filament/Resources/PublisherResource/RelationManagers/BooksRelationManager.php`

Once the file is created, we open the `app/Filament/Resources/PublisherResource.php` file and add our new `BooksRelationManager::class` to the `getRelations()` method. With this in place, we can edit publishers and their books in the same place

**`app/Filament/Resources/PublisherResource.php`**:
```php

class PublisherResource extends Resource{
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
Beyond managing individual models, Filament provides extensive control over the entire admin interface, allowing us to tailor its branding, colors, and behavior. This global customization is centralized in a single file created during installation, the `AdminPanelProvider.php`, which is typically located in `app/Providers/Filament`. Inside this class, we modify the main `Panel` object within its `panel()` method.   
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
- the `brandName()` method set a custom header for our admin panel.
- the `title()` method to change the browser tab's title.
- the `colors()` method define the entire color schema,
