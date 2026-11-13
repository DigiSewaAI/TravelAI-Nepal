@extends('layouts.traveler-v2')

@section('title', 'Edit Review')

@section('traveler-v2-content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md border p-6">
        <h1 class="text-2xl font-bold mb-4">Edit Review</h1>

        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="bg-red-100 text-red-800 p-3 rounded mb-4"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ route('traveler.reviews.update', $review) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block font-semibold mb-2">Rating *</label>
                <select name="rating" class="w-full border p-2 rounded" required>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'selected' : '' }}>{{ $i }} ★</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Title (optional)</label>
                <input type="text" name="title" value="{{ old('title', $review->title) }}" class="w-full border p-2 rounded" maxlength="150">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Comment (optional)</label>
                <textarea name="comment" rows="5" class="w-full border p-2 rounded" maxlength="2000">{{ old('comment', $review->comment) }}</textarea>
            </div>

            <div class="flex gap-2">
                <button class="bg-blue-600 text-white px-6 py-2 rounded">Save Changes</button>
                <a href="{{ route('traveler.reviews.index') }}" class="bg-gray-200 px-6 py-2 rounded">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection