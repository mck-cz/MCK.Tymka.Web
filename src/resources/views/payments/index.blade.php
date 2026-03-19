@extends('layouts.app')

@section('title', __('messages.payments.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.payments.title') }}</h1>
        @if($isAdmin)
            <a href="{{ route('payments.create') }}" class="btn-primary">{{ __('messages.payments.create') }}</a>
        @endif
    </div>

    {{-- Admin view: Payment Requests --}}
    @if($isAdmin)
        <div class="card mb-6">
            <div class="card-header">
                <h2 class="font-medium">{{ __('messages.payments.requests') }}</h2>
            </div>
            <div class="card-body">
                @if($paymentRequests->isEmpty())
                    <p class="text-muted">{{ __('messages.payments.no_requests') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left">
                                    <th class="pb-2 font-medium">{{ __('messages.payments.name') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.amount') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.due_date') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.status') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.paid_count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentRequests as $pr)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3">
                                            <a href="{{ route('payments.show', $pr) }}" class="font-medium hover:underline">{{ $pr->name }}</a>
                                            @if($pr->team)
                                                <div class="text-xs text-muted">{{ $pr->team->name }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3">{{ number_format($pr->amount, 0, ',', ' ') }} {{ $pr->currency }}</td>
                                        <td class="py-3 text-muted">
                                            {{ app()->getLocale() === 'cs' ? $pr->due_date->format('d.m.Y') : $pr->due_date->format('Y-m-d') }}
                                        </td>
                                        <td class="py-3">
                                            @if($pr->status === 'active')
                                                <span class="badge badge-success">{{ __('messages.payments.status_active') }}</span>
                                            @elseif($pr->status === 'closed')
                                                <span class="badge badge-gray">{{ __('messages.payments.status_closed') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('messages.payments.status_cancelled') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-muted">
                                            {{ $pr->memberPayments->where('status', 'paid')->count() }}/{{ $pr->memberPayments->count() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Member view: My Payments --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.payments.my_payments') }}</h2>
        </div>
        <div class="card-body">
            @if($myPayments->isEmpty())
                <p class="text-muted">{{ __('messages.payments.no_payments') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($myPayments as $payment)
                        <a href="{{ route('payments.show', $payment->paymentRequest) }}"
                            class="flex items-center justify-between py-3 border-b border-border last:border-0 hover:bg-bg rounded-lg transition-colors px-2">
                            <div>
                                <div class="font-medium">{{ $payment->paymentRequest->name }}</div>
                                <div class="text-sm text-muted">
                                    {{ __('messages.payments.due_date') }}:
                                    {{ app()->getLocale() === 'cs' ? $payment->paymentRequest->due_date->format('d.m.Y') : $payment->paymentRequest->due_date->format('Y-m-d') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->paymentRequest->currency }}</div>
                                @if($payment->status === 'pending')
                                    <span class="badge badge-warning">{{ __('messages.payments.pending') }}</span>
                                @elseif($payment->status === 'paid')
                                    <span class="badge badge-success">{{ __('messages.payments.paid') }}</span>
                                @elseif($payment->status === 'overdue')
                                    <span class="badge badge-danger">{{ __('messages.payments.overdue') }}</span>
                                @else
                                    <span class="badge badge-gray">{{ __('messages.payments.cancelled') }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
