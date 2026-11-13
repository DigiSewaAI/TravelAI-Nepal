@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Content Pages (CMS)</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 text-sm">
                <tr>
                    <th class="p-4 border-b">Title</th>
                    <th class="p-4 border-b">Slug</th>
                    <th class="p-4 border-b">Status</th>
                    <th class="p-4 border-b">Last Updated</th>
                    <th class="p-4 border-b">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700">
                @forelse($pages as $page)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 border-b font-semibold">{{ $page->title }}</td>
                    <td class="p-4 border-b">{{ $page->slug }}</td>
                    <td class="p-4 border-b">
                        @if($page->is_published)
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">Published</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Draft</span>
                        @endif
                    </td>
                    <td class="p-4 border-b">{{ $page->updated_at->format('M d, Y') }}</td>
                    <td class="p-4 border-b">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-blue-600 hover:underline">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">No pages found. Run the seeder to create default pages.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection