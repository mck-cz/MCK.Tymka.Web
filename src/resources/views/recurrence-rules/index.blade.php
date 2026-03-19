@extends('layouts.app')

@section('title', __('messages.recurrence.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.recurrence.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('recurrence-rules.create') }}" class="btn-primary text-sm">{{ __('messages.recurrence.create') }}</a>
        @endif
    </div>

    @if($rules->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.recurrence.no_rules') }}</p>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach($rules as $rule)
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium">{{ $rule->name ?? $rule->team->name . ' — ' . __('messages.events.' . $rule->event_type) }}</span>
                                    @if(!$rule->is_active)
                                        <span class="badge badge-gray">{{ __('messages.recurrence.inactive') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('messages.recurrence.active') }}</span>
                                    @endif
                                </div>
                                <div class="text-sm text-muted flex flex-wrap gap-3">
                                    <span>{{ __('messages.recurrence.day_' . $rule->day_of_week) }}</span>
                                    <span>{{ \Carbon\Carbon::createFromFormat('H:i:s', $rule->time_start)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $rule->time_end)->format('H:i') }}</span>
                                    <span>{{ __('messages.recurrence.freq_' . ($rule->interval === 2 ? 'biweekly' : $rule->frequency)) }}</span>
                                    @if($rule->venue)
                                        <span>{{ $rule->venue->name }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    {{ app()->getLocale() === 'cs' ? $rule->valid_from->format('d.m.Y') : $rule->valid_from->format('Y-m-d') }}
                                    @if($rule->valid_until)
                                        — {{ app()->getLocale() === 'cs' ? $rule->valid_until->format('d.m.Y') : $rule->valid_until->format('Y-m-d') }}
                                    @else
                                        — {{ __('messages.recurrence.no_end') }}
                                    @endif
                                </div>
                            </div>
                            @if($isClubAdmin)
                                <div class="flex items-center gap-2 shrink-0 ml-4">
                                    <a href="{{ route('recurrence-rules.edit', $rule) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                                    <form action="{{ route('recurrence-rules.toggle', $rule) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-ghost text-sm">
                                            {{ $rule->is_active ? __('messages.recurrence.deactivate') : __('messages.recurrence.activate') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('recurrence-rules.destroy', $rule) }}" method="POST"
                                        onsubmit="return confirm('{{ __('messages.recurrence.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost text-danger text-sm">{{ __('messages.common.delete') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
