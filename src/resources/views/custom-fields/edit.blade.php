@extends('layouts.app')

@section('title', __('messages.custom_fields.edit'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.custom_fields.title'), 'href' => route('custom-fields.index')],
        ['label' => __('messages.custom_fields.edit')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.custom_fields.edit') }}</h1>

    <form action="{{ route('custom-fields.update', $customField) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $customField->name) }}" class="form-input @error('name') border-danger @enderror" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.display_name') }}</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $customField->display_name) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.field_type') }} *</label>
                    <select name="field_type" class="form-select" required x-data x-ref="fieldType" @change="$dispatch('field-type-changed', $refs.fieldType.value)">
                        @foreach(['text','textarea','number_int','number_decimal','checkbox','select','multi_select','date'] as $type)
                            <option value="{{ $type }}" @selected(old('field_type', $customField->field_type) === $type)>{{ __('messages.custom_fields.type_' . $type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.suffix') }}</label>
                    <input type="text" name="suffix" value="{{ old('suffix', $customField->suffix) }}" class="form-input">
                </div>
            </div>

            <div x-data="{ showOptions: {{ in_array($customField->field_type, ['select', 'multi_select']) ? 'true' : 'false' }} }" @field-type-changed.window="showOptions = ['select', 'multi_select'].includes($event.detail)">
                <div x-show="showOptions" x-cloak>
                    <label class="form-label">{{ __('messages.custom_fields.options') }}</label>
                    <textarea name="options" rows="4" class="form-input" placeholder="{{ __('messages.custom_fields.options_hint') }}">{{ old('options', is_array($customField->options) ? implode("\n", $customField->options) : '') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.default_value') }}</label>
                    <input type="text" name="default_value" value="{{ old('default_value', $customField->default_value) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.placeholder_text') }}</label>
                    <input type="text" name="placeholder" value="{{ old('placeholder', $customField->placeholder) }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('messages.custom_fields.help_text') }}</label>
                <input type="text" name="help_text" value="{{ old('help_text', $customField->help_text) }}" class="form-input">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.validation_min') }}</label>
                    <input type="number" step="any" name="validation_min" value="{{ old('validation_min', $customField->validation_min) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.validation_max') }}</label>
                    <input type="number" step="any" name="validation_max" value="{{ old('validation_max', $customField->validation_max) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.visibility_read') }}</label>
                    <select name="visibility_read" class="form-select">
                        <option value="everyone" @selected(old('visibility_read', $customField->visibility_read) === 'everyone')>{{ __('messages.custom_fields.vis_everyone') }}</option>
                        <option value="coaches" @selected(old('visibility_read', $customField->visibility_read) === 'coaches')>{{ __('messages.custom_fields.vis_coaches') }}</option>
                        <option value="admins" @selected(old('visibility_read', $customField->visibility_read) === 'admins')>{{ __('messages.custom_fields.vis_admins') }}</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('messages.custom_fields.visibility_write') }}</label>
                    <select name="visibility_write" class="form-select">
                        <option value="member" @selected(old('visibility_write', $customField->visibility_write) === 'member')>{{ __('messages.custom_fields.vis_member') }}</option>
                        <option value="coaches" @selected(old('visibility_write', $customField->visibility_write) === 'coaches')>{{ __('messages.custom_fields.vis_coaches') }}</option>
                        <option value="admins" @selected(old('visibility_write', $customField->visibility_write) === 'admins')>{{ __('messages.custom_fields.vis_admins') }}</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required', $customField->is_required) ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.required') }}
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="show_in_roster" value="0">
                    <input type="checkbox" name="show_in_roster" value="1" {{ old('show_in_roster', $customField->show_in_roster) ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.show_in_roster') }}
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="show_in_registration" value="0">
                    <input type="checkbox" name="show_in_registration" value="1" {{ old('show_in_registration', $customField->show_in_registration) ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.show_in_registration') }}
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customField->is_active) ? 'checked' : '' }} class="form-checkbox">
                    {{ __('messages.custom_fields.active') }}
                </label>
            </div>

            <div class="pt-4">
                <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
            </div>
        </div>
    </form>
@endsection
