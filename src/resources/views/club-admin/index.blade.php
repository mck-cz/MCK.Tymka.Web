@extends('layouts.app')

@section('title', __('messages.club_admin.title'))

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
        <div>
            <h1 class="text-xl font-semibold">{{ __('messages.club_admin.title') }}</h1>
            <p class="text-muted mt-1">{{ $club->name }}</p>
        </div>
        <a href="{{ route('club-admin.settings') }}" class="btn-secondary text-sm">{{ __('messages.club_admin.settings') }}</a>
    </div>

    {{-- Pending Join Requests --}}
    @if($pendingRequests->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header">
                <h2 class="font-medium">
                    {{ __('messages.club_admin.pending_requests') }}
                    <span class="badge badge-accent ml-2">{{ $pendingRequests->count() }}</span>
                </h2>
            </div>
            <div class="card-body">
                @foreach($pendingRequests as $joinRequest)
                    <div class="flex items-center justify-between py-3 @if(!$loop->last) border-b border-border @endif">
                        <div>
                            <div class="font-medium">{{ $joinRequest->user->full_name }}</div>
                            <div class="text-sm text-muted">{{ $joinRequest->user->email }}</div>
                            @if($joinRequest->message)
                                <p class="text-sm text-muted mt-1 italic">"{{ $joinRequest->message }}"</p>
                            @endif
                            <div class="text-xs text-muted mt-1">
                                {{ app()->getLocale() === 'cs' ? $joinRequest->created_at->format('d.m.Y H:i') : $joinRequest->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                        <div class="flex gap-2 shrink-0 ml-4">
                            <form action="{{ route('club-admin.approve-request', $joinRequest) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-primary text-sm">{{ __('messages.club_admin.approve') }}</button>
                            </form>
                            <form action="{{ route('club-admin.reject-request', $joinRequest) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.club_admin.reject_confirm') }}')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-danger text-sm">{{ __('messages.club_admin.reject') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Members --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">
                {{ __('messages.club_admin.members') }}
                <span class="text-muted font-normal text-sm">({{ $members->count() }})</span>
            </h2>
        </div>
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="pb-2 font-medium">{{ __('messages.club_admin.member_name') }}</th>
                            <th class="pb-2 font-medium">{{ __('messages.club_admin.email') }}</th>
                            <th class="pb-2 font-medium">{{ __('messages.club_admin.club_role') }}</th>
                            <th class="pb-2 font-medium">{{ __('messages.club_admin.joined_at') }}</th>
                            @if($membership->role === 'owner')
                                <th class="pb-2 font-medium"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                            {{ strtoupper(mb_substr($member->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($member->user->last_name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium">{{ $member->user->full_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-muted">{{ $member->user->email }}</td>
                                <td class="py-3">
                                    @if($member->role === 'owner')
                                        <span class="badge badge-accent">{{ __('messages.club_admin.role_owner') }}</span>
                                    @elseif($member->role === 'admin')
                                        <span class="badge badge-primary">{{ __('messages.club_admin.role_admin') }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ __('messages.club_admin.role_member') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $member->joined_at ? (app()->getLocale() === 'cs' ? $member->joined_at->format('d.m.Y') : $member->joined_at->format('Y-m-d')) : '—' }}
                                </td>
                                @if($membership->role === 'owner')
                                    <td class="py-3 text-right">
                                        @if($member->user_id !== auth()->id())
                                            <div class="flex items-center justify-end gap-2" x-data="{ showRole: false }">
                                                <button @click="showRole = !showRole" class="btn-ghost text-sm">
                                                    {{ __('messages.club_admin.change_role') }}
                                                </button>
                                                <div x-show="showRole" x-cloak class="flex items-center gap-1">
                                                    <form action="{{ route('club-admin.update-role', $member) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="role" onchange="this.form.submit()" class="form-select text-sm py-1">
                                                            <option value="admin" @selected($member->role === 'admin')>{{ __('messages.club_admin.role_admin') }}</option>
                                                            <option value="member" @selected($member->role === 'member')>{{ __('messages.club_admin.role_member') }}</option>
                                                        </select>
                                                    </form>
                                                </div>
                                                <form action="{{ route('club-admin.remove-member', $member) }}" method="POST"
                                                    onsubmit="return confirm('{{ __('messages.club_admin.remove_confirm') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-ghost text-danger text-sm" title="{{ __('messages.common.delete') }}" aria-label="{{ __('messages.common.delete') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
