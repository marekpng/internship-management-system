<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // Primárny kľúč je document_id (nie default id)
    protected $primaryKey = 'document_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'document_name',
        'file_path',
        'type',
        'internship_id',
        'uploaded_by',
        'company_status'
    ];

    /**
     * Dokument patrí ku praxi (internship)
     */
    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }

    /**
     * Kto dokument nahral (študent/firma)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
