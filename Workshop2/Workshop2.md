## Objectives
- Rendering and Returning Views    
- The Blade Template Engine
- Managing and Serving Static Files
- Handling User Input with Forms
## Rendering and Returning Views
In our previous session, we returned simple strings (like "Hello, World!") from our routes and controllers. While perfect for simple tests or APIs, most web applications need to display rich, structured content. To achieve this, we use HTML templates, which Laravel calls Views.

A view is an HTML file where we can embed dynamic data before sending it to the user's browser. This approach keeps our application's logic separate from its presentation, making our code cleaner and easier to maintain.
### Rendering Views
When we want to render and return an HTML template, we will use Laravel's `view()` helper function. The `view()` function compiles a Blade view file, combines it with data , and generates the final HTML.
### The `resources/views` Folder
By default, Laravel looks for views in a folder named `resources/views`. As a best practice, we should create a subfolder within this `views` directory that matches our feature's name (e.g., `app1`). This helps prevent view name conflicts.    
All Laravel view files must end with the `.blade.php` extension (e.g., `index.blade.php`). This tells Laravel that the file should be processed by the Blade templating engine.
### Creating The App
Now, let’s put this into practice, we will create new project using composer
```shell
composer create-project laravel/laravel workshop2
cd workshop2
```
Aftet that we create new controller to handel our app, In the terminal, run the Artisan command:
```shell
php artisan make:controller App1Controller
```
This creates a new file at `app/Http/Controllers/App1Controller.php`.

Next, let's set up the route in `routes/web.php` to point to our new controller.

**`routes/web.php`**
```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App1Controller; // Import the controller

/* ... other routes ... */

// Add this new route
Route::get('/app1', [App1Controller::class, 'index'])->name('app1_index');
```
With this setup, when we visit **`http://localhost:8000/app1`**, Laravel will route the request to the `index` method inside `App1Controller`.

Finally, let’s create that `index` method in our controller to return the `index.blade.php` view.   
**`app/Http/Controllers/App1Controller.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App1Controller extends Controller
{
    public function index()
    {
        // This will look for 'resources/views/app1/index.blade.php'
        return view('app1.index');
    }
}
```
The `index` function uses Laravel's `view()` helper to return an HTTP response containing the rendered HTML.

The `view()` function takes two main arguments:
1. **`view_name`**  The path to the view you want to render, relative to the `resources/views` directory, using **dot notation**.
2. **`data`** (optional) An associative array of data we want to pass to the view.

In our case, we used `view('app1.index')`. The dot (`.`) tells Laravel to look for the `index.blade.php` file inside the `resources/views/app1/` directory.
### Creating The View
Next, we’ll create our Blade view. Inside your `resources/views/` folder, we create a new subfolder named `app1`. and inside it we create the file `index.blade.php`.

**`resources/views/app1/index.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My First View</title>
</head>
<body>
    <h1>Welcome to Our Website!</h1>
    <p>This page was rendered from a Laravel Blade view.</p>
</body>
</html>
```
This simple blade template that will display when user visit ``http://localhost:8000/app1``
### Running The App
Now we can run our Laravel development server using:
```shell
php artisan serve
```

Once the server is running, open your browser and visit **`http://127.0.0.1:8000/app1`**. We should see the content of our `index.blade.php` view displayed on the page.
## The Blade Template Engine
With Laravel, we can do much more than just create and return static HTML views. Laravel includes a powerful template engine called Blade that allows us to build dynamic and reusable pages. Using Blade, we can insert variables, apply conditions, loop through data, and even define reusable layouts that other views can extend.

All Blade files use the `.blade.php` file extension and are stored in `resources/views`.
### Adding Variables
In Blade views, we can display dynamic data passed from the controller using a simple, readable syntax.   
For example, if we pass an array of data from our controller's method like this:

