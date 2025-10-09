<?php

namespace app\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'must_change_password',

        // Študent
        'first_name',
        'last_name',
        'address',
        'student_email',
        'alternative_email',
        'phone',
        'study_field',

        // Firma
        'company_name',
        'company_address',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Kontrola roly
    public function hasRole(string $roleName): bool
    {
        return $this->role && strtolower($this->role) === strtolower($roleName);
    }

    // Generovanie náhodného hesla
    public static function generateRandomPassword(int $length = 12): string
    {
        return Str::random($length);
    }

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
}
