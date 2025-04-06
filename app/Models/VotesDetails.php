<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotesDetails extends Model
{
    use HasFactory;

    protected $table = "votes_details";
    protected $fillable = ['vote_id', 'person_1', 'person_2', 'selected_person'];

    public function vote()
    {
        return $this->belongsTo(Vote::class, 'vote_id');
    }
}
