<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'company_account_activity', 'ico', 'street', 'city', 'zip'];

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    public function isActive()
    {
        return $this->company_account_activity == 1;
    }
}
