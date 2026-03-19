@extends('layouts.app')

@section('title', __('messages.templates.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.templates.title') }}</h1>

    {{-- Equipment Templates --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.templates.equipment') }}</h2>
        </div>
        <div class="card-body">
            @if($equipmentTemplates->isEmpty())
                <p class="text-muted mb-4">{{ __('messages.templates.no_equipment') }}</p>
            @else
                <div class="space-y-2 mb-4">
                    @foreach($equipmentTemplates as $template)
                        <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $template->name }}</span>
                                <span class="badge badge-gray text-xs">{{ __('messages.events.' . $template->event_type) }}</span>
                            </div>
                            <form action="{{ route('templates.destroy-equipment', $template) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.templates.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-danger text-sm">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('templates.store-equipment') }}" method="POST" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="eq_name" class="form-label">{{ __('messages.templates.item_name') }}</label>
                    <input type="text" name="name" id="eq_name" class="form-input w-full" required>
                </div>
                <div>
                    <label for="eq_event_type" class="form-label">{{ __('messages.events.type') }}</label>
                    <select name="event_type" id="eq_event_type" class="form-select" required>
                        <option value="training">{{ __('messages.events.training') }}</option>
                        <option value="match">{{ __('messages.events.match') }}</option>
                        <option value="competition">{{ __('messages.events.competition') }}</option>
                        <option value="tournament">{{ __('messages.events.tournament') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary shrink-0">{{ __('messages.teams.add') }}</button>
            </form>
        </div>
    </div>

    {{-- Instruction Templates --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.templates.instructions') }}</h2>
        </div>
        <div class="card-body">
            @if($instructionTemplates->isEmpty())
                <p class="text-muted mb-4">{{ __('messages.templates.no_instructions') }}</p>
            @else
                <div class="space-y-2 mb-4">
                    @foreach($instructionTemplates as $template)
                        <div class="flex items-start justify-between py-2 border-b border-border last:border-0">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $template->name }}</span>
                                    <span class="badge badge-gray text-xs">{{ __('messages.events.' . $template->event_type) }}</span>
                                </div>
                                <p class="text-sm text-muted mt-1">{{ Str::limit($template->body, 100) }}</p>
                            </div>
                            <form action="{{ route('templates.destroy-instruction', $template) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.templates.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-danger text-sm shrink-0">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('templates.store-instruction') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="instr_name" class="form-label">{{ __('messages.templates.template_name') }}</label>
                        <input type="text" name="name" id="instr_name" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="instr_event_type" class="form-label">{{ __('messages.events.type') }}</label>
                        <select name="event_type" id="instr_event_type" class="form-select w-full" required>
                            <option value="training">{{ __('messages.events.training') }}</option>
                            <option value="match">{{ __('messages.events.match') }}</option>
                            <option value="competition">{{ __('messages.events.competition') }}</option>
                            <option value="tournament">{{ __('messages.events.tournament') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="instr_body" class="form-label">{{ __('messages.templates.template_body') }}</label>
                    <textarea name="body" id="instr_body" rows="3" class="form-input w-full" required></textarea>
                </div>
                <button type="submit" class="btn-primary">{{ __('messages.teams.add') }}</button>
            </form>
        </div>
    </div>
@endsection
