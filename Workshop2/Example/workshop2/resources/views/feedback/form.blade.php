<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Feedback</title>
</head>
<body>
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
</body>
</html>