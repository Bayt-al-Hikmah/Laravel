<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My App</title>
</head>
<body>
    <h1>My Website</h1>
    @include('app1.components.navbar')
    
    <main>
        @yield('content')
    </main>
    
    @include('app1.components.footer')
</body>
</html>