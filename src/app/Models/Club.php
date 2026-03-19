<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'primary_sport',
        'address',
        'logo_url',
        'color',
        'bank_account',
        'settings',
        'billing_plan',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function clubMemberships(): HasMany
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function consentTypes(): HasMany
    {
        return $this->hasMany(ConsentType::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function venueCosts(): HasMany
    {
        return $this->hasMany(VenueCost::class);
    }

    public function penaltyRules(): HasMany
    {
        return $this->hasMany(PenaltyRule::class);
    }

    public function equipmentTemplates(): HasMany
    {
        return $this->hasMany(EquipmentTemplate::class);
    }

    public function instructionTemplates(): HasMany
    {
        return $this->hasMany(InstructionTemplate::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
