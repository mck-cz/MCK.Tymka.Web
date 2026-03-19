@extends(auth()->check() ? 'layouts.app' : 'layouts.auth')

@section('title', __('messages.placeholder.claim_title'))

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-xl font-semibold mb-6">{{ __('messages.placeholder.claim_title') }}</h1>

        <div class="card mb-6">
            <div class="card-body space-y-3">
                <p class="text-muted">{{ __('messages.placeholder.claim_desc') }}</p>

                <div class="bg-bg rounded-xl p-4 space-y-2">
                    <div class="flex gap-2">
                        <span class="font-medium" style="min-width: 100px;">{{ __('messages.placeholder.child_name') }}:</span>
                        <span>{{ $placeholder->full_name }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-medium" style="min-width: 100px;">{{ __('messages.placeholder.club') }}:</span>
                        <span>{{ $claim->club->name }}</span>
                    </div>
                    @if($claim->team)
                        <div class="flex gap-2">
                            <span class="font-medium" style="min-width: 100px;">{{ __('messages.placeholder.team') }}:</span>
                            <span>{{ $claim->team->name }}</span>
                        </div>
                    @endif
                </div>

                @auth
                    <form action="{{ route('placeholder.process-claim', $claim->token) }}" method="POST" class="pt-4">
                        @csrf
                        <p class="text-sm mb-4">
                            {{ __('messages.placeholder.claim_confirm', ['name' => $placeholder->full_name]) }}
                        </p>
                        <div class="flex gap-3">
                            <button type="submit" class="btn-primary">{{ __('messages.placeholder.accept_guardian') }}</button>
                            <a href="{{ route('dashboard') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                        </div>
                    </form>
                @else
                    <div class="pt-4">
                        <p class="text-sm text-muted mb-4">{{ __('messages.placeholder.login_first') }}</p>
                        <div class="flex gap-3">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary">{{ __('messages.nav.login') }}</a>
                            <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="btn-secondary">{{ __('messages.nav.register') }}</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection
