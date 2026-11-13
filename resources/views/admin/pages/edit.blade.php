@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Page: {{ $page->title }}</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.pages.update', $page) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Title</label>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Meta Description (SEO)</label>
            <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Content (HTML/Text)</label>
            <textarea name="content" rows="15" class="w-full border p-2 rounded font-mono text-sm">{{ old('content', $page->content) }}</textarea>
        </div>

        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }} class="mr-2">
            <label for="is_published" class="text-gray-700 font-bold">Published</label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Page</button>
        </div>
    </form>
</div>
@endsection