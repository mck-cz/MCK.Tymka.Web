@extends('layouts.app')

@section('title', __('messages.attendance_check.title'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.events.title'), 'href' => route('events.index')],
        ['label' => $event->title, 'href' => route('events.show', $event)],
        ['label' => __('messages.attendance_check.title')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('messages.attendance_check.title') }}</h1>
        <p class="text-muted mt-1">{{ $event->title }} — {{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.Y H:i') : $event->starts_at->format('Y-m-d H:i') }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('attendance-check.update', $event) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    @foreach($attendances as $attendance)
                        <div class="flex items-center justify-between py-3 border-b border-border last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                    {{ strtoupper(mb_substr($attendance->teamMembership->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($attendance->teamMembership->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $attendance->teamMembership->user->full_name }}</div>
                                    <div class="text-xs">
                                        @if($attendance->rsvp_status === 'confirmed')
                                            <span class="text-success">{{ __('messages.rsvp.confirmed') }}</span>
                                        @elseif($attendance->rsvp_status === 'declined')
                                            <span class="text-danger">{{ __('messages.rsvp.declined') }}</span>
                                        @else
                                            <span class="text-muted">{{ __('messages.rsvp.pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="attendance[{{ $attendance->id }}]" value="present"
                                        {{ ($attendance->actual_status === 'present') ? 'checked' : '' }}
                                        class="form-radio text-success">
                                    <span class="text-sm">{{ __('messages.attendance_check.present') }}</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="attendance[{{ $attendance->id }}]" value="absent"
                                        {{ ($attendance->actual_status === 'absent' || !$attendance->actual_status) ? 'checked' : '' }}
                                        class="form-radio text-danger">
                                    <span class="text-sm">{{ __('messages.attendance_check.absent') }}</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('messages.attendance_check.save') }}</button>
                    <a href="{{ route('events.show', $event) }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
