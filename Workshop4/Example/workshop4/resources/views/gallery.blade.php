<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Gallery</title>
    {{-- We'll assume a CSS file is in public/css/style.css --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Photo Gallery</h1>
    <a href="{{ route('upload.create') }}" class="btn">Upload New Photo</a>

    <div class="gallery">
    
        @forelse ($photos as $photo)
            <div class="photo-card">
                <img src="{{ asset('storage/' . $photo->image_path) }}" alt="{{ $photo->title }}">
                <p>{{ $photo->title }}</p>
                <small>Uploaded at: {{ $photo->created_at->format('Y-m-d H:i') }}</small>
            </div>
        @empty
            <p>No photos uploaded yet.</p>
        @endforelse
    </div>
</body>
</html>