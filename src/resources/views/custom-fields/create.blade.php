@extends('layouts.app')

@section('title', __('messages.custom_fields.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.custom_fields.title'), 'href' => route('custom-fields.index')],
        ['label' => __('messages.custom_fields.create')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.custom_fields.create') }}</h1>

    <form action="{{ route('custom-fields.store') }}" method="POST" class="card">
        @csrf
        <div class="card-body space-y-4">
            <input type="hidden" name="entity_type" value="member">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-danger @enderror" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.display_name') }}</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" class="form-input">
                    <p class="text-xs text-muted mt-1">{{ __('messages.custom_fields.display_name_hint') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.field_type') }} *</label>
                    <select name="field_type" class="form-select @error('field_type') border-danger @enderror" required x-data x-ref="fieldType" @change="$dispatch('field-type-changed', $refs.fieldType.value)">
                        <option value="text" @selected(old('field_type') === 'text')>{{ __('messages.custom_fields.type_text') }}</option>
                        <option value="textarea" @selected(old('field_type') === 'textarea')>{{ __('messages.custom_fields.type_textarea') }}</option>
                        <option value="number_int" @selected(old('field_type') === 'number_int')>{{ __('messages.custom_fields.type_number_int') }}</option>
                        <option value="number_decimal" @selected(old('field_type') === 'number_decimal')>{{ __('messages.custom_fields.type_number_decimal') }}</option>
                        <option value="checkbox" @selected(old('field_type') === 'checkbox')>{{ __('messages.custom_fields.type_checkbox') }}</option>
                        <option value="select" @selected(old('field_type') === 'select')>{{ __('messages.custom_fields.type_select') }}</option>
                        <option value="multi_select" @selected(old('field_type') === 'multi_select')>{{ __('messages.custom_fields.type_multi_select') }}</option>
                        <option value="date" @selected(old('field_type') === 'date')>{{ __('messages.custom_fields.type_date') }}</option>
                    </select>
                    @error('field_type') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.suffix') }}</label>
                    <input type="text" name="suffix" value="{{ old('suffix') }}" class="form-input" placeholder="kg, cm, min...">
                </div>
            </div>

            <div x-data="{ showOptions: false }" @field-type-changed.window="showOptions = ['select', 'multi_select'].includes($event.detail)">
                <div x-show="showOptions" x-cloak>
                    <label class="form-label">{{ __('messages.custom_fields.options') }}</label>
                    <textarea name="options" rows="4" class="form-input" placeholder="{{ __('messages.custom_fields.options_hint') }}">{{ old('options') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.default_value') }}</label>
                    <input type="text" name="default_value" value="{{ old('default_value') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.placeholder_text') }}</label>
                    <input type="text" name="placeholder" value="{{ old('placeholder') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('messages.custom_fields.help_text') }}</label>
                <input type="text" name="help_text" value="{{ old('help_text') }}" class="form-input">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.validation_min') }}</label>
                    <input type="number" step="any" name="validation_min" value="{{ old('validation_min') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.validation_max') }}</label>
                    <input type="number" step="any" name="validation_max" value="{{ old('validation_max') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.visibility_read') }}</label>
                    <select name="visibility_read" class="form-select">
                        <option value="everyone" @selected(old('visibility_read', 'everyone') === 'everyone')>{{ __('messages.custom_fields.vis_everyone') }}</option>
                        <option value="coaches" @selected(old('visibility_read') === 'coaches')>{{ __('messages.custom_fields.vis_coaches') }}</option>
                        <option value="admins" @selected(old('visibility_read') === 'admins')>{{ __('messages.custom_fields.vis_admins') }}</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.visibility_write') }}</label>
                    <select name="visibility_write" class="form-select">
                        <option value="member" @selected(old('visibility_write') === 'member')>{{ __('messages.custom_fields.vis_member') }}</option>
                        <option value="coaches" @selected(old('visibility_write', 'coaches') === 'coaches')>{{ __('messages.custom_fields.vis_coaches') }}</option>
                        <option value="admins" @selected(old('visibility_write') === 'admins')>{{ __('messages.custom_fields.vis_admins') }}</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.required') }}
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="show_in_roster" value="0">
                    <input type="checkbox" name="show_in_roster" value="1" {{ old('show_in_roster') ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.show_in_roster') }}
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="show_in_registration" value="0">
                    <input type="checkbox" name="show_in_registration" value="1" {{ old('show_in_registration') ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.show_in_registration') }}
                </label>
            </div>

            <div class="pt-4">
                <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
            </div>
        </div>
    </form>
@endsection
