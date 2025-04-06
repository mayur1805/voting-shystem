<?php

namespace App\Jobs;

use App\Models\Vote;
use App\Models\VoteDetail;
use App\Models\VotesDetails;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreVotesFromCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKeys = Cache::store('persistent')->get('voteStore:index', []);
        foreach ($cacheKeys as $key => $email) {
            $votes = Cache::store('persistent')->get("voteStore:{$email}",[]);
            foreach ($votes as $voteData) {
                $vote = Vote::updateOrCreate(
                    ['email' => $email],
                    [
                        'full_name' => $voteData['full_name'],
                        'phone' => $voteData['phone'],
                        'member_id' => $voteData['member_id'],
                        'is_verified' => true,
                    ]
                );
                VotesDetails::create(
                    [
                        'vote_id' => $vote->id,
                        'person_1' => $voteData['person_1'],
                        'person_2' => $voteData['person_2'],
                        'selected_person' => $voteData['selected_person'],
                    ]
                );
            }
            Cache::store('persistent')->forget("voteStore:{$email}");
        }
        Cache::store('persistent')->forget('voteStore:index');
    }
}
