<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Enums\BloodGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile-image',
        'firstName',
        'lastName',
        'email',
        'password',
        'phone',
        'status',
        'is_admin',
        'tenant_id',
        'shift_id',
        'name',
        'emp_code',
        'date_of_birth',
        'attendance_method',
        'gender',
        'nationality',
        'joining_date',
        'blood_group',
        'about',
        'address',
        'country',
        'state',
        'city',
        'zipcode',
        'emergency_contact_number_1',
        'emergency_contact_relation_1',
        'emergency_contact_name_1',
        'emergency_contact_number_2',
        'emergency_contact_relation_2',
        'emergency_contact_name_2',
        'bank_name',
        'account_number',
    ];

    // Ensure Spatie permissions use the tenant web guard
    protected $guard_name = 'web';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $casts = [
        'blood_group' => BloodGroup::class
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function fullName() {
        return "{$this->firstName} {$this->lastName}";
    }
    // Convenience accessors to avoid dealing with hyphenated column names in views/components
    public function getProfileImagePathAttribute(): ?string
    {
        return $this->getAttribute('profile-image');
    }
    public function getProfileImageUrlAttribute(): ?string
    {
        return tenant_storage_url($this->getProfileImagePathAttribute());
    }
}