**`app/Http/Controllers/App1Controller.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App1Controller extends Controller
{
    public function index()
    {
        $data = [
            'username' => 'Alice',
            'age' => 25
        ];
        
        // Pass the $data array to the view
        return view('app1.index', $data);
        
        // An alternative 'compact' syntax:
        // $username = 'Alice';
        // $age = 25;
        // return view('app1.index', compact('username', 'age'));
    }
}
```
Here, we updated our `index` method to include a `$data` array that holds dynamic data. This array is passed as the second argument to the `view()` helper, which makes the keys (`username`, `age`) available as variables (`$username`, `$age`) inside the `index.blade.php` view.

We can then access these variables in our view using double curly braces (`{{ }}`), which also automatically protects against XSS attacks.

**`resources/views/app1/index.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My First View</title>
</head>
<body>
    <h1>Welcome {{ $username }}!</h1>
    <p>You are {{ $age }} years old.</p>
</body>
</html>
```
When rendered, Blade compiles this to PHP and replaces the variables with the actual values.

### Using Conditions
The Blade template engine also allows us to add conditions to our HTML views, making our pages more dynamic and responsive to data.    
We can use directives such as **`@if`**, **`@elseif`**, and **`@else`** to control what content is displayed based on specific conditions.
#### Example
Now, let’s create a new controller called **`App2Controller`** (`php artisan make:controller App2Controller`). We’ll set up its route in `routes/web.php`.

**`routes/web.php`**

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App2Controller;

// This route captures a 'role' from the URL
Route::get('/app2/{role?}', [App2Controller::class, 'index'])->name('app2_index');
```
We added the `/app2/{role?}` route. Here, `{role?}` means it's an optional parameter, so when the user visits `/app2`, our controller will use the default value for the parameter. When the user adds a role to the URL, it will be used as the actual value.    
After that, we’ll configure the `index` method to display different messages based on the dynamic URL segment the user visits.

**`app/Http/Controllers/App2Controller.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App2Controller extends Controller
{
    public function index($role = "")
    {
        return view('app2.index', [
            'role' => $role
        ]);
    }
}
```
The controller have default value for the role parametre so if user visit `/app2` and don't provide the role value the controller will use this default value else it will capture the value from the url and use it, we passing then the role to our template engine, so we load different data depending on the value of `$role`.    
Finally lets create our view and display a different message depending on the role.  
**`resources/views/app2/index.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App 2</title>
</head>
<body>
    @if ($role == 'admin')
        <h1>Welcome, Admin!</h1>
        <p>You have full access to the system.</p>
    @elseif ($role == 'editor')
        <h1>Welcome, Editor!</h1>
        <p>You can edit and manage content.</p>
    @elseif ($role == 'viewer')
        <h1>Welcome, Viewer!</h1>
        <p>You can browse and read the available content.</p>
    @else
        <h1>Welcome, Guest!</h1>
        <p>Your role is not recognized. Please log in or contact the administrator.</p>
    @endif
</body>
</html>
```
Here we are using Blade’s `@if`, `@elseif`, and `@else` directives to display different messages based on the value of the `$role` variable.    
When a user visits a URL like `/app2/admin` or `/app2/viewer`, Blade will render the appropriate message dynamically.
### Using Loops
The Blade template engine also allows us to loop through data in our HTML views. This is especially useful for displaying lists or collections of items.     
We can use the **`@foreach`** directive to iterate over data passed from our controller.
#### Example
Now, let’s create a new controller called **`App3Controller`**. We’ll set up its route and add it to `routes/web.php`.

**`routes/web.php`**

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App3Controller;

Route::get('/app3', [App3Controller::class, 'index'])->name('index');
```
Next, we’ll configure the `index` method to pass a list of items to our view.

**`app/Http/Controllers/App3Controller.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App3Controller extends Controller
{
    public function index()
    {
        $data = [
            'fruits' => ['Apple', 'Banana', 'Cherry', 'Mango', 'Orange']
        ];
        return view('app3.index', $data);
    }
}
```
Now let’s create our view and display the list dynamically using a loop. A common and convenient directive for this is **`@forelse`**, which combines a loop with an empty-state check.

