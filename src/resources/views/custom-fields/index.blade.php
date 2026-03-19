@extends('layouts.app')

@section('title', __('messages.custom_fields.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.custom_fields.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('custom-fields.create') }}" class="btn-primary text-sm">{{ __('messages.custom_fields.create') }}</a>
        @endif
    </div>

    @forelse($fields as $field)
        <div class="card mb-3">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $field->display_name ?? $field->name }}</span>
                            <span class="badge badge-gray">{{ __('messages.custom_fields.type_' . $field->field_type) }}</span>
                            @if(!$field->is_active)
                                <span class="badge badge-danger">{{ __('messages.custom_fields.inactive') }}</span>
                            @endif
                            @if($field->is_required)
                                <span class="badge badge-accent">{{ __('messages.custom_fields.required') }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-3 mt-1 text-xs text-muted">
                            @if($field->suffix)
                                <span>{{ __('messages.custom_fields.suffix') }}: {{ $field->suffix }}</span>
                            @endif
                            <span>{{ __('messages.custom_fields.read') }}: {{ __('messages.custom_fields.vis_' . $field->visibility_read) }}</span>
                            <span>{{ __('messages.custom_fields.write') }}: {{ __('messages.custom_fields.vis_' . $field->visibility_write) }}</span>
                            @if($field->show_in_roster)
                                <span class="text-primary">{{ __('messages.custom_fields.in_roster') }}</span>
                            @endif
                        </div>
                        @if($field->help_text)
                            <p class="text-xs text-muted mt-1">{{ $field->help_text }}</p>
                        @endif
                    </div>
                    @if($isClubAdmin)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('custom-fields.edit', $field) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                            <form action="{{ route('custom-fields.destroy', $field) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.custom_fields.delete_confirm') }}')">
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
                <p class="text-muted">{{ __('messages.custom_fields.no_fields') }}</p>
            </div>
        </div>
    @endforelse
@endsection
