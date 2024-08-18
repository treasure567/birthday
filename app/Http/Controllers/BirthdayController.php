<?php

namespace App\Http\Controllers;

use App\Models\Birthday;
use App\Models\WhatsAppOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BirthdayController extends Controller
{

    public static function sendBirthday() {
        $today = Carbon::now()->format('m-d');
        $birthdays = Birthday::where('status', 'active')
            ->whereRaw("CONCAT(month, '-', day) = ?", [$today])
            ->where(function ($query) {
                $todayStart = Carbon::now()->startOfDay();
                $query->whereNull('last_sent_at')
                    ->orWhere('last_sent_at', '<', $todayStart);
            })
            ->get();
        
        foreach ($birthdays as $birthday) {
            $todayStart = Carbon::now()->startOfDay();
            $todayEnd = Carbon::now()->endOfDay();
            if ($birthday->last_sent_at && $birthday->last_sent_at >= $todayStart && $birthday->last_sent_at <= $todayEnd) {
                continue;
            }
            if ($birthday->status === 'sending') {
                continue;
            }
            $birthday->update([
                'status' => 'sending',
            ]);
            try {
                
            } catch (\Exception $e) {
                $birthday->update([
                    'status' => 'active',
                ]);
            }
        }
    }

    public function index() {
        return view('form');
    }
    
    public function save(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'picture' => ['required', 'mimes:png,jpg'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'whatsapp' => ['required', 'numeric'],
            'otp' => ['required', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
            'gender' => ['required', 'in:male,female']
        ]);
        if ($validator->fails()) {
                return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }
        if (strlen($request->whatsapp) !== 13) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'WhatsApp Number must be 13 Digit. E.g 2348157002782'
            ]);
        }
        if (!str_starts_with($request->whatsapp, '234')) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'Invalid WhatsApp Number, use the sample provided'
            ]);
        }
        $whatsapp = $request->whatsapp;
        $otp = WhatsAppOtp::where('whatsapp', $whatsapp)->first();
        if (!$otp) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'Please request for a WhatsApp OTP First'
            ]);
        }
        if (!$otp->verifyOtp($request->otp)) {
            // return response()->json([
            //     'status' => false,
            //     'class' => 'error',
            //     'message' => 'Invalid OTP Code Supplied'
            // ]);
        }
        $bd = Birthday::where('whatsapp', $request->whatsapp)->first();
        $payload = $request->only((new Birthday())->getFillable());
        if ($request->has('picture')) {
            if ($bd && !empty($bd->picture) && Storage::exists($bd->picture)) {
                Storage::delete($bd->picture);
            }
            $payload['picture'] = $request->file('picture')->store('public/pictures');
        }
        Birthday::updateOrCreate(
            ['whatsapp' => $request->whatsapp],
            $payload 
        );
        return response()->json([
            'status' => false,
            'class' => 'success',
            'message' => 'Birthday added/modified successfully'
        ]);
    }

    public function sendOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'whatsapp' => ['required']
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }
        if (strlen($request->whatsapp) !== 13) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'WhatsApp Number must be 13 Digit. E.g 2348157002782'
            ]);
        }
        if (!str_starts_with($request->whatsapp, '234')) {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'Invalid WhatsApp Number, use the sample provided'
            ]);
        }
        $item = WhatsAppOtp::where('whatsapp', $request->whatsapp)->first();
        if (!$item) {
            $item = WhatsAppOtp::create([
                'whatsapp' => $request->whatsapp
            ]);
        }
        $msg = "Your CSC Verification OTP is: {{otp}}";
        if ($item->sendWhatsAppOtp($msg)) {
            return response()->json([
                'status' => true,
                'class' => 'success',
                'message' => 'Please check your WhatsApp for an OTP Code'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'class' => 'error',
                'message' => 'OTP code could not be sent. Ensure you are using a valid WhatsApp Number'
            ]);
        }
    }
}
