{{-- Transport category fields --}}
@php $detail = $service?->transportDetail; @endphp

<div id="fields-transport" class="category-fields hidden space-y-4" data-slug="transport">

    <div>
        <label class="block text-gray-700 font-semibold mb-1">Transport Type *</label>
        <select name="transport_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('transport_type') border-red-500 @enderror">
            <option value="">Select type</option>
            @foreach(['bus' => 'Bus', 'jeep' => 'Jeep', 'car' => 'Car', 'van' => 'Van', 'flight' => 'Flight', 'heli' => 'Helicopter'] as $val => $label)
                <option value="{{ $val }}" {{ old('transport_type', $detail?->transport_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('transport_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 font-semibold mb-1">From Location *</label>
            <select name="from_location_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('from_location_id') border-red-500 @enderror">
                <option value="">Select location</option>
                @foreach(($locations ?? collect()) as $loc)
                    <option value="{{ $loc->id }}" {{ old('from_location_id', $detail?->from_location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->city }}</option>
                @endforeach
            </select>
            @error('from_location_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">To Location *</label>
            <select name="to_location_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('to_location_id') border-red-500 @enderror">
                <option value="">Select location</option>
                @foreach(($locations ?? collect()) as $loc)
                    <option value="{{ $loc->id }}" {{ old('to_location_id', $detail?->to_location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->city }}</option>
                @endforeach
            </select>
            @error('to_location_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Departure Time</label>
            <input type="time" name="departure_time" value="{{ old('departure_time', $detail?->departure_time ? \Carbon\Carbon::parse($detail->departure_time)->format('H:i') : '') }}"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('departure_time') border-red-500 @enderror">
            @error('departure_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">Duration (hours) *</label>
            <input type="number" name="duration_hours" step="0.5" min="0.5"
                   value="{{ old('duration_hours', $detail?->duration_minutes ? $detail->duration_minutes / 60 : '') }}"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_hours') border-red-500 @enderror">
            <p class="text-xs text-gray-400 mt-1">e.g., 6 for 6 hours</p>
            @error('duration_hours')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Price / Person *</label>
            <input type="number" name="price_per_person" value="{{ old('price_per_person', $detail?->price_per_person ?? '') }}" min="0" step="0.01"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_person') border-red-500 @enderror">
            @error('price_per_person')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">Price / Vehicle</label>
            <input type="number" name="price_per_vehicle" value="{{ old('price_per_vehicle', $detail?->price_per_vehicle ?? '') }}" min="0" step="0.01"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_vehicle') border-red-500 @enderror">
            @error('price_per_vehicle')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">Total Seats *</label>
            <input type="number" name="total_seats" value="{{ old('total_seats', $detail?->total_seats ?? '') }}" min="1"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_seats') border-red-500 @enderror">
            @error('total_seats')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Private / Shared *</label>
            <select name="private_shared" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('private_shared') border-red-500 @enderror">
                <option value="private" {{ old('private_shared', $detail?->private_shared ?? 'private') === 'private' ? 'selected' : '' }}>Private</option>
                <option value="shared" {{ old('private_shared', $detail?->private_shared) === 'shared' ? 'selected' : '' }}>Shared</option>
            </select>
            @error('private_shared')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">Booking Type</label>
            <select name="booking_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('booking_type') border-red-500 @enderror">
                <option value="on-request" {{ old('booking_type', $detail?->booking_type ?? 'on-request') === 'on-request' ? 'selected' : '' }}>On Request</option>
                <option value="instant" {{ old('booking_type', $detail?->booking_type) === 'instant' ? 'selected' : '' }}>Instant</option>
            </select>
            @error('booking_type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-1">AC Available</label>
            <label class="flex items-center mt-2">
                <input type="checkbox" name="ac_available" value="1" {{ old('ac_available', $detail?->ac_available) ? 'checked' : '' }} class="mr-2">
                <span class="text-sm text-gray-600">Yes</span>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="flex items-center">
            <input type="checkbox" name="driver_included" value="1" {{ old('driver_included', $detail?->driver_included ?? true) ? 'checked' : '' }} class="mr-2">
            <span class="text-sm text-gray-600">Driver Included</span>
        </label>
        <label class="flex items-center">
            <input type="checkbox" name="fuel_included" value="1" {{ old('fuel_included', $detail?->fuel_included ?? true) ? 'checked' : '' }} class="mr-2">
            <span class="text-sm text-gray-600">Fuel Included</span>
        </label>
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">Cancellation Policy</label>
        <textarea name="cancellation_policy" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cancellation_policy') border-red-500 @enderror">{{ old('cancellation_policy', $detail?->cancellation_policy ?? '') }}</textarea>
        @error('cancellation_policy')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $detail?->description ?? '') }}</textarea>
        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

</div>