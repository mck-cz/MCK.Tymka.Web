@extends('layouts.app')

@section('title', __('messages.penalties.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.penalties.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('penalty-rules.create') }}" class="btn-primary text-sm">{{ __('messages.penalties.create') }}</a>
        @endif
    </div>

    @forelse($rules as $rule)
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $rule->name }}</span>
                            @if($rule->is_active)
                                <span class="badge badge-success">{{ __('messages.penalties.active') }}</span>
                            @else
                                <span class="badge badge-gray">{{ __('messages.penalties.inactive') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-muted mt-1">
                            {{ __('messages.penalties.trigger_' . $rule->trigger_type) }}
                            → {{ __('messages.penalties.type_' . $rule->penalty_type) }}
                            @if($rule->amount)
                                ({{ number_format($rule->amount, 0) }})
                            @endif
                        </p>
                        <p class="text-xs text-muted mt-1">
                            @if($rule->team)
                                {{ $rule->team->name }}
                            @else
                                {{ __('messages.venue_costs.all_teams') }}
                            @endif
                            @if($rule->grace_count > 0)
                                · {{ __('messages.penalties.grace_count') }}: {{ $rule->grace_count }}
                            @endif
                            @if($rule->late_cancel_hours)
                                · {{ __('messages.penalties.late_cancel_hours') }}: {{ $rule->late_cancel_hours }}h
                            @endif
                        </p>
                    </div>
                    @if($isClubAdmin)
                        <div class="flex items-center gap-2">
                            <form action="{{ route('penalty-rules.toggle', $rule) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-ghost text-sm">
                                    {{ $rule->is_active ? __('messages.penalties.deactivate') : __('messages.penalties.activate') }}
                                </button>
                            </form>
                            <a href="{{ route('penalty-rules.edit', $rule) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                            <form action="{{ route('penalty-rules.destroy', $rule) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.penalties.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.penalties.no_rules') }}</p>
            </div>
        </div>
    @endforelse
@endsection
