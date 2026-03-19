<div class="flex items-center gap-3 py-2 @if(!($isLast ?? false)) border-b border-border @endif">
    {{-- Status icon --}}
    @if($attendance->rsvp_status === 'confirmed')
        <span class="text-success text-lg">&#10003;</span>
    @elseif($attendance->rsvp_status === 'declined')
        <span class="text-danger text-lg">&#10005;</span>
    @else
        <span class="text-muted text-lg">&#9201;</span>
    @endif

    {{-- Name --}}
    <div class="flex-1">
        <span class="font-medium">{{ $attendance->teamMembership->user->full_name }}</span>
        @if($attendance->rsvp_note)
            <p class="text-sm text-muted">{{ $attendance->rsvp_note }}</p>
        @endif
    </div>

    {{-- Actual attendance status --}}
    @if($attendance->actual_status === 'present')
        <span class="badge badge-success">{{ __('messages.attendance_check.present') }}</span>
    @elseif($attendance->actual_status === 'absent')
        <span class="badge badge-danger">{{ __('messages.attendance_check.absent') }}</span>
    @endif

    {{-- RSVP status + date --}}
    @if($attendance->rsvp_status === 'confirmed')
        <span class="text-success text-sm">{{ __('messages.rsvp.confirmed') }}</span>
        @if($attendance->responded_at)
            <span class="text-muted text-xs">{{ app()->getLocale() === 'cs' ? $attendance->responded_at->format('d.m. H:i') : $attendance->responded_at->format('M d H:i') }}</span>
        @endif
    @elseif($attendance->rsvp_status === 'declined')
        <span class="text-danger text-sm">{{ __('messages.rsvp.declined') }}</span>
        @if($attendance->responded_at)
            <span class="text-muted text-xs">{{ app()->getLocale() === 'cs' ? $attendance->responded_at->format('d.m. H:i') : $attendance->responded_at->format('M d H:i') }}</span>
        @endif
    @else
        <span class="text-muted text-sm">{{ __('messages.rsvp.pending') }}</span>
    @endif
</div>
