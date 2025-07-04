<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'answer',
        'correct',
    ];

    protected $casts = [
        'correct' => 'boolean',
    ];
}
