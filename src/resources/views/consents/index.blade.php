@extends('layouts.app')

@section('title', __('messages.consents.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.consents.title') }}</h1>

    {{-- User's own consents --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.consents.my_consents') }}</h2>
        </div>
        <div class="card-body">
            @forelse($consentTypes as $type)
                <div class="flex items-center justify-between py-3 @if(!$loop->last) border-b border-border @endif">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $type->name }}</span>
                            @if($type->is_required)
                                <span class="badge badge-warning">{{ __('messages.consents.required') }}</span>
                            @endif
                        </div>
                        @if($type->description)
                            <p class="text-sm text-muted mt-1">{{ $type->description }}</p>
                        @endif
                        @if($type->content)
                            <a href="{{ route('consent-types.show', $type) }}" class="text-xs text-primary hover:underline mt-1 inline-block">{{ __('messages.consents.read_full_text') }}</a>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        @php $consent = $userConsents[$type->id] ?? null; @endphp
                        @if($consent && $consent->granted)
                            <span class="text-success text-sm font-medium">&#10003; {{ __('messages.consents.granted_label') }}</span>
                            @if(!$type->is_required)
                                <form action="{{ route('consents.revoke', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.consents.revoke') }}</button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('consents.grant', $type) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn-primary text-sm">{{ __('messages.consents.grant') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.consents.no_types') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Children's consents --}}
    @if($children->isNotEmpty())
        @foreach($children as $child)
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-medium">{{ __('messages.consents.child_consents') }}: {{ $child->first_name }} {{ $child->last_name }}</h2>
                </div>
                <div class="card-body">
                    @foreach($consentTypes as $type)
                        <div class="flex items-center justify-between py-3 @if(!$loop->last) border-b border-border @endif">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $type->name }}</span>
                                    @if($type->is_required)
                                        <span class="badge badge-warning">{{ __('messages.consents.required') }}</span>
                                    @endif
                                </div>
                                @if($type->description)
                                    <p class="text-sm text-muted mt-1">{{ $type->description }}</p>
                                @endif
                                @if($type->content)
                                    <a href="{{ route('consent-types.show', $type) }}" class="text-xs text-primary hover:underline mt-1 inline-block">{{ __('messages.consents.read_full_text') }}</a>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-4">
                                @php $childConsent = ($childConsents[$child->id] ?? collect())[$type->id] ?? null; @endphp
                                @if($childConsent && $childConsent->granted)
                                    <span class="text-success text-sm font-medium">&#10003; {{ __('messages.consents.granted_label') }}</span>
                                    @if(!$type->is_required)
                                        <form action="{{ route('consents.revoke', $type) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="child_id" value="{{ $child->id }}">
                                            <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.consents.revoke') }}</button>
                                        </form>
                                    @endif
                                @else
                                    <form action="{{ route('consents.grant', $type) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="child_id" value="{{ $child->id }}">
                                        <button type="submit" class="btn-primary text-sm">{{ __('messages.consents.grant') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Admin section --}}
    @if($isClubAdmin ?? false)
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-medium">{{ __('messages.consents.manage_types') }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('consents.overview') }}" class="btn-secondary text-sm">{{ __('messages.consents.overview') }}</a>
                    <a href="{{ route('consent-types.create') }}" class="btn-primary text-sm">{{ __('messages.consents.add_type') }}</a>
                </div>
            </div>
            <div class="card-body">
                @forelse($consentTypes as $type)
                    <div class="flex items-center justify-between py-2 @if(!$loop->last) border-b border-border @endif">
                        <div>
                            <span class="font-medium">{{ $type->name }}</span>
                            @if($type->is_required)
                                <span class="badge badge-warning text-xs">{{ __('messages.consents.required') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('consent-types.edit', $type) }}" class="text-xs text-primary hover:underline">{{ __('messages.common.edit') }}</a>
                            <form action="{{ route('consent-types.destroy', $type) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.consents.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">{{ __('messages.consents.no_types') }}</p>
                @endforelse
            </div>
        </div>
    @endif
@endsection
