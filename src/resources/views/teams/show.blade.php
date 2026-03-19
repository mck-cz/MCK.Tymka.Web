@extends('layouts.app')

@section('title', $team->name)

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

    <x-breadcrumb :items="[
        ['label' => __('messages.teams.title'), 'href' => route('teams.index')],
        ['label' => $team->name],
    ]" />

    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                @if($team->color)
                    <div class="w-5 h-5 rounded-full shrink-0" style="background-color: {{ $team->color }}"></div>
                @endif
                <h1 class="text-2xl font-semibold">{{ $team->name }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('teams.wall', $team) }}" class="btn-ghost text-sm">{{ __('messages.wall.title') }}</a>
                @if($canEdit ?? false)
                    <a href="{{ route('teams.edit', $team) }}" class="btn-secondary text-sm">{{ __('messages.common.edit') }}</a>
                    @if($canDelete ?? false)
                        <form action="{{ route('teams.destroy', $team) }}" method="POST"
                            onsubmit="return confirm('{{ __('messages.teams.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-sm">{{ __('messages.common.delete') }}</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($team->sport)
                <span class="badge badge-primary">{{ $team->sport }}</span>
            @endif
            @if($team->age_category)
                <span class="badge badge-gray">{{ $team->age_category }}</span>
            @endif
        </div>
    </div>

    {{-- Roster --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.teams.roster') }}</h2>
        </div>
        <div class="card-body">
            @if($team->teamMemberships->isEmpty())
                <p class="text-muted">{{ __('messages.teams.no_members') }}</p>
            @elseif($isParent && !($isDirectMember ?? false) && !($canEdit ?? false))
                {{-- Parent-only view: coaches with names, athlete count --}}
                @php
                    $coaches = $team->teamMemberships->filter(fn ($m) => in_array($m->role, ['head_coach', 'assistant_coach']));
                    $athleteCount = $team->teamMemberships->filter(fn ($m) => $m->role === 'athlete')->count();
                @endphp

                @if($coaches->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-sm text-muted mb-2">{{ __('messages.teams.coaches') }}</p>
                        @foreach($coaches as $coach)
                            <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-2' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                    {{ strtoupper(mb_substr($coach->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($coach->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-medium">{{ $coach->user->full_name }}</span>
                                    @if($coach->role === 'head_coach')
                                        <span class="badge badge-success text-xs ml-1">{{ __('messages.teams.head_coach') }}</span>
                                    @else
                                        <span class="badge badge-primary text-xs ml-1">{{ __('messages.teams.assistant_coach') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center gap-2 text-sm text-muted">
                    <span class="badge badge-gray">{{ $athleteCount }} {{ __('messages.teams.athletes_count') }}</span>
                </div>
            @else
                {{-- Full roster for team members, coaches, admins --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="pb-2 font-medium">{{ __('messages.teams.members') }}</th>
                                <th class="pb-2 font-medium">#</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.role') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.position') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.joined') }}</th>
                                @if($canEdit ?? false)
                                    <th class="pb-2 font-medium"></th>
                                @endif
                            </tr>
                        </thead>
                            @foreach($team->teamMemberships as $membership)
                            <tbody x-data="{ editing: false, detail: false }">
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                                {{ strtoupper(mb_substr($membership->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($membership->user->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="font-medium">{{ $membership->user->full_name }}</span>
                                                @if($membership->user->status === 'placeholder')
                                                    <span class="badge badge-warning text-xs">{{ __('messages.placeholder.badge') }}</span>
                                                    @if($membership->user->guardians->isEmpty())
                                                        <span class="badge badge-danger text-xs">{{ __('messages.placeholder.not_linked') }}</span>
                                                    @else
                                                        <span class="badge badge-success text-xs">{{ __('messages.placeholder.linked') }}</span>
                                                    @endif
                                                @endif
                                                @if($membership->federation_id)
                                                    <span class="text-xs text-muted ml-1">({{ $membership->federation_id }})</span>
                                                @endif
                                                @if($membership->user->status === 'placeholder' && $membership->user->guardians->isNotEmpty())
                                                    <p class="text-xs text-muted">
                                                        {{ __('messages.placeholder.guardian_short') }}:
                                                        {{ $membership->user->guardians->map(fn ($g) => $g->guardian->full_name)->implode(', ') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-muted">
                                        <template x-if="!editing">
                                            <span>{{ $membership->jersey_number ?? '—' }}</span>
                                        </template>
                                        <template x-if="editing">
                                            <input type="number" name="jersey_number" form="edit-member-{{ $membership->id }}"
                                                value="{{ $membership->jersey_number }}" min="0" max="999"
                                                class="form-input text-sm" style="width: 60px;">
                                        </template>
                                    </td>
                                    <td class="py-3">
                                        <template x-if="!editing">
                                            <div>
                                                @if($membership->role === 'head_coach')
                                                    <span class="badge badge-success">{{ __('messages.teams.head_coach') }}</span>
                                                @elseif($membership->role === 'assistant_coach')
                                                    <span class="badge badge-primary">{{ __('messages.teams.assistant_coach') }}</span>
                                                @else
                                                    <span class="badge badge-gray">{{ __('messages.teams.athlete') }}</span>
                                                @endif
                                            </div>
                                        </template>
                                        <template x-if="editing">
                                            <select name="role" form="edit-member-{{ $membership->id }}" class="form-select text-sm">
                                                <option value="athlete" @selected($membership->role === 'athlete')>{{ __('messages.teams.athlete') }}</option>
                                                <option value="assistant_coach" @selected($membership->role === 'assistant_coach')>{{ __('messages.teams.assistant_coach') }}</option>
                                                <option value="head_coach" @selected($membership->role === 'head_coach')>{{ __('messages.teams.head_coach') }}</option>
                                            </select>
                                        </template>
                                    </td>
                                    <td class="py-3">
                                        <template x-if="!editing">
                                            <span class="text-muted">{{ $membership->position ?? '—' }}</span>
                                        </template>
                                        <template x-if="editing">
                                            <input type="text" name="position" form="edit-member-{{ $membership->id }}"
                                                value="{{ $membership->position }}"
                                                class="form-input text-sm w-full" style="min-width: 100px;">
                                        </template>
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ $membership->joined_at ? (app()->getLocale() === 'cs' ? $membership->joined_at->format('d.m.Y') : $membership->joined_at->format('Y-m-d')) : '—' }}
                                    </td>
                                    @if($canEdit ?? false)
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                {{-- Detail toggle --}}
                                                <template x-if="!editing">
                                                    <button type="button" @click="detail = !detail" class="btn-ghost text-sm" title="{{ __('messages.teams.member_detail') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': detail }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                {{-- Edit toggle --}}
                                                <template x-if="!editing">
                                                    <button type="button" @click="editing = true" class="btn-ghost text-sm" title="{{ __('messages.teams.edit_member') }}" aria-label="{{ __('messages.teams.edit_member') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                {{-- Save / Cancel --}}
                                                <template x-if="editing">
                                                    <div class="flex items-center gap-1">
                                                        <form id="edit-member-{{ $membership->id }}" action="{{ route('teams.update-member', [$team, $membership]) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn-ghost text-success text-sm" title="{{ __('messages.common.save') }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                        <button type="button" @click="editing = false" class="btn-ghost text-muted text-sm" title="{{ __('messages.common.cancel') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                                {{-- Remove --}}
                                                <template x-if="!editing">
                                                    <form action="{{ route('teams.remove-member', [$team, $membership]) }}" method="POST"
                                                        onsubmit="return confirm('{{ __('messages.teams.remove_confirm') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-ghost text-danger" title="{{ __('messages.common.delete') }}" aria-label="{{ __('messages.common.delete') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </template>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                {{-- Detail row (federation, license) --}}
                                <tr x-show="detail || editing" x-cloak class="border-b border-border last:border-0 bg-bg">
                                    <td colspan="{{ ($canEdit ?? false) ? 6 : 5 }}" class="py-3 px-4">
                                        <template x-if="!editing">
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                <div>
                                                    <span class="text-muted text-xs">{{ __('messages.teams.federation_id') }}</span>
                                                    <p>{{ $membership->federation_id ?? '—' }}</p>
                                                </div>
                                                <div>
                                                    <span class="text-muted text-xs">{{ __('messages.teams.federation_status') }}</span>
                                                    <p>{{ $membership->federation_status ? __('messages.teams.fed_status_' . $membership->federation_status) : '—' }}</p>
                                                </div>
                                                <div>
                                                    <span class="text-muted text-xs">{{ __('messages.teams.federation_valid_until') }}</span>
                                                    <p>
                                                        @if($membership->federation_membership_valid_until)
                                                            {{ app()->getLocale() === 'cs' ? $membership->federation_membership_valid_until->format('d.m.Y') : $membership->federation_membership_valid_until->format('Y-m-d') }}
                                                            @if($membership->federation_membership_valid_until->isPast())
                                                                <span class="badge badge-danger text-xs ml-1">{{ __('messages.teams.expired') }}</span>
                                                            @elseif($membership->federation_membership_valid_until->diffInDays(now()) <= 30)
                                                                <span class="badge badge-accent text-xs ml-1">{{ __('messages.teams.expiring_soon') }}</span>
                                                            @endif
                                                        @else
                                                            —
                                                        @endif
                                                    </p>
                                                </div>
                                                <div>
                                                    <span class="text-muted text-xs">{{ __('messages.teams.license') }}</span>
                                                    <p>
                                                        {{ $membership->license_type ?? '—' }}
                                                        @if($membership->license_valid_until)
                                                            <span class="text-muted text-xs">({{ app()->getLocale() === 'cs' ? $membership->license_valid_until->format('d.m.Y') : $membership->license_valid_until->format('Y-m-d') }})</span>
                                                            @if($membership->license_valid_until->isPast())
                                                                <span class="badge badge-danger text-xs ml-1">{{ __('messages.teams.expired') }}</span>
                                                            @endif
                                                        @endif
                                                    </p>
                                                </div>
                                                @if($membership->federation_external_url)
                                                    <div class="col-span-2">
                                                        <span class="text-muted text-xs">{{ __('messages.teams.federation_link') }}</span>
                                                        <p><a href="{{ $membership->federation_external_url }}" target="_blank" rel="noopener" class="text-primary hover:underline">{{ __('messages.teams.open_profile') }}</a></p>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="text-muted text-xs">{{ __('messages.teams.attendance_required_label') }}</span>
                                                    <p>{{ $membership->attendance_required ? __('messages.common.yes') : __('messages.common.no') }}</p>
                                                </div>
                                            </div>

                                            {{-- Guardian info for placeholder members --}}
                                            @if($membership->user->status === 'placeholder')
                                                <div class="mt-4 pt-4 border-t border-border">
                                                    <p class="text-xs font-medium text-muted mb-2">{{ __('messages.placeholder.guardian_section') }}</p>
                                                    @if($membership->user->guardians->isNotEmpty())
                                                        @foreach($membership->user->guardians as $guardianRel)
                                                            <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-2' : '' }}">
                                                                <div class="w-6 h-6 rounded-full bg-primary-light text-primary flex items-center justify-center text-[10px] font-medium shrink-0">
                                                                    {{ strtoupper(mb_substr($guardianRel->guardian->first_name, 0, 1)) }}{{ strtoupper(mb_substr($guardianRel->guardian->last_name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <span class="text-sm font-medium">{{ $guardianRel->guardian->full_name }}</span>
                                                                    <span class="badge badge-success text-xs ml-1">{{ __('messages.placeholder.linked') }}</span>
                                                                    <p class="text-xs text-muted">{{ $guardianRel->guardian->email }} · {{ $guardianRel->relationship }}</p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $claim = ($pendingClaims ?? collect())->get($membership->user->id);
                                                        @endphp
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="badge badge-warning text-xs">{{ __('messages.placeholder.not_linked') }}</span>
                                                            @if($claim && $claim->status === 'pending')
                                                                <span class="text-xs text-muted">
                                                                    {{ __('messages.placeholder.invite_sent_to', ['email' => $claim->target_email]) }}
                                                                    · {{ $claim->created_at->diffForHumans() }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($canEdit ?? false)
                                                            <form action="{{ route('placeholder.guardian-invite', [$team, $membership->user]) }}" method="POST"
                                                                class="flex items-end gap-2 mt-2">
                                                                @csrf
                                                                <div>
                                                                    <label class="form-label text-xs">{{ __('messages.placeholder.guardian_email') }}</label>
                                                                    <input type="email" name="guardian_email" required
                                                                        value="{{ $claim->target_email ?? '' }}"
                                                                        class="form-input text-sm" placeholder="rodic@email.cz">
                                                                </div>
                                                                <button type="submit" class="btn-secondary text-xs">
                                                                    {{ $claim ? __('messages.placeholder.resend_invite') : __('messages.placeholder.send_invite') }}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        </template>
                                        <template x-if="editing">
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_id') }}</label>
                                                    <input type="text" name="federation_id" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->federation_id }}" class="form-input text-sm">
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_status') }}</label>
                                                    <select name="federation_status" form="edit-member-{{ $membership->id }}" class="form-select text-sm">
                                                        <option value="">—</option>
                                                        <option value="amateur" @selected($membership->federation_status === 'amateur')>{{ __('messages.teams.fed_status_amateur') }}</option>
                                                        <option value="professional" @selected($membership->federation_status === 'professional')>{{ __('messages.teams.fed_status_professional') }}</option>
                                                        <option value="recreational" @selected($membership->federation_status === 'recreational')>{{ __('messages.teams.fed_status_recreational') }}</option>
                                                        <option value="youth" @selected($membership->federation_status === 'youth')>{{ __('messages.teams.fed_status_youth') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_registered_at') }}</label>
                                                    <input type="date" name="federation_registered_at" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->federation_registered_at?->format('Y-m-d') }}" class="form-input text-sm">
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_valid_until') }}</label>
                                                    <input type="date" name="federation_membership_valid_until" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->federation_membership_valid_until?->format('Y-m-d') }}" class="form-input text-sm">
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_link_type') }}</label>
                                                    <select name="federation_link_type" form="edit-member-{{ $membership->id }}" class="form-select text-sm">
                                                        <option value="">—</option>
                                                        <option value="facr" @selected($membership->federation_link_type === 'facr')>FAČR</option>
                                                        <option value="cfbu" @selected($membership->federation_link_type === 'cfbu')>ČFbU</option>
                                                        <option value="csp" @selected($membership->federation_link_type === 'csp')>ČSP</option>
                                                        <option value="cus" @selected($membership->federation_link_type === 'cus')>ČUS</option>
                                                        <option value="custom" @selected($membership->federation_link_type === 'custom')>{{ __('messages.common.other') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.federation_external_url') }}</label>
                                                    <input type="url" name="federation_external_url" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->federation_external_url }}" class="form-input text-sm" placeholder="https://...">
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.license_type') }}</label>
                                                    <input type="text" name="license_type" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->license_type }}" class="form-input text-sm">
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs">{{ __('messages.teams.license_valid_until') }}</label>
                                                    <input type="date" name="license_valid_until" form="edit-member-{{ $membership->id }}"
                                                        value="{{ $membership->license_valid_until?->format('Y-m-d') }}" class="form-input text-sm">
                                                </div>
                                                <div class="flex items-end">
                                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                                        <input type="hidden" name="attendance_required" form="edit-member-{{ $membership->id }}" value="0">
                                                        <input type="checkbox" name="attendance_required" form="edit-member-{{ $membership->id }}" value="1"
                                                            {{ $membership->attendance_required ? 'checked' : '' }}
                                                            class="form-checkbox">
                                                        {{ __('messages.teams.attendance_required_label') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                            @endforeach
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Add Member --}}
    @if(!$isParent || ($isDirectMember ?? false) || ($canEdit ?? false))
    <div class="card mb-6" x-data="{ open: false, mode: 'existing' }">
        <div class="card-header cursor-pointer flex items-center justify-between" @click="open = !open">
            <h2 class="font-medium">{{ __('messages.teams.add_member') }}</h2>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-muted transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div class="card-body" x-show="open" x-cloak>
            {{-- Tab switch --}}
            <div class="flex gap-1 mb-4">
                <button type="button" @click="mode = 'existing'"
                    :class="mode === 'existing' ? 'bg-primary text-white' : 'bg-bg text-muted hover:bg-border'"
                    class="px-4 py-2 rounded-lg text-sm transition-colors cursor-pointer">
                    {{ __('messages.teams.add_existing') }}
                </button>
                <button type="button" @click="mode = 'placeholder'"
                    :class="mode === 'placeholder' ? 'bg-primary text-white' : 'bg-bg text-muted hover:bg-border'"
                    class="px-4 py-2 rounded-lg text-sm transition-colors cursor-pointer">
                    {{ __('messages.placeholder.add_child') }}
                </button>
            </div>

            {{-- Existing user form --}}
            <div x-show="mode === 'existing'" x-cloak>
                <p class="text-sm text-muted mb-4">{{ __('messages.teams.add_member_desc') }}</p>

                <form action="{{ route('teams.add-member', $team) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="form-label">{{ __('messages.teams.email') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-input @error('email') border-danger @enderror" required>
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="form-label">{{ __('messages.teams.role') }}</label>
                        <select name="role" id="role" class="form-select @error('role') border-danger @enderror" required>
                            <option value="athlete" @selected(old('role') === 'athlete')>{{ __('messages.teams.athlete') }}</option>
                            <option value="assistant_coach" @selected(old('role') === 'assistant_coach')>{{ __('messages.teams.assistant_coach') }}</option>
                            <option value="head_coach" @selected(old('role') === 'head_coach')>{{ __('messages.teams.head_coach') }}</option>
                        </select>
                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">{{ __('messages.teams.add') }}</button>
                    </div>
                </form>
            </div>

            {{-- Placeholder (child) form --}}
            <div x-show="mode === 'placeholder'" x-cloak x-data="{ sendInvite: false }">
                <p class="text-sm text-muted mb-4">{{ __('messages.placeholder.add_child_desc') }}</p>

                <form action="{{ route('placeholder.store', $team) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __('messages.auth.first_name') }} *</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}"
                                class="form-input @error('first_name') border-danger @enderror" required>
                            @error('first_name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('messages.auth.last_name') }} *</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}"
                                class="form-input @error('last_name') border-danger @enderror" required>
                            @error('last_name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __('messages.placeholder.sex') }}</label>
                            <select name="sex" class="form-select">
                                <option value="">—</option>
                                <option value="male" @selected(old('sex') === 'male')>{{ __('messages.placeholder.sex_male') }}</option>
                                <option value="female" @selected(old('sex') === 'female')>{{ __('messages.placeholder.sex_female') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">{{ __('messages.profile.birth_date') }}</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('messages.teams.role') }} *</label>
                        <select name="role" class="form-select" required>
                            <option value="athlete" @selected(old('role', 'athlete') === 'athlete')>{{ __('messages.teams.athlete') }}</option>
                            <option value="assistant_coach" @selected(old('role') === 'assistant_coach')>{{ __('messages.teams.assistant_coach') }}</option>
                            <option value="head_coach" @selected(old('role') === 'head_coach')>{{ __('messages.teams.head_coach') }}</option>
                        </select>
                    </div>

                    {{-- Guardian invite toggle --}}
                    <div class="border-t border-border pt-4">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="hidden" name="is_guardian_invite" value="0">
                            <input type="checkbox" name="is_guardian_invite" value="1" class="form-checkbox"
                                x-model="sendInvite" {{ old('is_guardian_invite') ? 'checked' : '' }}>
                            {{ __('messages.placeholder.send_guardian_invite') }}
                        </label>
                        <p class="text-xs text-muted mt-1">{{ __('messages.placeholder.guardian_invite_desc') }}</p>
                    </div>

                    <div x-show="sendInvite" x-cloak>
                        <label class="form-label">{{ __('messages.placeholder.guardian_email') }} *</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                            class="form-input @error('guardian_email') border-danger @enderror"
                            :required="sendInvite">
                        @error('guardian_email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">{{ __('messages.placeholder.create_member') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Upcoming Events --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.teams.upcoming_events') }}</h2>
        </div>
        <div class="card-body">
            @if($upcomingEvents->isEmpty())
                <p class="text-muted">{{ __('messages.teams.no_upcoming_events') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($upcomingEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="flex items-start gap-4 py-2 border-b border-border last:border-0 hover:bg-bg rounded-lg transition-colors">
                            <div class="text-center shrink-0">
                                <div class="text-xs text-muted">{{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.') : $event->starts_at->format('M d') }}</div>
                                <div class="text-sm font-medium">{{ $event->starts_at->format('H:i') }}</div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $event->title }}</div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="badge badge-gray">{{ __('messages.events.' . $event->event_type) }}</span>
                                    @if($event->venue)
                                        <span class="text-sm text-muted">{{ $event->venue->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
