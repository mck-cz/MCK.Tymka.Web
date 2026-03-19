@extends('layouts.app')

@section('title', $paymentRequest->name)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.payments.title'), 'href' => route('payments.index')],
        ['label' => $paymentRequest->name],
    ]" />

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-semibold">{{ $paymentRequest->name }}</h1>
                @if($paymentRequest->status === 'active')
                    <span class="badge badge-success">{{ __('messages.payments.status_active') }}</span>
                @elseif($paymentRequest->status === 'closed')
                    <span class="badge badge-gray">{{ __('messages.payments.status_closed') }}</span>
                @else
                    <span class="badge badge-danger">{{ __('messages.payments.status_cancelled') }}</span>
                @endif
            </div>
            @if($isAdmin && $paymentRequest->status === 'active')
                <div class="flex items-center gap-2">
                    <a href="{{ route('payments.edit', $paymentRequest) }}" class="btn-secondary text-sm">{{ __('messages.common.edit') }}</a>
                    <form action="{{ route('payments.cancel-request', $paymentRequest) }}" method="POST"
                        onsubmit="return confirm('{{ __('messages.payments.cancel_request_confirm') }}')">
                        @csrf
                        <button type="submit" class="btn-danger text-sm">{{ __('messages.payments.cancel_request') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Info --}}
    <div class="card mb-6">
        <div class="card-body space-y-3">
            @if($paymentRequest->description)
                <div>
                    <p class="text-muted">{{ $paymentRequest->description }}</p>
                </div>
            @endif
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.amount') }}:</span>
                <span>{{ number_format($paymentRequest->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.due_date') }}:</span>
                <span>{{ app()->getLocale() === 'cs' ? $paymentRequest->due_date->format('d.m.Y') : $paymentRequest->due_date->format('Y-m-d') }}</span>
            </div>
            @if($paymentRequest->team)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 140px;">{{ __('messages.events.team') }}:</span>
                    <span>{{ $paymentRequest->team->name }}</span>
                </div>
            @endif
            @if($paymentRequest->bank_account)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.bank_account') }}:</span>
                    <span class="font-mono">{{ $paymentRequest->bank_account }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Member Payments --}}
    @if($isAdmin)
        <div class="card mb-6">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium">
                        {{ __('messages.payments.member_payments') }}
                        <span class="text-muted font-normal text-sm">
                            ({{ $paymentRequest->memberPayments->where('status', 'paid')->count() }}/{{ $paymentRequest->memberPayments->count() }} {{ __('messages.payments.paid') }})
                        </span>
                    </h2>
                </div>
            </div>
            <div class="card-body">
                @if($paymentRequest->memberPayments->isEmpty())
                    <p class="text-muted">{{ __('messages.common.no_results') }}</p>
                @else
                    <div class="overflow-x-auto" x-data="{ confirmModal: null }">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left">
                                    <th class="pb-2 font-medium">{{ __('messages.club_admin.member_name') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.variable_symbol') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.amount') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.status') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentRequest->memberPayments->sortBy(fn ($p) => $p->user->last_name) as $mp)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                                    {{ strtoupper(mb_substr($mp->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($mp->user->last_name, 0, 1)) }}
                                                </div>
                                                <span class="font-medium">{{ $mp->user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 font-mono text-muted">{{ $mp->variable_symbol }}</td>
                                        <td class="py-3">
                                            @if($mp->paid_amount > 0 && $mp->status !== 'paid')
                                                <span class="text-muted">{{ number_format($mp->paid_amount, 0, ',', ' ') }} /</span>
                                            @endif
                                            {{ number_format($mp->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}
                                        </td>
                                        <td class="py-3">
                                            @if($mp->status === 'pending')
                                                <span class="badge badge-warning">{{ __('messages.payments.pending') }}</span>
                                            @elseif($mp->status === 'partial')
                                                <span class="badge badge-accent">{{ __('messages.payments.partial') }}</span>
                                            @elseif($mp->status === 'paid')
                                                <span class="badge badge-success">{{ __('messages.payments.paid') }}</span>
                                            @elseif($mp->status === 'overdue')
                                                <span class="badge badge-danger">{{ __('messages.payments.overdue') }}</span>
                                            @else
                                                <span class="badge badge-gray">{{ __('messages.payments.cancelled') }}</span>
                                            @endif
                                            @if($mp->thanked_at)
                                                <span class="text-xs text-muted ml-1" title="{{ __('messages.payments.thanked') }}">&#9993;</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if(in_array($mp->status, ['pending', 'overdue', 'partial']))
                                                <button
                                                    type="button"
                                                    class="btn-ghost text-success text-sm"
                                                    @click="confirmModal = @js([
                                                        'id' => $mp->id,
                                                        'name' => $mp->user->full_name,
                                                        'amount' => (float) $mp->amount,
                                                        'paidAmount' => (float) $mp->paid_amount,
                                                        'remaining' => $mp->remaining,
                                                        'currency' => $paymentRequest->currency,
                                                        'url' => route('payments.confirm', $mp),
                                                    ])"
                                                >{{ __('messages.payments.mark_paid') }}</button>
                                            @elseif($mp->status === 'paid')
                                                <span class="text-xs text-muted">
                                                    {{ $mp->paid_at ? (app()->getLocale() === 'cs' ? $mp->paid_at->format('d.m.Y') : $mp->paid_at->format('Y-m-d')) : '' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Confirm Payment Modal --}}
                        <template x-teleport="body">
                            <div
                                x-show="confirmModal"
                                x-transition.opacity
                                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                @keydown.escape.window="confirmModal = null"
                                style="display: none;"
                            >
                                <div class="fixed inset-0 bg-black/40" @click="confirmModal = null"></div>
                                <div class="relative bg-surface rounded-2xl shadow-lg w-full max-w-sm" @click.stop>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold">{{ __('messages.payments.confirm_payment') }}</h3>
                                            <button @click="confirmModal = null" class="text-muted hover:text-text text-xl leading-none">&times;</button>
                                        </div>
                                        <p class="text-sm text-muted mb-1" x-text="confirmModal?.name"></p>
                                        <p class="text-sm text-muted mb-5">
                                            {{ __('messages.payments.remaining_amount') }}:
                                            <span class="font-semibold" x-text="confirmModal ? Number(confirmModal.remaining).toLocaleString('cs-CZ') + ' ' + confirmModal.currency : ''"></span>
                                        </p>

                                        <form :action="confirmModal?.url" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <div class="mb-4">
                                                <label class="form-label">{{ __('messages.payments.paid_amount_field') }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        type="number"
                                                        name="paid_amount"
                                                        class="form-input w-full"
                                                        :value="confirmModal?.remaining"
                                                        min="1"
                                                        :max="confirmModal?.remaining"
                                                        step="1"
                                                        required
                                                    >
                                                    <span class="text-sm text-muted shrink-0" x-text="confirmModal?.currency"></span>
                                                </div>
                                                <p class="text-xs text-muted mt-1">{{ __('messages.payments.partial_hint') }}</p>
                                            </div>

                                            <div class="mb-5">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="send_thanks" value="1" checked class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                                    <span class="text-sm">{{ __('messages.payments.send_thanks') }}</span>
                                                </label>
                                                <p class="text-xs text-muted mt-1 ml-6">{{ __('messages.payments.send_thanks_hint') }}</p>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <button type="button" class="btn-ghost text-sm flex-1" @click="confirmModal = null">{{ __('messages.common.cancel') }}</button>
                                                <button type="submit" class="btn-primary text-sm flex-1">{{ __('messages.payments.confirm_payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Member view: show own payment + QR --}}
        @php
            $myPayment = $paymentRequest->memberPayments->firstWhere('user_id', auth()->id());
        @endphp
        @if($myPayment)
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-medium">{{ __('messages.payments.your_payment') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-center gap-3">
                        @if($myPayment->status === 'pending')
                            <span class="badge badge-warning">{{ __('messages.payments.pending') }}</span>
                        @elseif($myPayment->status === 'partial')
                            <span class="badge badge-accent">{{ __('messages.payments.partial') }}</span>
                        @elseif($myPayment->status === 'paid')
                            <span class="badge badge-success">{{ __('messages.payments.paid') }}</span>
                        @elseif($myPayment->status === 'overdue')
                            <span class="badge badge-danger">{{ __('messages.payments.overdue') }}</span>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.amount') }}:</span>
                        <span class="font-semibold">
                            @if($myPayment->paid_amount > 0 && $myPayment->status !== 'paid')
                                {{ number_format($myPayment->paid_amount, 0, ',', ' ') }} /
                            @endif
                            {{ number_format($myPayment->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}
                        </span>
                    </div>

                    @if($myPayment->variable_symbol)
                        <div class="flex gap-2">
                            <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.variable_symbol') }}:</span>
                            <span class="font-mono">{{ $myPayment->variable_symbol }}</span>
                        </div>
                    @endif

                    @if($myPayment->qr_payload && $myPayment->status !== 'paid')
                        <div class="mt-4 p-4 bg-bg rounded-xl text-center">
                            <p class="text-sm text-muted mb-3">{{ __('messages.payments.qr_instructions') }}</p>
                            <div class="inline-block p-4 bg-surface rounded-lg border border-border">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($myPayment->qr_payload) }}"
                                    alt="QR {{ __('messages.payments.payment_code') }}"
                                    width="200" height="200"
                                    class="mx-auto">
                            </div>
                            <p class="text-xs text-muted mt-2 font-mono break-all">{{ $myPayment->qr_payload }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
