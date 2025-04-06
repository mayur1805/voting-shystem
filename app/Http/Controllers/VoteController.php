<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class VoteController extends Controller
{
    public function showForm()
    {
        try {
            return view('vote');
        } catch (\Exception $e) {
            Log::error('Error loading vote form: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong while loading the form.'], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $otp = Str::random(6);
            dispatch(new \App\Jobs\SendOtpJob($request->email, $otp));

            return response()->json(['status' => true,'message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            Log::error('Error sending OTP: ' . $e->getMessage());
            return response()->json(['status' => false,'message' => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
            ]);
            if (Cache::store('persistent')->get("otp:{$request->email}") !== $request->otp) {
                return response()->json(['status' => false,'message' => 'Invalid OTP'], 400);
            }
            Cache::store('persistent')->put("verified:{$request->email}", true);
            return response()->json(['status' => true,'message' => 'OTP verified successfully']);
        } catch (QueryException $e) {
            Log::error('Database error while verifying OTP: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeVote(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'person_1' => 'required',
                'person_2' => 'required',
                'selected_person' => 'required',
                'full_name' => 'required',
                'phone' => 'required',
                'member_id' => 'required',
            ]);
            if (!Cache::store('persistent')->get("otp:{$request->email}") && Cache::store('persistent')->get("otp:{$request->email}") == null) {
                return response()->json(['status' => false,'message' => 'OTP not verified'], 400);
            }
            $cacheKey = "voteStore:{$request->email}";
            $allEmails = Cache::store('persistent')->get('voteStore:index', []);
            if (!in_array($request->email, $allEmails)) {
                $allEmails[] = $request->email;
                Cache::store('persistent')->put('voteStore:index', $allEmails, now()->addHours(24));
            }
            $existingVotes = Cache::store('persistent')->get($cacheKey, []);
            $newVote = [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'member_id' => $request->member_id,
                'is_verified' => true,
                'person_1' => $request->person_1,
                'person_2' => $request->person_2,
                'selected_person' => $request->selected_person,
            ];
            $existingVotes[] = $newVote;

            Cache::store('persistent')->put($cacheKey, $existingVotes, now()->addHours(24));
            Cache::store('persistent')->forget("otp:{$request->email}");
            Cache::store('persistent')->forget("verified:{$request->email}");
            return response()->json(['status' => true,'message' => 'Vote submitted successfully!']);
        } catch (QueryException $e) {
            Log::error('Database error while storing vote: ' . $e->getMessage());
            return response()->json(['status' => false,'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Error storing vote: ' . $e->getMessage());
            return response()->json(['status' => false,'error' =>  $e->getMessage()], 500);
        }
    }

    public function checkExistsUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $user = Vote::where('email', $request->email)->first();

        if (!$user) {
            $userData = collect(Cache::store('persistent')->get("voteStore:{$request->email}", []))->first();
            $user = $userData ? new Vote($userData) : null;

        }
        if ($user) {
            return response()->json([
                'status' => true,
                'member_id' => $user->member_id
            ]);
        }

        return response()->json(['status' => false]);
    }
}
