@extends('layouts.app')

@section('title', __('messages.absences.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.absences.title') }}</h1>
        <a href="{{ route('absences.create') }}" class="btn-primary text-sm">{{ __('messages.absences.create') }}</a>
    </div>

    @if($absences->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.absences.no_absences') }}</p>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach($absences as $absence)
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="badge badge-gray">{{ __('messages.absences.reason_' . $absence->reason_type) }}</span>
                                    <span class="text-sm text-muted">
                                        {{ app()->getLocale() === 'cs' ? $absence->starts_at->format('d.m.Y') : $absence->starts_at->format('Y-m-d') }}
                                        —
                                        {{ app()->getLocale() === 'cs' ? $absence->ends_at->format('d.m.Y') : $absence->ends_at->format('Y-m-d') }}
                                    </span>
                                </div>
                                @if($absence->reason_note)
                                    <p class="text-sm text-muted">{{ $absence->reason_note }}</p>
                                @endif
                            </div>
                            <form action="{{ route('absences.destroy', $absence) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.absences.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-danger text-sm">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
