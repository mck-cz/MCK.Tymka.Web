@extends('layouts.app')

@section('title', __('messages.venue_costs.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.venue_costs.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('venue-costs.create') }}" class="btn-primary text-sm">{{ __('messages.venue_costs.create') }}</a>
        @endif
    </div>

    @forelse($venueCosts as $vc)
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $vc->name }}</span>
                            @if($vc->is_active)
                                <span class="badge badge-success">{{ __('messages.venue_costs.active') }}</span>
                            @else
                                <span class="badge badge-gray">{{ __('messages.venue_costs.inactive') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-muted mt-1">
                            {{ number_format($vc->cost_per_event, 0) }} {{ $vc->currency }} / {{ __('messages.venue_costs.per_event') }}
                            @if($vc->team)
                                — {{ $vc->team->name }}
                            @endif
                        </p>
                        <p class="text-xs text-muted mt-1">
                            {{ __('messages.venue_costs.split_' . $vc->split_method) }} · {{ __('messages.venue_costs.period_' . $vc->billing_period) }}
                        </p>
                    </div>
                    @if($isClubAdmin)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('venue-costs.edit', $vc) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                            <form action="{{ route('venue-costs.destroy', $vc) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.venue_costs.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Generate settlement --}}
                <div class="mt-4 pt-4 border-t border-border" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="text-sm text-primary hover:underline">
                        {{ __('messages.venue_costs.generate_settlement') }}
                    </button>
                    <form x-show="open" x-cloak action="{{ route('venue-costs.generate-settlement', $vc) }}" method="POST" class="mt-3 flex items-end gap-3">
                        @csrf
                        <div>
                            <label class="form-label text-xs">{{ __('messages.venue_costs.period_from') }}</label>
                            <input type="date" name="period_from" class="form-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">{{ __('messages.venue_costs.period_to') }}</label>
                            <input type="date" name="period_to" class="form-input text-sm" required>
                        </div>
                        <button type="submit" class="btn-primary text-sm">{{ __('messages.venue_costs.generate') }}</button>
                    </form>
                </div>

                {{-- List existing settlements --}}
                @if($vc->venueCostSettlements->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-border">
                        <p class="text-xs font-medium text-muted mb-2">{{ __('messages.venue_costs.settlements') }}</p>
                        @foreach($vc->venueCostSettlements->sortByDesc('generated_at') as $settlement)
                            <a href="{{ route('venue-cost-settlements.show', $settlement) }}"
                                class="flex items-center justify-between py-1 text-sm hover:underline">
                                <span>{{ app()->getLocale() === 'cs' ? \Carbon\Carbon::parse($settlement->period_from)->format('d.m.Y') : $settlement->period_from }} — {{ app()->getLocale() === 'cs' ? \Carbon\Carbon::parse($settlement->period_to)->format('d.m.Y') : $settlement->period_to }}</span>
                                <span class="badge badge-{{ $settlement->status === 'settled' ? 'success' : ($settlement->status === 'sent' ? 'primary' : 'gray') }}">
                                    {{ __('messages.venue_costs.status_' . $settlement->status) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.venue_costs.no_costs') }}</p>
            </div>
        </div>
    @endforelse
@endsection
