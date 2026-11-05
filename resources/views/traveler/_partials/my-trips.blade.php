@php
    $treks = $treks ?? null;
    $allOrders = $allOrders ?? null;
    $shopOrders = $shopOrders ?? null;
    $rentalOrders = $rentalOrders ?? null;
    $rfqs = $rfqs ?? null;
    $counts = $counts ?? [];
    $claimedRestaurant = $claimedRestaurant ?? collect();
    $claimedTransport = $claimedTransport ?? collect();
    $claimedHotel = $claimedHotel ?? collect();
    $claimedArtcraft = $claimedArtcraft ?? collect();
@endphp

<div class="tv2-mytrips" style="background: #fff; border-radius: 18px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 22px; font-weight: 800; margin: 0; color: #111827;">
            🧳 {{ __('messages.my_trips') ?? 'My Trips' }}
        </h2>
        <a href="{{ route('traveler.dashboard-v2') }}" style="color: #2563eb; font-weight: 600; font-size: 14px; text-decoration: none;">{{ __('messages.view_all') ?? 'View All' }} <span class="tv2-btn-arrow">→</span></a>
    </div>

    {{-- Tab counts --}}
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
        <span style="background: #2563eb; color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.tab_all') ?? 'All' }} ({{ $counts['all_orders'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.tab_treks') ?? 'Trekking' }} ({{ $counts['trekking'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_shop') ?? 'Shop' }} ({{ $counts['shop'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_rental') ?? 'Rental' }} ({{ $counts['rental'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_wholesale') ?? 'Wholesale' }} ({{ $counts['wholesale'] ?? 0 }})</span>
    
        @if($claimedRestaurant->count() > 0)
        <span style="background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">🍽️ {{ __('messages.my_trips_restaurant') }} ({{ $claimedRestaurant->count() }})</span>
        @endif
        @if($claimedTransport->count() > 0)
        <span style="background: #dbeafe; color: #1e40af; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">🚐 {{ __('messages.my_trips_transport') }} ({{ $claimedTransport->count() }})</span>
        @endif
        @if($claimedHotel->count() > 0)
        <span style="background: #ede9fe; color: #5b21b6; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">🏨 {{ __('messages.my_trips_hotel') }} ({{ $claimedHotel->count() }})</span>
        @endif

        @if($claimedArtcraft->count() > 0)
        <span style="background: #fce7f3; color: #9f1239; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">🎨 ArtCraft ({{ $claimedArtcraft->count() }})</span>
        @endif
    </div>

    {{-- Trekking cards --}}
    @if($treks && $treks->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @foreach($treks as $trek)
                @php
                    $trekImgPath = $trek->service->cover_image ?? null;
                    $trekImgUrl = $trekImgPath
                        ? (\Illuminate\Support\Str::startsWith($trekImgPath, 'http') ? $trekImgPath : \Illuminate\Support\Facades\Storage::url($trekImgPath))
                        : asset('images/defaults/trek.jpg');
                @endphp
                <div style="border-radius: 14px; overflow: hidden; position: relative; height: 260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <div style="position: absolute; inset: 0; background: url('{{ $trekImgUrl }}') center/cover;"></div>
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.15) 70%, rgba(0,0,0,0) 100%);"></div>
                    <div style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; color: #fff;">
                        <div style="font-size: 12px; font-weight: 700; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">🏔️ {{ __('messages.tab_treks') ?? 'Treks' }}</div>
                        <div style="font-weight: 700; font-size: 15px; margin-top: 6px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ $trek->service->name ?? 'Trek' }}</div>
                        <div style="font-size: 12px; opacity: 0.9; margin-top: 4px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ $trek->created_at->translatedFormat('M d, Y') }}</div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px;">
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($trek->status) }}
                            </span>
                            <a href="{{ route('traveler.bookings.show', $trek) }}" style="background: rgba(255,255,255,0.95); color: #1e293b; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-decoration: none;">
                                View <span class="tv2-btn-arrow">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    
    {{-- ═══ Claimed ArtCraft Orders ═══ --}}
    @if($claimedArtcraft->count() > 0)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 12px;">
                🎨 ArtCraft ({{ $claimedArtcraft->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($claimedArtcraft as $artOrder)
                    <div style="border-radius: 14px; overflow: hidden; position: relative; height: 260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        @php
                            $imgPath_artcraft = null;
                            if ($artOrder->provider) {
                                $svc_artcraft = $artOrder->provider->services()->first();
                                $imgPath_artcraft = $svc_artcraft?->cover_image;
                            }
                            $imgUrl_artcraft = $imgPath_artcraft
                                ? (\Illuminate\Support\Str::startsWith($imgPath_artcraft, 'http') ? $imgPath_artcraft : \Illuminate\Support\Facades\Storage::url($imgPath_artcraft))
                                : asset('images/defaults/artcraft.jpg');
                        @endphp
                        <div style="position: absolute; inset: 0; background: url('{{ $imgUrl_artcraft }}') center/cover;"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.15) 70%, rgba(0,0,0,0) 100%);"></div>
                        <div style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; color: #fff;">
                            <div style="font-size: 12px; font-weight: 700; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">🎨 ArtCraft</div>
                            <div style="font-weight: 700; font-size: 15px; margin-top: 6px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ ucfirst($artOrder->type) }} · {{ $artOrder->client_name }}</div>
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 4px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                                NPR {{ number_format($artOrder->budget, 2) }}@if($artOrder->deadline) · Due: {{ \Carbon\Carbon::parse($artOrder->deadline)->format('M d, Y') }}@endif
                            </div>
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($artOrder->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent orders --}}
    {{-- ═══ Claimed Restaurant Orders ═══ --}}
    @if($claimedRestaurant->count() > 0)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 12px;">
                🍽️ {{ __('messages.my_trips_restaurant') }} ({{ $claimedRestaurant->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($claimedRestaurant as $order)
                    <div style="border-radius: 14px; overflow: hidden; position: relative; height: 260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        @php
                            $imgPath_restaurant = null;
                            if ($order->provider) {
                                $svc_restaurant = $order->provider->services()->first();
                                $imgPath_restaurant = $svc_restaurant?->cover_image;
                            }
                            $imgUrl_restaurant = $imgPath_restaurant
                                ? (\Illuminate\Support\Str::startsWith($imgPath_restaurant, 'http') ? $imgPath_restaurant : \Illuminate\Support\Facades\Storage::url($imgPath_restaurant))
                                : asset('images/defaults/restaurant.jpg');
                        @endphp
                        <div style="position: absolute; inset: 0; background: url('{{ $imgUrl_restaurant }}') center/cover;"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.15) 70%, rgba(0,0,0,0) 100%);"></div>
                        <div style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; color: #fff;">
                            <div style="font-size: 12px; font-weight: 700; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">🍽️ {{ __('messages.my_trips_restaurant') }}</div>
                            <div style="font-weight: 700; font-size: 15px; margin-top: 6px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ $order->order_number }}</div>
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 4px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                                {{ $order->created_at ? $order->created_at->translatedFormat('M d, Y') : '' }} · NPR {{ number_format($order->total ?? 0, 2) }}
                            </div>
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ Claimed Transport Bookings ═══ --}}
    @if($claimedTransport->count() > 0)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 12px;">
                🚐 {{ __('messages.my_trips_transport') }} ({{ $claimedTransport->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($claimedTransport as $booking)
                    <div style="border-radius: 14px; overflow: hidden; position: relative; height: 260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        @php
                            $imgPath_transport = null;
                            if ($booking->provider) {
                                $svc_transport = $booking->provider->services()->first();
                                $imgPath_transport = $svc_transport?->cover_image;
                            }
                            $imgUrl_transport = $imgPath_transport
                                ? (\Illuminate\Support\Str::startsWith($imgPath_transport, 'http') ? $imgPath_transport : \Illuminate\Support\Facades\Storage::url($imgPath_transport))
                                : asset('images/defaults/transport.jpg');
                        @endphp
                        <div style="position: absolute; inset: 0; background: url('{{ $imgUrl_transport }}') center/cover;"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.15) 70%, rgba(0,0,0,0) 100%);"></div>
                        <div style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; color: #fff;">
                            <div style="font-size: 12px; font-weight: 700; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">🚐 {{ __('messages.my_trips_transport') }}</div>
                            <div style="font-weight: 700; font-size: 15px; margin-top: 6px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ $booking->booking_ref }}</div>
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 4px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                                {{ $booking->route_name ?? '' }}@if($booking->travel_date) · {{ \Carbon\Carbon::parse($booking->travel_date)->format('M d, Y') }}@endif
                            </div>
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ Claimed Hotel Bookings ═══ --}}
    @if($claimedHotel->count() > 0)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 12px;">
                🏨 {{ __('messages.my_trips_hotel') }} ({{ $claimedHotel->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($claimedHotel as $hotelBooking)
                    <div style="border-radius: 14px; overflow: hidden; position: relative; height: 260px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        @php
                            $imgPath_hotel = null;
                            if ($hotelBooking->provider) {
                                $svc_hotel = $hotelBooking->provider->services()->first();
                                $imgPath_hotel = $svc_hotel?->cover_image;
                            }
                            $imgUrl_hotel = $imgPath_hotel
                                ? (\Illuminate\Support\Str::startsWith($imgPath_hotel, 'http') ? $imgPath_hotel : \Illuminate\Support\Facades\Storage::url($imgPath_hotel))
                                : asset('images/defaults/hotel.jpg');
                        @endphp
                        <div style="position: absolute; inset: 0; background: url('{{ $imgUrl_hotel }}') center/cover;"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.15) 70%, rgba(0,0,0,0) 100%);"></div>
                        <div style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; color: #fff;">
                            <div style="font-size: 12px; font-weight: 700; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">🏨 {{ __('messages.my_trips_hotel') }}</div>
                            <div style="font-weight: 700; font-size: 15px; margin-top: 6px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">{{ $hotelBooking->guest_name ?? 'Guest' }}</div>
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 4px; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                                {{ $hotelBooking->check_in_date ? \Carbon\Carbon::parse($hotelBooking->check_in_date)->format('M d, Y') : '' }}@if($hotelBooking->nights) · {{ $hotelBooking->nights }} nights @endif
                            </div>
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($hotelBooking->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if($allOrders && $allOrders->count() > 0)
        <div style="margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">{{ __('messages.my_orders') ?? 'My Orders' }} <span style="font-size: 12px; font-weight: 500; color: #6b7280;">({{ $allOrders->total() }})</span></h3>
                <button type="button" id="tv2OrdersToggle" onclick="(function(){var l=document.getElementById('tv2OrdersList');var b=this;if(l.style.display==='none'||!l.style.display){l.style.display='flex';b.textContent='{{ __('messages.hide') ?? "Hide" }} ↑';}else{l.style.display='none';b.textContent='{{ __('messages.view_all') ?? "View All" }} →';}}).call(this)" style="background: #2563eb; color: #fff; border: 0; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    {{ __('messages.view_all') ?? 'View All' }} <span class="tv2-btn-arrow">→</span>
                </button>
            </div>
            <div id="tv2OrdersList" style="display: none; flex-direction: column; gap: 10px;">
                @foreach($allOrders as $order)
                    @php
                        $orderTypes = $order->items->pluck('product_type')->unique();
                        $isMixed = $orderTypes->count() > 1 || $orderTypes->contains(null);
                        $typeLabel = $isMixed ? 'Mixed' : ucfirst($orderTypes->first() ?? 'order');
                    @endphp
                    <a href="{{ route('orders.show', $order) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; text-decoration: none; color: inherit;">
                        <div>
                            <div style="font-weight: 700; color: #111827; font-size: 14px;">{{ $order->order_number ?? 'Order' }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $order->created_at->translatedFormat('M d, Y') }} · {{ $order->items->count() }} {{ __('messages.items_count') ?? 'items' }} · {{ $typeLabel }}</div>
                        </div>
                        <span style="color: #2563eb; font-size: 13px; font-weight: 600;">{{ __('messages.view') ?? 'View' }} <span class="tv2-btn-arrow">→</span></span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if((!$treks || $treks->count() === 0) && (!$allOrders || $allOrders->count() === 0) && (!$rfqs || $rfqs->count() === 0))
        <div style="text-align: center; padding: 40px 20px; color: #6b7280;">
            <div style="font-size: 48px; margin-bottom: 8px;">🧳</div>
            <p>{{ __('messages.no_trips_yet') ?? 'No trips yet. Start exploring Nepal!' }}</p>
        </div>
    @endif
</div>