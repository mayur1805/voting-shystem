<?php
namespace App\Jobs;

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Cache;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email, $otp;

    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    public function handle()
    {
        try {
            Cache::forget("otp:{$this->email}");
            Mail::to($this->email)->send(new OtpMail($this->otp));
            Cache::store('persistent')->put("otp:{$this->email}", $this->otp, now()->addSeconds(60));
        } catch (\Exception $e) {
            \Log::error("OTP Email sending failed: " . $e->getMessage());
        }
    }
}

