@extends('layouts.app')

@section('title', __('messages.seasons.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.seasons.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('seasons.create') }}" class="btn-primary">{{ __('messages.seasons.create') }}</a>
        @endif
    </div>

    @if($seasons->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.seasons.no_seasons') }}</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="pb-2 font-medium">{{ __('messages.seasons.name') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.seasons.start_date') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.seasons.end_date') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.seasons.teams_count') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.seasons.status') }}</th>
                                @if($isClubAdmin)
                                    <th class="pb-2 font-medium"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seasons as $season)
                                @php
                                    $isActive = $season->start_date->lte(now()) && $season->end_date->gte(now());
                                @endphp
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-3 font-medium">{{ $season->name }}</td>
                                    <td class="py-3 text-muted">
                                        {{ app()->getLocale() === 'cs' ? $season->start_date->format('d.m.Y') : $season->start_date->format('Y-m-d') }}
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ app()->getLocale() === 'cs' ? $season->end_date->format('d.m.Y') : $season->end_date->format('Y-m-d') }}
                                    </td>
                                    <td class="py-3 text-muted">{{ $season->teams_count }}</td>
                                    <td class="py-3">
                                        @if($isActive)
                                            <span class="badge badge-success">{{ __('messages.seasons.active') }}</span>
                                        @elseif($season->start_date->isFuture())
                                            <span class="badge badge-primary">{{ __('messages.seasons.upcoming') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ __('messages.seasons.ended') }}</span>
                                        @endif
                                    </td>
                                    @if($isClubAdmin)
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('seasons.edit', $season) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                                                @if($season->teams_count === 0)
                                                    <form action="{{ route('seasons.destroy', $season) }}" method="POST"
                                                        onsubmit="return confirm('{{ __('messages.seasons.delete_confirm') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-ghost text-danger text-sm">{{ __('messages.common.delete') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
