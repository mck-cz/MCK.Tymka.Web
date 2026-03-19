<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\MemberPayment;
use App\Models\PaymentRequest;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentRequestController extends Controller
{
    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();
        abort_unless($membership && in_array($membership->role, ['owner', 'admin']), 403);
    }

    public function index()
    {
        $clubId = session('current_club_id');
        $isAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if ($isAdmin) {
            // Admin sees all payment requests
            $paymentRequests = PaymentRequest::where('club_id', $clubId)
                ->with(['team', 'createdBy', 'memberPayments'])
                ->latest('created_at')
                ->get();
        } else {
            // Member sees only payment requests where they have a member payment
            $paymentRequests = PaymentRequest::where('club_id', $clubId)
                ->whereHas('memberPayments', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->with(['team', 'createdBy', 'memberPayments' => function ($q) {
                    $q->where('user_id', auth()->id());
                }])
                ->latest('created_at')
                ->get();
        }

        // My payments (for member view)
        $myPayments = MemberPayment::where('user_id', auth()->id())
            ->whereHas('paymentRequest', function ($q) use ($clubId) {
                $q->where('club_id', $clubId);
            })
            ->with('paymentRequest')
            ->latest('created_at')
            ->get();

        return view('payments.index', compact('paymentRequests', 'myPayments', 'isAdmin'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('payments.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|string|in:membership_fee,event_fee,equipment,other',
            'due_date' => 'required|date|after_or_equal:today',
            'team_id' => 'nullable|exists:teams,id',
            'bank_account' => 'nullable|string|max:50',
            'variable_symbol_prefix' => 'nullable|string|max:6',
        ]);

        // Verify team belongs to club
        if (!empty($validated['team_id'])) {
            $team = Team::findOrFail($validated['team_id']);
            abort_unless($team->club_id === $clubId, 403);
        }

        $paymentRequest = PaymentRequest::create([
            'club_id' => $clubId,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'CZK',
            'payment_type' => $validated['payment_type'],
            'due_date' => $validated['due_date'],
            'team_id' => $validated['team_id'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'variable_symbol_prefix' => $validated['variable_symbol_prefix'] ?? null,
            'status' => 'active',
        ]);

        // Generate member payments
        $this->generateMemberPayments($paymentRequest);

        // Notify all members who received a payment
        $paymentUserIds = MemberPayment::where('payment_request_id', $paymentRequest->id)
            ->where('user_id', '!=', auth()->id())
            ->pluck('user_id')
            ->toArray();

        if (!empty($paymentUserIds)) {
            NotificationService::send(
                $paymentUserIds,
                'new_payment',
                __('messages.notifications_msg.new_payment', [
                    'title' => $paymentRequest->name,
                    'amount' => number_format($paymentRequest->amount, 0, ',', ' ') . ' ' . $paymentRequest->currency,
                ])
            );
        }

        return redirect()->route('payments.show', $paymentRequest)
            ->with('success', __('messages.payments.created'));
    }

    public function show(PaymentRequest $paymentRequest)
    {
        $clubId = session('current_club_id');
        abort_unless($paymentRequest->club_id === $clubId, 403);

        $isAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        $paymentRequest->load(['team', 'createdBy', 'memberPayments.user']);

        // For non-admin, only show their own payment
        if (!$isAdmin) {
            $myPayment = $paymentRequest->memberPayments->firstWhere('user_id', auth()->id());
            abort_unless($myPayment, 403);
        }

        return view('payments.show', compact('paymentRequest', 'isAdmin'));
    }

    public function edit(PaymentRequest $paymentRequest)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($paymentRequest->club_id === $clubId, 403);

        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('payments.edit', compact('paymentRequest', 'teams'));
    }

    public function update(Request $request, PaymentRequest $paymentRequest)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($paymentRequest->club_id === $clubId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'bank_account' => 'nullable|string|max:50',
        ]);

        $paymentRequest->update($validated);

        return redirect()->route('payments.show', $paymentRequest)
            ->with('success', __('messages.payments.updated'));
    }

    public function cancelRequest(PaymentRequest $paymentRequest)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($paymentRequest->club_id === $clubId, 403);

        $paymentRequest->update(['status' => 'cancelled']);

        // Cancel all pending member payments
        $paymentRequest->memberPayments()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        return redirect()->route('payments.index')
            ->with('success', __('messages.payments.request_cancelled'));
    }

    public function confirmPayment(Request $request, MemberPayment $memberPayment)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($memberPayment->paymentRequest->club_id === $clubId, 403);

        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:1', 'max:' . $memberPayment->remaining],
            'send_thanks' => ['sometimes', 'boolean'],
        ]);

        $paidAmount = (float) $validated['paid_amount'];
        $newTotal = (float) $memberPayment->paid_amount + $paidAmount;
        $isFullyPaid = $newTotal >= (float) $memberPayment->amount;

        $memberPayment->update([
            'paid_amount' => $newTotal,
            'status' => $isFullyPaid ? 'paid' : 'partial',
            'paid_at' => $isFullyPaid ? now() : $memberPayment->paid_at,
            'confirmed_by' => auth()->id(),
            'thanked_at' => $request->boolean('send_thanks', true) ? now() : $memberPayment->thanked_at,
        ]);

        // Notify the member whose payment was confirmed
        NotificationService::send(
            $memberPayment->user_id,
            'payment_confirmed',
            __('messages.notifications_msg.payment_confirmed', [
                'title' => $memberPayment->paymentRequest->name,
            ])
        );

        $message = $isFullyPaid
            ? __('messages.payments.payment_confirmed')
            : __('messages.payments.partial_payment_confirmed', ['amount' => number_format($paidAmount, 0, ',', ' ')]);

        return back()->with('success', $message);
    }

    public function cancelPayment(Request $request, MemberPayment $memberPayment)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($memberPayment->paymentRequest->club_id === $clubId, 403);

        $memberPayment->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', __('messages.payments.payment_cancelled'));
    }

    private function generateMemberPayments(PaymentRequest $paymentRequest): void
    {
        $prefix = $paymentRequest->variable_symbol_prefix ?: str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);

        if ($paymentRequest->team_id) {
            // Team-specific: generate for all team members
            $memberships = TeamMembership::where('team_id', $paymentRequest->team_id)
                ->where('status', 'active')
                ->with('user')
                ->get();

            $counter = 1;
            foreach ($memberships as $membership) {
                $vs = $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
                $qrPayload = $this->generateQrPayload(
                    $paymentRequest->bank_account,
                    $paymentRequest->amount,
                    $vs,
                    $paymentRequest->currency
                );

                MemberPayment::create([
                    'payment_request_id' => $paymentRequest->id,
                    'user_id' => $membership->user_id,
                    'variable_symbol' => $vs,
                    'amount' => $paymentRequest->amount,
                    'status' => 'pending',
                    'qr_payload' => $qrPayload,
                ]);
                $counter++;
            }
        } else {
            // Club-wide: generate for all club members
            $memberships = ClubMembership::where('club_id', $paymentRequest->club_id)
                ->where('status', 'active')
                ->with('user')
                ->get();

            $counter = 1;
            foreach ($memberships as $membership) {
                $vs = $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
                $qrPayload = $this->generateQrPayload(
                    $paymentRequest->bank_account,
                    $paymentRequest->amount,
                    $vs,
                    $paymentRequest->currency
                );

                MemberPayment::create([
                    'payment_request_id' => $paymentRequest->id,
                    'user_id' => $membership->user_id,
                    'variable_symbol' => $vs,
                    'amount' => $paymentRequest->amount,
                    'status' => 'pending',
                    'qr_payload' => $qrPayload,
                ]);
                $counter++;
            }
        }
    }

    private function generateQrPayload(?string $bankAccount, float $amount, string $vs, string $currency): ?string
    {
        if (!$bankAccount) {
            return null;
        }

        // SPD (Short Payment Descriptor) format for Czech QR payments
        return 'SPD*1.0*ACC:' . $bankAccount . '*AM:' . number_format($amount, 2, '.', '') . '*CC:' . $currency . '*X-VS:' . $vs;
    }
}
