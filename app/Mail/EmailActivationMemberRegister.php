<?php

namespace App\Mail;

use App\Models\MemberOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmailActivationMemberRegister extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $data = [
            'name' => $this->data['name'],
            'type' => $this->data['type'],
            'code' => $this->data['code'],
            'expired' => $this->data['expired'],
        ];
        $subject = 'Linkyi Shop : Konfirmasi Kode OTP Untuk aktivasai akun';
        if ($data['type'] == MemberOtp::TYPE_FORGOTPASSWORD) {
            $subject = 'Linkyi Shop : Konfirmasi Kode OTP Untuk permintaan update password';
        }
        return $this->view('mail.otp_activation', $data)->subject($subject);
    }
}
