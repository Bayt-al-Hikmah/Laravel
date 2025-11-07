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