**`resources/views/app3/index.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App 3 - Using Loops</title>
</head>
<body>
    <h1>Available Fruits</h1>
    <ul>
        @forelse ($fruits as $fruit)
            <li>{{ $fruit }}</li>
        @empty
            <li>No fruits available at the moment.</li>
        @endforelse
    </ul>

    <p>Total fruits: {{ count($fruits) }}</p>
</body>
</html>
```
Here, we’re using the Blade `@forelse` directive:
- It loops through each item in the `$fruits` array.
- The `@empty` directive defines what should be shown if the `$fruits` array is empty.
- `@endforelse` closes the block.


Finally, we used the standard PHP `count()` function to display the total number of fruits.
### Template Inheritance 
In larger projects, many pages share the same layout (header, navigation, footer). Instead of repeating HTML, Blade allows us to create a **base layout** and let other views **extend** it.     
It's a common convention to store layouts in a `resources/views/layouts` directory.

**`resources/views/layouts/app.blade.php`** 
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My App</title>
</head>
<body>
    @yield('content')
</body>
</html>
```
In this file, we define a "slot" or "placeholder" called `content` using the `@yield('content')` directive. This tells Blade, "Other views that extend this one can insert their content here."    
Now, we can update our `index.blade.php` to _extend_ this layout.

**`resources/views/app1/index.blade.php`** 
```html
@extends('layouts.app')

@section('content')
    <h2>Welcome to the Home Page!</h2>
    <p>This content is unique to the index page.</p>
@endsection
```
- The `@extends('layouts.app')` directive tells Blade to use our base layout.
- The `@section('content') ... @endsection` block defines the HTML that will be injected into the `@yield('content')` placeholder in the base layout.
### Including Template Parts 
Blade also allows us to include smaller, reusable components, like a navigation bar or footer, using the **`@include`** directive.     
It's common to place these in a `components` or `partials` folder.

**`resources/views/app1/components/navbar.blade.php`**
```html
<nav>
    <a href="#">Home</a> |
    <a href="#">About</a> |
    <a href="#">Contact</a>
</nav>
```
**`resources/views/app1/components/footer.blade.php`**
```html
<footer>
    <p>&copy; 2025 My Website</p>
</footer>
```
Here we created footer and navbar compenontes,  we include them in our `app.blade.php` base layout:

**`resources/views/layouts/app.blade.php`**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My App</title>
</head>
<body>
    <header>
        <h1>My Website</h1>
        @include('app1.components.navbar')
    </header>

    <main>
        @yield('content')
    </main>

    @include('app1.components.footer')
</body>
</html>
```
Here, the `@include()` directives tell Blade to load and render those smaller templates at the specified locations. This component-based approach helps keep our templates modular, clean, and easy to maintain.
## Managing and Serving Assets 
Real web applications need styles, images, and sometimes videos to enhance the user experience. These files, which don’t change dynamically, are called assets or static files.   
Laravel provides a simple and efficient way to manage and serve these assets (like CSS, JavaScript, and images) from a single, dedicated directory.
### Setting Up Assets in the `public` Folder
In Laravel, all static assets (CSS, JavaScript, images, etc.) are placed inside the `public` directory. This folder is the web server's "document root," meaning any file here is directly accessible via a URL.    
Let’s set up a folder structure to organize our assets

**Folder structure:**
```
public/
│
├── css/
│   └── style.css
│
└── images/
    └── logo.png

```
- Files in `public/css/` will be accessible at URLs like `http://localhost:8000/css/style.css`.
- Files in `public/images/` will be accessible at `http://localhost:8000/images/logo.png`.
### Creating the Stylesheet
Next, let’s create a CSS file that will define the styles for our page, we’ll name it `style.css` and place it inside the **`public/css/`** folder.

**`public/css/style.css`**
```css
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    text-align: center;
    margin: 50px;
}

h1 {
    color: #2c3e50;
}

img {
    width: 150px;
    margin-top: 20px;
}

input, textarea {
    width: 300px;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}
textarea {
    height: 100px;
}
button {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}  
```
This stylesheet will control the appearance of our webpage, setting a clean background, centering content, and styling the heading and image.
### Building the Blade View
Now that our assets are in the `public` folder, it’s time to use them in a Blade view. We’ll use Laravel's `asset()` helper function, which generates a full, correct URL to any file in the `public` directory.    
**`resources/views/app4/index.blade.php`**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App 4 - Static Files</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
</head>
<body>
    <h1>Welcome to App 4</h1>
    <p>This page demonstrates how to use assets in Laravel.</p>
    
    <img src="{{ asset('images/logo.png') }}" alt="App 4 Logo">
