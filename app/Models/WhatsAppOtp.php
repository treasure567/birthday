<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class WhatsAppOtp extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_otps';

    protected $fillable = [
        'otp',
        'whatsapp'
    ];

    protected $casts = [
        'otp' => 'hashed'
    ];

    public function verifyOtp(int $otp) : bool {
        if (!Hash::check($otp, $this->otp)) {
            return false;
        }
        $this->resetOtp();
        return true;
    }

    public function resetOtp(int $otp = null) : bool {
        $this->otp = ($otp == null) ? generate_string(6, 'numeric') : $otp;
        return $this->save();
    }

    public function sendWhatsAppOtp($msg, $whatsapp = null) : bool {
        $whatsapp = $whatsapp ?? $this->whatsapp;
        $otp = generate_string(6, 'numeric');
        $msg = str_replace('{{otp}}', $otp, $msg);
        if (trenalyze($whatsapp, $msg)) {
            $this->resetOtp($otp);
            return true;
        }
        return false;
    }
}
