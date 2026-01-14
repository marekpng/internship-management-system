<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Document;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'semester',
        'status',
        'year',
        'company_id',
        'student_id',
        'garant_id',
        'agreement_pdf_path',
    ];

    // voliteľné (odporúčané) - nech sa ti dátumy správne castujú
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'year' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function garant()
    {
        return $this->belongsTo(User::class, 'garant_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'internship_id');
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDocumentNames()
    {
        return $this->documents()->pluck('document_name')->toArray();
    }
}
