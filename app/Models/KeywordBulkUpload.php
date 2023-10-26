<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordBulkUpload extends Model
{
    const CSV_UPLOAD = 'csv';

    /**
     * @var string
     */
    protected $table = 'keyword_bulk_upload';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'filename',
        'filepath',
        'user_id',
        'failed_count',
        'success_count',
        'total_count',
    ];
}
