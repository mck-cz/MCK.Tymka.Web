@extends('layouts.app')

@section('title', __('messages.consents.overview'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.consents.title'), 'href' => route('consents.index')],
        ['label' => __('messages.consents.overview')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.consents.overview') }}</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('consents.overview') }}" class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.common.search') }}</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-input"
                        placeholder="{{ __('messages.consents.search_placeholder') }}">
                </div>
                <div>
                    <label class="form-label">{{ __('messages.consents.consent_type') }}</label>
                    <select name="type" class="form-select">
                        <option value="">{{ __('messages.common.all') }}</option>
                        @foreach($consentTypes as $type)
                            <option value="{{ $type->id }}" @selected($typeFilter === $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('messages.common.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('messages.common.all') }}</option>
                        <option value="granted" @selected($statusFilter === 'granted')>{{ __('messages.consents.granted_label') }}</option>
                        <option value="revoked" @selected($statusFilter === 'revoked')>{{ __('messages.consents.revoked_label') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary">{{ __('messages.common.filter') }}</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Results table --}}
    <div class="card">
        <div class="card-body overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-border">
                        <th class="pb-2 font-medium text-muted">{{ __('messages.consents.member') }}</th>
                        <th class="pb-2 font-medium text-muted">{{ __('messages.consents.for_child') }}</th>
                        <th class="pb-2 font-medium text-muted">{{ __('messages.consents.consent_type') }}</th>
                        <th class="pb-2 font-medium text-muted">{{ __('messages.common.status') }}</th>
                        <th class="pb-2 font-medium text-muted">{{ __('messages.consents.signed_by') }}</th>
                        <th class="pb-2 font-medium text-muted">{{ __('messages.common.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consents as $consent)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-2">
                                {{ $consent->user->first_name }} {{ $consent->user->last_name }}
                            </td>
                            <td class="py-2">
                                @if($consent->child)
                                    <span class="badge badge-info">{{ $consent->child->first_name }} {{ $consent->child->last_name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-2">{{ $consent->consentType->name }}</td>
                            <td class="py-2">
                                @if($consent->granted)
                                    <span class="badge badge-success">{{ __('messages.consents.granted_label') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('messages.consents.revoked_label') }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-sm">
                                @if($consent->grantedBy)
                                    {{ $consent->grantedBy->first_name }} {{ $consent->grantedBy->last_name }}
                                @endif
                            </td>
                            <td class="py-2 text-sm text-muted">
                                @if($consent->granted && $consent->granted_at)
                                    {{ $consent->granted_at->format(app()->getLocale() === 'cs' ? 'd.m.Y H:i' : 'Y-m-d H:i') }}
                                @elseif($consent->revoked_at)
                                    {{ $consent->revoked_at->format(app()->getLocale() === 'cs' ? 'd.m.Y H:i' : 'Y-m-d H:i') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-muted">{{ __('messages.consents.no_consents') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($consents->hasPages())
        <div class="mt-4">
            {{ $consents->links() }}
        </div>
    @endif
@endsection
