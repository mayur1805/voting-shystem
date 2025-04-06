<?php
namespace App\AdminModule\Http\Controllers;

use App\Models\Vote;
use App\Models\VotesDetails;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $votes = VotesDetails::selectRaw("'person_1' AS candidate, COUNT(*) AS total_votes")
            ->where('person_1', 'yes')
            ->groupBy('person_1')

            ->unionAll(
                VotesDetails::selectRaw("'person_2' AS candidate, COUNT(*) AS total_votes")
                    ->where('person_2', 'yes')
                    ->groupBy('person_2')
            )

            ->unionAll(
                VotesDetails::selectRaw("selected_person AS candidate, COUNT(*) AS total_votes")
                    ->whereIn('selected_person', ['person_3', 'person_4'])
                    ->groupBy('selected_person')
            );

            return DataTables::of($votes)->make(true);
        }

        return view('admin.election_data');
    }

    // Generate vote report
    public function report(Request $request)
    {
         if ($request->ajax()) {
            $votes = VotesDetails::selectRaw("
                'Person 1' AS candidate,
                SUM(CASE WHEN person_1 = 'yes' THEN 1 ELSE 0 END) AS yes_votes,
                SUM(CASE WHEN person_1 = 'no' THEN 1 ELSE 0 END) AS no_votes,
                COUNT(person_1) AS total_votes
            ")
            ->unionAll(
                VotesDetails::selectRaw("
                    'Person 2' AS candidate,
                    SUM(CASE WHEN person_2 = 'yes' THEN 1 ELSE 0 END) AS yes_votes,
                    SUM(CASE WHEN person_2 = 'no' THEN 1 ELSE 0 END) AS no_votes,
                    COUNT(person_2) AS total_votes
                ")
            )
            ->unionAll(
                VotesDetails::selectRaw("
                    'Person 3' AS candidate,
                    COUNT(CASE WHEN selected_person = 'person_3' THEN 1 ELSE NULL END) AS yes_votes,
                    COUNT(CASE WHEN selected_person IS NOT NULL AND selected_person != 'person_3' THEN 1 ELSE NULL END) AS no_votes,
                    COUNT(CASE WHEN selected_person = 'person_3' THEN 1 ELSE NULL END) AS total_votes
                ")
            )
            ->unionAll(
                VotesDetails::selectRaw("
                    'Person 4' AS candidate,
                    COUNT(CASE WHEN selected_person = 'person_4' THEN 1 ELSE NULL END) AS yes_votes,
                    COUNT(CASE WHEN selected_person IS NOT NULL AND selected_person != 'person_4' THEN 1 ELSE NULL END) AS no_votes,
                    COUNT(CASE WHEN selected_person = 'person_4' THEN 1 ELSE NULL END) AS total_votes
                ")
            );


            return DataTables::of($votes)
                ->order(function ($query) {
                    $request = request();
                    $orderColumnIndex = $request->input('order.0.column');
                    $orderDirection = $request->input('order.0.dir', 'asc');
                    $columns = [
                        0 => 'candidate',
                        1 => 'yes_votes',
                        2 => 'no_votes',
                        3 => 'total_votes'
                    ];
                    if (isset($columns[$orderColumnIndex])) {
                        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
                    }
                })->toJson();
        }

    return view('admin.election_report');
    }
}
