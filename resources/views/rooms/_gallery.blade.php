@php
    $hasPhotos = $photos->isNotEmpty();
@endphp

@if($hasPhotos)
<div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
    @foreach($photos as $photo)
        <img src="{{ $photo->url }}" class="rounded border border-gray-200 aspect-square object-cover">
    @endforeach
</div>
@else
<p class="text-sm text-gray-400 mb-3">No {{ $type }} photos yet.</p>
@endif

@if($canUpload)
<form method="POST" action="{{ route('rooms.photos.store', [$project, $room]) }}" enctype="multipart/form-data" class="flex items-center gap-2">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="file" name="photo" accept="image/*" required
        class="text-xs border border-gray-300 rounded px-2 py-1.5 flex-1">
    <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded hover:bg-blue-700 whitespace-nowrap">
        Upload {{ ucfirst($type) }} Photo
    </button>
</form>
@elseif($lockedMessage)
<p class="text-xs text-gray-400">🔒 {{ $lockedMessage }}</p>
@endif
