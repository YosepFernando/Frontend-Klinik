<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_id',
        'user_id',
        'attended',
        'score',
        'feedback',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'score' => 'integer',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
