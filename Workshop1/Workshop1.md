## Objectives
- What is Backend Development?
- Overview of the Laravel Framework
- Laravel’s Project Structure and Philosophy
- Creating Our First Project

## What is Backend Development?
When we browse a website, book a flight, or use a mobile app, our interaction is with the front-end. We see the buttons, the text, and the images beautifully arranged on our screen. This is the visual part of the application, often called the client-side. But have you ever wondered what happens when you click that "Login" button or "Buy Now"?  

![image](image.jpg)  


Behind the scenes, there's a whole other world working tirelessly to make that experience possible. This is the backend, also known as the server-side. It's the engine of the application, the part that you don't see but that does all the heavy lifting. When you click "Login," a specific part of the backend takes over to manage authentication and security. It's responsible for everything related to who you are and what you're allowed to do. These responsibilities include:

- Storing and Managing Users: When you create an account, the backend takes your email and password, securely hashes the password, and saves it in a database.
- Handling Authentication Logic: The backend is the brain of the security operation. It processes login requests, performs complex checks, and enforces the rules. For example, when you try to log in, it's the backend that checks if your username and password are correct.
- Managing Sessions: The backend has a constant conversation with your browser. After you log in, it gives you a "ticket" (like a session cookie or an API token) and checks that ticket on every subsequent request to keep you logged in.
- Controlling Access (Authorization): A critical role of the backend is to keep everything secure. It controls who has access to what information (e.g., "Is this user an admin?" or "Does this user own this blog post?").  

## Overview of the Laravel Framework
### First, What is Framework
Framework is a collection of pre-written code, tools, and conventions that provides a foundation for building an application. Think of it as a toolkit and a blueprint rolled into one. It handles repetitive tasks like hashing passwords, managing sessions, and protecting routes, freeing developers to focus on their application's unique features.
### What is Laravel and How Do Its "Guards" Help Us?
Laravel is a high-level PHP web framework designed for rapid development and expressive, elegant syntax. Born to make PHP development more enjoyable and productive, Laravel was built to power complex, database-driven websites with ease. Its primary goal is to simplify the repetitive, low-level tasks of web development.
### Batteries-Included and Driver-Based
Laravel is often described as Batteries-Included framework, it comes packed with nearly everything you need to build a web app right out of the box. It includes tools for session-based login (the `web` guard), API token authentication (like Laravel Sanctum), an Object-Relational Mapper (ORM) called Eloquent for seamless database interactions.  
### Who Uses Laravel?
Laravel isn’t just for small projects it’s a powerful framework trusted by major organizations and startups to handle high-traffic, complex applications. Some notable examples include:
- 9GAG: The popular social media platform for user-generated content leverages Laravel for its backend.
- TourRadar: A major online marketplace for booking multi-day tours.
- Barchart: A leading provider of market data and financial technology solutions.
- Flarum: A popular, modern open-source forum software built with Laravel.
## Laravel’s Project Structure and Philosophy
Now that we understand what Laravel is and why it’s so popular, let’s take a closer look at how Laravel organizes a project and the philosophy behind that structure.
### The MVC Pattern
Before exploring Laravel’s structure, it’s helpful to understand the widely used software design principle it's built on: MVC, or Model-View-Controller. This pattern organizes an application into three interconnected components, each with a specific role:
#### Model:
The model represents the data and the business logic for managing it. It defines the structure of our database and handles how data is stored, retrieved, and updated.
- Example: A `User` model might define fields like `username`, `email`, and `password`, along with logic for validating or querying that data.
#### View:
The view is the user interface, what users see and interact with. It’s responsible for displaying data from the models, typically as HTML pages.
- Example: A view might display a user’s profile page with their name and email.
#### Controller:
The controller acts as the glue between the model and view. It processes user input, interacts with the model to fetch or update data, and then passes that data to the appropriate view to be rendered.
- Example: When a user visits `/profile/1`, the controller fetches `User` with id 1 from the model and passes that user's data to the view.  
### Laravel’s MVC Architecture
Laravel is a true MVC framework. It follows this pattern closely, with a clear separation of concerns that is reflected in its directory structure. Here’s how MVC breaks down in a typical Laravel application:
#### Model:
Just like in standard MVC, the model defines the data structure and manages database interactions. Laravel’s models are PHP classes, typically found in `app/Models`, that use the Eloquent Object-Relational Mapping (ORM) system. Each model class maps to a database table, allowing us to query and interact with our data using simple, expressive PHP syntax instead of writing raw SQL.
- Example: A `BlogPost` model might have `title`, `content`, and `publish_date` properties and relationships to a `User` model.
#### View:
The view is the presentation layer, responsible for rendering the HTML that the user sees in their browser. Laravel uses the Blade templating engine for its views, which are stored in `resources/views`. Blade allows us to write clean HTML mixed with simple PHP-like directives (e.g., `@if`, `@foreach`) to display dynamic data, use layouts, and include partials.
- Example: A `blog.blade.php` template might loop through a list of blog posts passed from the controller to display their titles and summaries.
#### Controller:
In Laravel, the controller is a PHP class (in `app/Http/Controllers`) that contains the business logic. It handles incoming HTTP requests, retrieves data from Models, processes user input, and tells the application what to do next.

In Laravel, the "traffic cop" that connects a URL to a controller method is the Router. These definitions are stored in the `routes/` directory (e.g., `routes/web.php`). We explicitly define which URL (like `/blog`) should be handled by which method on which controller (like `BlogController@index`).
### Laravel’s Project Structure
Laravel organizes projects in a structured, conventional way to keep code organized and scalable. When we create a new Laravel project, it generates a specific set of files and folders, each with a clear purpose.
#### Creating a Laravel Project
To start a new Laravel project, we use Composer (PHP's package manager). Assuming we have Composer installed, we can create a project by running:
```shell
composer create-project laravel/laravel myproject
```
This command creates a folder named `myproject` with the following structure:
```
myproject/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── .env
├── artisan
└── composer.json
```
Let’s break down the most important files and directories:
#### `artisan`:
A command-line utility for interacting with our Laravel project. We use it to run the development server (`php artisan serve`), create database tables (`php artisan migrate`), generate new classes (`php artisan make:controller`), and perform other administrative tasks.
#### `.env`:
The environment configuration file. This is where we store all our project's "secrets" and environment-specific settings, like database credentials, API keys, and app debug settings.
#### `app/`:
This is the heart of our application. It contains our core PHP code, including:
- `app/Models`: Where our Eloquent data models live.
- `app/Http/Controllers`: Where our controllers live.
- `app/Providers`: Service providers that bootstrap our application.
- ...and any other business logic we write.

#### `config/`:
Contains all of our project’s configuration files (e.g., `config/database.php`, `config/app.php`). These files pull their values from the `.env` file, allowing us to have different settings for different environments.
#### `database/`:
Holds our database-related files:
- `database/migrations`: Files that define our database table structure, allowing us to version-control our database schema.
- `database/seeders`: Files to populate our database with test or default data.
- `database/factories`: Used to generate fake data for testing.
#### `public/`:
This is the web server root and the only folder that should be accessible from the internet. It contains the `index.php` file and our compiled assets (CSS, JavaScript, images).
#### `resources/`:
This folder contains our "raw" front-end files:
- `resources/views`: All our Blade template files.
- `resources/css`, `resources/js`: Our uncompiled CSS and JavaScript source files.
- `resources/lang`: Language files for multi-language support.
#### `routes/`:
The URL dispatcher. This folder contains our route definitions.
- `routes/web.php`: Defines all routes for our main web interface.
- `routes/api.php`: Defines routes for our stateless API.
#### `storage/`:
A folder for files generated by the application, such as logs (`storage/logs`), file uploads (`storage/app/public`), and cache files.
### Laravel's Component-Based Structure
Laravel encourages us to organize our code using component-based structure. All our models go in the `app/Models` folder, all controllers in `app/Http/Controllers`, and so on.   
This provides a very clear separation of concerns out of the box. we generate the individual components we need using `artisan` commands:
```shell
# Create a new Eloquent model
php artisan make:model BlogPost

# Create a new controller
php artisan make:controller BlogPostController --resource

# Create a new database migration
php artisan make:migration create_blog_posts_table
```
These commands will place the new files in their correct, conventional locations:
- `app/Models/BlogPost.php`
- `app/Http/Controllers/BlogPostController.php`
- `database/migrations/xxxx_xx_xx_xxxxxx_create_blog_posts_table.php`

### Why This Structure is Useful
Laravel’s project structure promotes clarity and convention over configuration. By providing a logical, default location for every type of file, it makes it easy to:
- Maintain: We always know where to find a specific type of file.
- Collaborate: The consistent structure means other Laravel developers can quickly understand and contribute to our project.
- Scale: For larger projects, Laravel is flexible. Many developers create feature-specific sub-directories within `app/` (e.g., `app/Blog/Post.php`, `app/Blog/PostController.php`) to group related code.
- Test: The separation of concerns makes it much easier to write automated tests for individual components.
## Creating Our First Project
Now that we understand the structure of a Laravel project, let’s build our first application. This section will guide you through setting up your environment and installing Laravel.
### Environment Configuration
The PHP ecosystem uses Composer as its primary dependency manager. Composer can manage packages on a per-project basis, but the tool itself is typically installed once on your system.   
We first need to install php by following the guide from [PHP](https://www.php.net/), for windows we can install PHP by running the following command
```powershell
# Download and install Chocolatey.
powershell -c "irm https://community.chocolatey.org/install.ps1|iex"

# Download and install PHP.
choco install php 
```
After the installation we need to edit the configuration of our ``php.ini`` file so it can work with composer, we do that by oppening  ``php.ini`` using
```
code C:\tools\php84\php.ini
```
Then we search for `;extension=fileinfo` ,`;extension=pdo_sqlite` and `;extension=sqlite3` we remove from their line the ``;``.   

With that set we ready to install composer, we can get it from  [getcomposer.org](https://getcomposer.org/) .
### Creating Laravel Project
With Composer installed, we can create a new Laravel project using the following command:
```shell
composer create-project laravel/laravel hello_world
```
This command:
1. Tells Composer to `create-project`.
2. Specifies the `laravel/laravel` package.
3. Creates a new directory named `hello_world` and installs Laravel and all its dependencies inside it.


Once it's finished, we navigate into our new project directory:
```shell
cd hello_world
```
We are now in the root of our Laravel project, where we can see the `artisan` file, the `app` directory, and all the other files we discussed.
### Building Our Website
Now that we’ve installed Laravel, it’s time to start building our first app a simple "Hello World" application to get familiar with how Laravel works.
#### Configuring Our First Route
To make our Laravel app accessible through the web, we need to set up URL routing. Routing is the process of defining which part of our application should handle a specific URL request.   
In Laravel, web routes are defined in a single file: `routes/web.php`. Let's open that file and add a route.
**`hello_world/routes/web.php`**
```php
<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

```
This is the default route that laravel set for us it return the welcome view, lets add new route `/hello` that display `Hello, World!`.

**`hello_world/routes/web.php`**
```php
<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return 'Hello, World!';
});
```
Let's break down that new line:
- `Route::get(...)`: This tells Laravel to listen for an HTTP GET request.
- `'/hello'`: This is the URL pattern it should match (The endpoint).
- `function () { ... }`: This is a Closure an anonymous function that contains the logic that will run when a user visits `/hello`.
- `return 'Hello, World!';`: This simply returns the plain text string "Hello, World!" as the HTTP response.

This method is quick for simple routes, but for more complex logic, we should use a Controller.
#### Creating the App's Controller
We'll use Artisan  to create a new controller:
```shell
php artisan make:controller HelloController
```
This command creates a new file at `app/Http/Controllers/HelloController.php`. Let's open that file:

**`hello_world/app/Http/Controllers/HelloController.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    //
}
```
We can see that it create empty `HelloController` Class for us that extand from the `Controller` Class, this class will be our controller, inside it we define methods that we want to run in our route, We create public method `showHello` which return 'Hello, World!'.   
**`hello_world/app/Http/Controllers/HelloController.php`**
```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    // Create this new method
    public function showHello()
    {
        return 'Hello, World!';
    }
}
```
#### Connecting the Route to the Controller
Now, let's go back to `routes/web.php` and tell our route to use this new controller method instead of the closure.  
**`hello_world/routes/web.php`**
```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloController; // 1. Import the controller

/* ... */

Route::get('/', function () {
    return view('welcome');
});


Route::get('/hello', [HelloController::class, 'showHello'])->name('hello');
```
Let's break down this new line:

1. We first add `use App\Http\Controllers\HelloController;` at the top of the file to import our new class.
2. After that we edited `Route::get('/hello', ...)`, the second argument it take now is `[HelloController::class, 'showHello']` which tells Laravel: "When this route is hit, find the `HelloController` class and run its `showHello` method."
3. Finally we added `->name('hello')` which assigns a symbolic name to the route. This lets us refer to this route by name in other parts of our application without having to hard-code the URL.
#### Running the Development Server
Now that our route and controller are ready, it’s time to test everything by running Laravel’s built-in development server.  
From the `hello_world` project directory , open your terminal and run:
```shell
php artisan migrate 
```
this create the database migration then after it we run:
```shell
php artisan serve
```
This command starts Laravel's development server, which runs by default on `http://localhost:8000`. The terminal will confirm the server is running.  
Open your web browser and visit `http://localhost:8000/hello`. You should see the message “Hello, World!” displayed on the page. This confirms that your app is correctly configured.
### Working with Dynamic URLs and Parameters
We’ve successfully built an app that displays “Hello, World!”. But right now, it's static. Let’s make our app more dynamic by customizing responses based on URL parameters.  
#### Using Dynamic URLs 
Dynamic URls are special urls (endpoints) that have variables part inside them, Let's improve our app to use dynamic urls to greet users by their name. For example, visiting `/hello/Alice` would show “Hello, Alice!” and `/hello/Bob` would show “Hello, Bob!”.  
First let's create new method in our controller to handel the dynamic urls, this method will have ``$name`` as parameter, and return "Hello, {$name}!"
**`hello_world/app/Http/Controllers/HelloController.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    public function showHello()
    {
        return 'Hello, World!';
    }

    // Add this new method
    public function personalGreeting($name)
    {
        return "Hello, {$name}!";
    }
}
```
Finally, we update our ``routes/web.php`` file and add a dynamic URL. To define a dynamic route parameter, we use ``hello/{name}``. This tells Laravel that any value appearing after ``hello/`` should be captured and passed to the controller method as an argument for the ``$name`` parameter.  
Inside {} we can use any name we want as laravel passes route parameters to the controller by position, but for clarity and convention we use same name as the one we defined in our controller method.  
**`hello_world/routes/web.php`**
```php

// Add our new dynamic route
Route::get('/hello/{name}', [HelloController::class, 'personalGreeting'])
     ->name('personal_greeting');
```
#### Query Parameters
Query parameters are special keys values that come at the end of urls, they come after the `?`, Unlike route parameters, they are optional and handled inside the controller using Laravel's `Request` object.     
Let’s improve this even more, and apply the query parameters to give the user the ability to change the greeting message itself? For example, visiting: `http://localhost:8000/hello/Alice?greet=Welcome`. should display: `Welcome, Alice!`.  
 
We start by modify our `personalGreeting` method to use them.
First we add `use Illuminate\Http\Request;` so we will have access to the request object.After this in our method we type-hint the `Request` object in the method's parameters: by adding to the parameter `(Request $request, $name)`. Laravel's service container automatically sees this and injects the current HTTP request object for us. Finally inside our method we access to the query parameter `greet` by using `$request->query('greet', 'Hello')`. If it’s not provided, it will be set to “Hello”.  
**`hello_world/app/Http/Controllers/HelloController.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    public function showHello()
    {
        return 'Hello, World!';
    }

    public function personalGreeting(Request $request, $name)
    {
        
        $greeting = $request->query('greet', 'Hello'); 
        return "{$greeting}, {$name}!";
    }
}
```
Now, see it in action:
- Visit `http://localhost:8000/hello/Alice` give us "Hello, Alice!".
- Visit `http://localhost:8000/hello/Alice?greet=Welcome` give us "Welcome, Alice!".
- Visit `http://localhost:8000/hello/Bob?greet=Good%20Morning` give us "Good Morning, Bob!".
