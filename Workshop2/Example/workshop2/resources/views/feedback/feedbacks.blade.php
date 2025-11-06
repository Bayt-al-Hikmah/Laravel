<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Feedback</title>
</head>
<body>
    <h1>Submitted Feedback</h1>
    <ul>
        @forelse ($feedback_list as $item)
            <li>
                <strong>{{ $item['name'] }}</strong> ({{ $item['email'] }})<br>
                {{ $item['message'] }}<br>
            </li>
            <hr>
        @empty
            <li>No feedback has been submitted yet.</li>
        @endforelse
    </ul>
</body>
</html>