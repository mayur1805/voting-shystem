<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $table = "votes";
    protected $fillable = [
        'person_1',
        'person_2',
        'selected_person',
        'full_name',
        'email',
        'phone',
        'member_id',
        'is_verified'
    ];

    public function details()
    {
        return $this->hasMany(VotesDetails::class, 'vote_id');
    }
}