</body>
</html>
```
In this template, we use the **`{{ asset(...) }}`** function to reference our CSS file and image.
- This helper function generates the correct path to the file. For example, `{{ asset('css/style.css') }}` will be rendered as: `http://127.0.0.1:8000/css/style.css`
- This ensures our links always work, even if our application is in a subdirectory on a production server.
### Creating the Controller Method
Now, let's connect everything. We need a controller method to render the `index.blade.php` view.

First, create the controller with Artisan:
```
php artisan make:controller App4Controller
```
After that edit the new file to add an `index` method.

**`app/Http/Controllers/App4Controller.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App4Controller extends Controller
{
    public function index()
    {
        // This returns the view at resources/views/app4/index.blade.php
        return view('app4.index');
    }
}
```
### Configuring the Route
Finally, to make our controller method accessible, we need to define a URL pattern for it in our `routes/web.php` file.

**`routes/web.php`**
```php
<?php

use Illuminate\Support\Facades\Route;
// Import all your controllers at the top
// ...
use App\Http\Controllers\App4Controller;

/* ... */

Route::get('/app4', [App4Controller::class, 'index']);
```
Here, we’ve mapped the `/app4` URL directly to the `index` method of our new `App4Controller`.   
Now, when we run `php artisan serve` and visit `http://127.0.0.1:8000/app4`, Laravel will execute the `index` method, render our Blade view, and our browser will correctly load the CSS and image from the `public` folder.

## Handling User Input with Forms
So far, our Laravel apps have focused on displaying data to users rendering Blade views, managing assets, and serving dynamic content. However, real-world web applications also need to receive data from users, process it, and often save it or use it to produce a result.

The most common way to collect user input is through HTML forms. Laravel provides robust support for handling forms, including seamless request handling, CSRF protection, and powerful validation classes.
### Create a Feedback "Feature"
Let’s create a new feature that allows users to submit their feedback. for that we need to create new Controller and a set of Routes and Views.

First, create the controller using Artisan:
```shell
php artisan make:controller FeedbackController
```
This creates a new file at `app/Http/Controllers/FeedbackController.php`. 
### Creating The Templates
After setting up our controller, it’s time to create the Blade views that will handle user interaction and data display. We’ll need two views, which we'll place in a new `resources/views/feedback/` directory.
1. One for the feedback form.
2. Another for displaying submitted feedback.

**`resources/views/feedback/form.blade.php`**
```html
@extends('layouts.app')

@section('content')
    <h1>We Value Your Feedback</h1>

    <form method="POST">
        <input type="text" name="name" placeholder="Your Name" required><br><br>
        <input type="email" name="email" placeholder="Your Email" required><br><br>
        <textarea name="message" rows="5" placeholder="Your Feedback" required></textarea><br><br>
        <button type="submit">Submit</button>
    </form>
@endsection
```
This view displays a simple HTML form. When the user submits, the data will be sent to the server using the `POST` method and handled by our `FeedbackController`.

**`resources/views/feedback/feedbacks.blade.php`**
```html
@extends('layouts.app')

@section('content')
    <h1>Submitted Feedback</h1>
        @forelse ($feedback_list as $item)
            <div>
                <div><strong>{{ $item['name'] }}</strong> ({{ $item['email'] }})</div>
            <div>{{ $item['message'] }}</div>
            </div>
            <hr>
        @empty
            <div>No feedback has been submitted yet.</div>
        @endforelse
@endsection
```
This template loops through the list of submitted feedback entries using Blade’s `@forelse` directive. If no feedback exists, the `@empty` section displays a default message.
### Creating the Controller Logic
Now let’s define the logic in our controller. It will perform two main tasks:
1. Display the feedback form (`GET` request).
2. Handle the form submission (`POST` request) and display all feedback.

