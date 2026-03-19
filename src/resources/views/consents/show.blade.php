@extends('layouts.app')

@section('title', $consentType->name)

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.consents.title'), 'href' => route('consents.index')],
        ['label' => $consentType->name],
    ]" />

    <div class="max-w-3xl">
        <h1 class="text-xl font-semibold mb-2">{{ $consentType->name }}</h1>

        @if($consentType->is_required)
            <span class="badge badge-warning mb-4 inline-block">{{ __('messages.consents.required') }}</span>
        @endif

        @if($consentType->description)
            <p class="text-text-secondary mb-4">{{ $consentType->description }}</p>
        @endif

        @if($consentType->content)
            <div class="card">
                <div class="card-body prose prose-sm max-w-none">
                    {!! $consentType->content !!}
                </div>
            </div>
        @endif
    </div>
@endsection
