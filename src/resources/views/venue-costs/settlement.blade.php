@extends('layouts.app')

@section('title', __('messages.venue_costs.settlement_detail'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.venue_costs.title'), 'href' => route('venue-costs.index')],
        ['label' => $settlement->venueCost->name, 'href' => route('venue-costs.index')],
        ['label' => __('messages.venue_costs.settlement_detail')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.venue_costs.settlement_detail') }}</h1>

    {{-- Summary --}}
    <div class="card mb-6">
        <div class="card-body space-y-3">
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.name') }}:</span>
                <span>{{ $settlement->venueCost->name }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.period') }}:</span>
                <span>{{ app()->getLocale() === 'cs' ? \Carbon\Carbon::parse($settlement->period_from)->format('d.m.Y') : $settlement->period_from }} — {{ app()->getLocale() === 'cs' ? \Carbon\Carbon::parse($settlement->period_to)->format('d.m.Y') : $settlement->period_to }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.total_events') }}:</span>
                <span>{{ $settlement->total_events }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.total_cost') }}:</span>
                <span>{{ number_format($settlement->total_cost, 0) }} {{ $settlement->venueCost->currency }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.total_attendances') }}:</span>
                <span>{{ $settlement->total_attendances }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.cost_per_attendance') }}:</span>
                <span>{{ number_format($settlement->cost_per_attendance, 2) }} {{ $settlement->venueCost->currency }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 160px;">{{ __('messages.venue_costs.status') }}:</span>
                <span class="badge badge-{{ $settlement->status === 'settled' ? 'success' : ($settlement->status === 'sent' ? 'primary' : 'gray') }}">
                    {{ __('messages.venue_costs.status_' . $settlement->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Member shares --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.venue_costs.member_shares') }}</h2>
        </div>
        <div class="card-body">
            @forelse($settlement->venueCostMemberShares->sortByDesc('amount_due') as $share)
                <div class="flex items-center justify-between py-3 @if(!$loop->last) border-b border-border @endif">
                    <div>
                        <span class="font-medium">{{ $share->user->full_name }}</span>
                        <p class="text-sm text-muted">
                            {{ $share->attendance_count }}× {{ __('messages.venue_costs.attendances_label') }}
                            = {{ number_format($share->amount_due, 0) }} {{ $settlement->venueCost->currency }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($share->status === 'paid')
                            <span class="text-success text-sm font-medium">&#10003; {{ __('messages.venue_costs.paid') }}</span>
                        @else
                            <span class="text-muted text-sm">{{ __('messages.venue_costs.pending') }}</span>
                            <form action="{{ route('venue-cost-shares.confirm', $share) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-ghost text-sm text-success">{{ __('messages.venue_costs.confirm_paid') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.common.no_results') }}</p>
            @endforelse
        </div>
    </div>
@endsection
