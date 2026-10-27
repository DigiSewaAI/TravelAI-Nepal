@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.edit_profile') ?? 'Edit Profile')
@section('header', __('messages.edit_profile_header') ?? 'Edit Profile')

@section('content')

{{-- 🆕 स्मार्ट कन्डिसन: यदि पुरानो Trekking लेआउट हो भने पुरानै Tailwind कोड चल्नेछ --}}
@if($provider_layout === 'layouts.provider')

    {{-- ========================================== --}}
    {{-- 🟢 ORIGINAL TREKKING / TOUR CODE (Tailwind) --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('provider.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.logo') }}</label>
                    @if($provider->logo_url)
                        <div class="mb-2">
                            <img src="{{ Storage::url($provider->logo_url) }}" 
                                 class="h-20 w-20 object-cover rounded-full border-2 border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.logo_upload_hint') }}</p>
                    @error('logo')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Cover Image Upload -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.cover_image') }}</label>
                    @if($provider->cover_image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($provider->cover_image) }}" 
                                 class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="cover_image" accept="image/*" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.cover_image_upload_hint') }}</p>
                    @error('cover_image')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $provider->name) }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.contact_email') }}</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $provider->contact_email) }}"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('contact_email')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.contact_phone') }}</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $provider->contact_phone) }}"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('contact_phone')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.website') ?? 'Website' }}</label>
                    <input type="url" name="website" value="{{ old('website', $provider->website) }}"
                           placeholder="https://example.com"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('website')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.address') }}</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $provider->address) }}</textarea>
                    @error('address')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.description') }}</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $provider->description) }}</textarea>
                    @error('description')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-save mr-1"></i> {{ __('messages.update_profile') }}
                </button>
                <a href="{{ route('provider.profile') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-1"></i> {{ __('messages.cancel') }}
                </a>
            </div>
        </form>
    </div>

@else

    {{-- ========================================== --}}
    {{-- 🟠 NEW 7 CATEGORIES CODE (Premium CSS)     --}}
    {{-- ========================================== --}}
    <style>
        .edit-container { max-width: 900px; margin: 0 auto; }
        
        .form-card {
            background: var(--card, #ffffff);
            border: 1px solid var(--line, #e5e7eb);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 16px; font-weight: 800; color: var(--text, #1f2937);
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid var(--line, #e5e7eb);
            display: flex; align-items: center; gap: 10px;
        }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group.full-width { grid-column: span 2; }
        @media (max-width: 768px) { .input-group.full-width { grid-column: span 1; } }

        .input-label {
            font-size: 12px; font-weight: 800; color: var(--muted, #6b7280);
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .form-input, .form-textarea {
            width: 100%; padding: 12px 16px;
            background: var(--bg, #f9fafb);
            border: 1px solid var(--line, #e5e7eb);
            border-radius: 12px;
            font-family: inherit; font-size: 14px; font-weight: 600; color: var(--text, #1f2937);
            outline: none; transition: all 0.2s;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--accent, #f97316);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
            background: var(--card, #ffffff);
        }

        .form-textarea { resize: vertical; min-height: 100px; }

        .file-upload-box {
            border: 2px dashed var(--line, #e5e7eb);
            border-radius: 12px; padding: 20px;
            text-align: center; background: var(--bg, #f9fafb);
            cursor: pointer; transition: 0.2s; position: relative;
        }
        .file-upload-box:hover { border-color: var(--accent, #f97316); background: var(--accent-soft, #fff7ed); }
        .file-upload-box input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        
        .current-img-preview { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; margin-bottom: 10px; border: 1px solid var(--line); }

        .action-bar { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
        
        .btn-save {
            background: var(--accent, #f97316); color: #fff;
            padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px;
            border: none; cursor: pointer; transition: 0.2s;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4); }
        
        .btn-cancel {
            background: var(--bg, #f3f4f6); color: var(--text, #4b5563);
            padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px;
            border: 1px solid var(--line, #e5e7eb); cursor: pointer; transition: 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-cancel:hover { background: var(--line, #e5e7eb); }
        
        .error-msg { color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 600; }
    </style>

    <div class="edit-container">
        <form action="{{ route('provider.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Business Visuals --}}
            <div class="form-card">
                <h3 class="section-title">
                    <i class="fas fa-images" style="color: var(--accent);"></i>
                    {{ __('messages.visuals') ?? 'Business Visuals' }}
                </h3>
                <div class="form-grid">
                    {{-- Logo --}}
                    <div class="input-group">
                        <label class="input-label">{{ __('messages.logo') ?? 'Logo' }}</label>
                        <div class="file-upload-box">
                            @if($provider->logo_url)
                                <img src="{{ Storage::url($provider->logo_url) }}" class="current-img-preview" alt="Logo">
                            @else
                                <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: var(--muted); margin-bottom: 8px;"></i>
                            @endif
                            <p style="font-size: 13px; font-weight: 700; color: var(--text);">Click to upload new logo</p>
                            <p style="font-size: 11px; color: var(--muted); margin-top: 4px;">Max 2MB</p>
                            <input type="file" name="logo" accept="image/*">
                        </div>
                        @error('logo') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    {{-- Cover Image --}}
                    <div class="input-group">
                        <label class="input-label">{{ __('messages.cover_image') ?? 'Cover Image' }}</label>
                        <div class="file-upload-box">
                            @if($provider->cover_image)
                                <img src="{{ Storage::url($provider->cover_image) }}" class="current-img-preview" style="width: 100px; height: 60px;" alt="Cover">
                            @else
                                <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: var(--muted); margin-bottom: 8px;"></i>
                            @endif
                            <p style="font-size: 13px; font-weight: 700; color: var(--text);">Click to upload new cover</p>
                            <p style="font-size: 11px; color: var(--muted); margin-top: 4px;">Max 5MB</p>
                            <input type="file" name="cover_image" accept="image/*">
                        </div>
                        @error('cover_image') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Business Details --}}
            <div class="form-card">
                <h3 class="section-title">
                    <i class="fas fa-building" style="color: var(--accent);"></i>
                    {{ __('messages.business_details') ?? 'Business Details' }}
                </h3>
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label class="input-label">{{ __('messages.business_name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="form-input">
                        @error('name') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('messages.contact_email') }}</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $provider->contact_email) }}" class="form-input">
                        @error('contact_email') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('messages.contact_phone') }}</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $provider->contact_phone) }}" class="form-input">
                        @error('contact_phone') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="input-group full-width">
                        <label class="input-label">{{ __('messages.website') ?? 'Website' }}</label>
                        <input type="url" name="website" value="{{ old('website', $provider->website) }}" placeholder="https://example.com" class="form-input">
                        @error('website') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="input-group full-width">
                        <label class="input-label">{{ __('messages.address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $provider->address) }}" class="form-input">
                        @error('address') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>

                    <div class="input-group full-width">
                        <label class="input-label">{{ __('messages.description') }}</label>
                        <textarea name="description" class="form-textarea">{{ old('description', $provider->description) }}</textarea>
                        @error('description') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="action-bar">
                    <a href="{{ route('provider.profile') }}" class="btn-cancel">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> {{ __('messages.update_profile') ?? 'Update Profile' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

@endif

@endsection