Because PHP processes requests and then "forgets" everything, we need to set the Session to store the feedbacks.

**`app/Http/Controllers/FeedbackController.php`**
```php
<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
class FeedbackController extends Controller
{

    public function submitFeedback(Request $request){
        if ($request->isMethod('POST')) {
            $name = $request->input('name');
            $email = $request->input('email');
            $message = $request->input('message');

            $feedbacks = $request->session()->get('feedbacks', []);
            $feedbacks[] = ["name" => $name, "email" => $email, "message" => $message];

            $request->session()->put('feedbacks', $feedbacks);
            return redirect()->route('feedbacks');
        }
        
        return view('feedback.form');
    }


    public function feedbackList(Request $request){
        $feedbacks = $request->session()->get('feedbacks', []);
        return view('feedback.feedbacks', ['feedback_list' => $feedbacks]);
    }
}
```
Here we created two methods one to handel the form submission and the other one to display the the feedbacks
#### `submitFeedback(Request $request)`
This method handles the feedback form submission.
- First, it checks if the request method is `POST` using ``$request->isMethod('POST')``.
- Then it retrieves the values submitted by the user using `$request->input('name')`, `$request->input('email')`, and `$request->input('message')`.
- It gets the existing `feedbacks` array from the session (or uses an empty array if none exists yet).
- The new feedback entry is added to the array.
- The updated feedback list is stored back into the session using `$request->session()->put()`.
- Finally, it redirects the user to the route that displays the feedback list.
- If the request is not `POST`, it simply loads the feedback form view.
#### `feedbackList(Request $request)`
This method retrieves all saved feedback entries from the session and sends them to the `feedback.feedbacks` view to display them to the user.
### Setting the Routes
Finally, we need to configure the URL patterns in our `routes/web.php` file.

**`routes/web.php`**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;

// ... other routes ...

// Add our two routes for the feedback feature
Route::get('/feedback', [FeedbackController::class, 'submitFeedback'])
     ->name('submit_feedback');

Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);

Route::get('/feedback/list', [FeedbackController::class, 'feedbackList'])
     ->name('feedbacks');
```
- We point both `GET` and `POST` requests for `/feedback` to the same `submitFeedback` method, which then checks the request type internally.
- We add a named route `feedbacks` for the list page, which our controller uses for the redirect.

Now when we visit:
- `http://127.0.0.1:8000/feedback` we will see the feedback form.
- `http://127.0.0.1:8000/feedback/list` we will see all submitted feedback.

If we try to submit our form right now, our application will fail with a "419 Page Expired" error, this happens because Laravel, by default, enables CSRF protection for all POST requests. 
### What Is CSRF?
CSRF (Cross-Site Request Forgery) is a type of attack where a malicious website tricks a logged-in user into performing unwanted actions on another website where they’re authenticated.

Laravel includes built-in CSRF protection to prevent this by requiring a unique token to be sent with every `POST`, `PUT`, `PATCH`, or `DELETE` request. If the token is missing or invalid, Laravel rejects the request with a 419 error.
### Adding the CSRF Token
To fix our form, we need to include a CSRF token. We do this by adding the **`@csrf`** Blade directive inside the `<form>` tag.

Edit **`resources/views/feedback/form.blade.php`** like this:
```html
@extends('layouts.app')

@section('content')
    <h1>We Value Your Feedback</h1>

    <form method="POST">
         @csrf
        <input type="text" name="name" placeholder="Your Name" required><br><br>
        <input type="email" name="email" placeholder="Your Email" required><br><br>
        <textarea name="message" rows="5" placeholder="Your Feedback" required></textarea><br><br>
        <button type="submit">Submit</button>
    </form>
@endsection
```
Now, when we submit the form, Laravel will verify the token and safely accept our feedback.
### Using a Form Request for Validation
Our app works, but it lacks validation and will accept empty or invalid inputs if we remove the `required` attribute from the HTML.

To solve this, Laravel provides a powerful, structured way to handle forms: "Form Request" classes.

