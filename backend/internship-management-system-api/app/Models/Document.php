<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['document_name', 'internship_id'];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function getInternshipId()
    {
        return $this->internship_id;
    }
}