Form Requests help us to:
- Automatically validate user input.
- Authorize if a user is even allowed to make the request.
- Re-render the form automatically with error messages if validation fails.
- Cleanly separate validation logic from your controller.
### Creating a Form Request
We’ll begin by creating a new Form Request class using Artisan.
```shell
php artisan make:request StoreFeedbackRequest
```
This creates a new file at **`app/Http/Requests/StoreFeedbackRequest.php`**.
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'message' => 'required|string',
        ];
    }
}
```
This class extends the `FormRequest` class and contains two important methods.   
The first method, `authorize()`, checks whether the user is allowed to submit the request. We currently return `true` because we haven’t implemented an authentication system yet  we will do that in the next workshop.

The second method, `rules()`, defines the validation rules that the submitted form data must follow. It returns an associative array where each key represents an input field name, and each value is the validation rule for that field. For example, the `name` field is required, must be a string, and cannot exceed 100 characters.
### Updating the Controller Logic
Now let’s update our `FeedbackController` to use this new Form Request. This also lets us clean up our logic by splitting the GET and POST handling into two separate methods.

**`app/Http/Controllers/FeedbackController.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreFeedbackRequest; 

class FeedbackController extends Controller
{

    public function showForm()
    {
        return view('feedback.form');
    }

    
    public function storeFeedback(StoreFeedbackRequest $request)
    {
    
        $validatedData = $request->validated();
        
        $feedbacks = $request->session()->get('feedbacks', []);
        $feedbacks[] = $validatedData;
        $request->session()->put('feedbacks', $feedbacks);
            
        return redirect()->route('feedbacks');
    }

    public function feedbackList(Request $request)
    {
        $feedbacks = $request->session()->get('feedbacks', []);
        return view('feedback.feedbacks', ['feedback_list' => $feedbacks]);
    }
}
```
We splite our form method into two methods , ``showForm`` method will just return the view, the `storeFeedback` method to use our custom `StoreFeedbackRequest` class. By type-hinting `StoreFeedbackRequest` instead of the default `Request`, Laravel automatically runs the validation rules before the method executes. If the form data doesn't meet the validation rules, Laravel immediately redirects the user back to the form with error messages and the previously entered input we don't need to manually check anything. If the validation is successful, the controller continues and we retrieve the validated data using `$request->validated()`. We then store that clean data in the session and redirect the user to the feedback list page.
### Updating the Routes
Since we split our `submitFeedback` method into `showForm` and `storeFeedback`, we must update `routes/web.php`:

**`routes/web.php`**
```php
// ...
// Point GET to the 'showForm' method
Route::get('/feedback', [FeedbackController::class, 'showForm'])
     ->name('submit_feedback');

Route::post('/feedback', [FeedbackController::class, 'storeFeedback']);

Route::get('/feedback/list', [FeedbackController::class, 'feedbackList'])
     ->name('feedbacks');
```

### Updating the Template 
Finally, let’s edit our form template.  we will  use Blade directives to display errors and old input if form didn't pass validation.

**`resources/views/feedback/form.blade.php`**
```php
@extends('layouts.app')

@section('content')
    <h1>We Value Your Feedback</h1>

    <form method="POST" action="{{ route('submit_feedback') }}">
        @csrf
        
        <div>
            <label for="name">Your Name</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            
            @error('name')
                <div style="color: red;">{{ $message }}</div>
            @enderror
        </div>
        <br>
        <div>
            <label for="email">Your Email</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div style="color: red;">{{ $message }}</div>
            @enderror
        </div>
        <br>
        <div>
            <label for="message">Your Feedback</label><br>
            <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
            @error('message')
                <div style="color: red;">{{ $message }}</div>
            @enderror
        </div>
        <br>
        <button type="submit">Submit</button>
    </form>
@endsection
```
This template now fully supports validation:
- **`@csrf`** provides security.
- **`old('name')`** is a helper that gets the previous input value if validation failed.
- **`@error('name') ... @endrelease`** is a block that only renders if an error for the `name` field exists.
- **`$message`** is a special variable available inside the `@error` block that contains the error string (e.g., "The name field is required.").
