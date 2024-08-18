<?php
use App\Models\Birthday;
use Carbon\Carbon;

if (! function_exists('send_birthday')) {
  function send_birthday(Birthday $birthday) {
    $message = generate_message($birthday);
    try {
      trenalyze($birthday->whatsapp, $message, '');
    } catch (\Exception $e) {
      $birthday->update([
        'status' => 'active',
      ]);
    }
  }
}

if (! function_exists('generate_message')) {
  function generate_message(Birthday $birthday, string $type = 'ai') {
    try {
      $today = Carbon::now()->format('l, F j, Y');
      $prompt = "Create a birthday message for {$birthday->name}, who is celebrating today, {$today}. Mention that {$birthday->name} shares their birthday with famous persons celebrating today like. Highlight how much they are valued by the Computer Science Department and express well wishes for many more years of success and celebration. Include a cheerful tone and wish them a fantastic day!";
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        'Content-Type' => 'application/json',
      ])->post('https://api.openai.com/v1/completions', [
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'max_tokens' => 100,
        'temperature' => 0.7
      ]);
      $data = $response->json();
      if ($response->ok()) {
        return $data['choices'][0]['text'] ?? "🎉🎂 Happy Birthday, {$birthday->name}! 🎂🎉\n\nOn this special day, we want you to know how much the CSC department values and appreciates you. Your dedication and positive spirit truly make a difference, and we’re all so lucky to have you in our team. Here’s to many more years of success and celebration! Enjoy your day to the fullest!\n\nBest wishes from all of us at CSC! 🥳🎈";
      } else {
        return "🎉🎂 Happy Birthday, {$birthday->name}! 🎂🎉\n\nOn this special day, we want you to know how much the CSC department values and appreciates you. Your dedication and positive spirit truly make a difference, and we’re all so lucky to have you in our team. Here’s to many more years of success and celebration! Enjoy your day to the fullest!\n\nBest wishes from all of us at CSC! 🥳🎈";
      }
    } catch (\Exception $e) {
      return "🎉🎂 Happy Birthday, {$birthday->name}! 🎂🎉\n\nOn this special day, we want you to know how much the CSC department values and appreciates you. Your dedication and positive spirit truly make a difference, and we’re all so lucky to have you in our team. Here’s to many more years of success and celebration! Enjoy your day to the fullest!\n\nBest wishes from all of us at CSC! 🥳🎈";
    }
  } 
}

if (! function_exists('generate_string')) {
    function generate_string(int $length, string $type = 'alpha', string $case = 'lower', string $prefix = '', string $suffix = '') : string {
        $chars = '';
        if ($type == 'alpha') {
          $chars .= $case == 'lower' ? 'abcdefghijklmnopqrstuvwxyz' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } elseif ($type == 'mixed') {
          $chars .= $case == 'lower' ? 'abcdefghijklmnopqrstuvwxyz0123456789' : ($case == 'upper' ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
        } elseif ($type == 'numeric') {
          $chars .= '0123456789';
        }
        $str = '';
        for ($i = 0; $i < $length; $i++) {
          $str .= $chars[rand(0, strlen($chars) - 1)]; 
        }
        return $prefix . $str . $suffix;
    }
}

if (! function_exists('get_trenalyze_id')) {
  function get_trenalyze_id() : string {
      $ids = env('TRENALYZE_ACCOUNT_ID');
      $uid = 'xxxxxxx';
      if (!empty($ids)) {
          $ids = str_replace(' ', '', $ids);
          if (str_contains($ids, ',')) {
              $arr = explode(',', $ids);
              $uid = $arr[array_rand($arr)];
          } else {
              $uid = $ids;
          }
      }
      return $uid;
  }
}

if (! function_exists('trenalyze') ) {
  function trenalyze ($receiver, $msg, $type = 'text', $addfile = false, $document = []) : bool {
      $secret = env('TRENALYZE_SECRET');
      $url = "https://trenalyze.com/api/send/whatsapp";
      $curl = curl_init($url);
      $tries = 0;
      do {
          $account_id = get_trenalyze_id();
          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          $chat = [
            "secret" => $secret, 
            "account" => $account_id,
            "recipient" => $receiver,
            "type" => $type,
            "message" => $msg,
            "priority" => 1
          ];
          if ($addfile) {
            $chat['document_url'] = $document['document_url'];
            $chat['document_type'] = $document['document_type'];
            $chat['document_name'] = $document['document_name'];
          }
          curl_setopt($curl, CURLOPT_POSTFIELDS, $chat);
          //for debug only!
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          $resp = curl_exec($curl);
          curl_close($curl);
          $resp = json_decode($resp);
          if (isset($resp->status) && $resp->status === 200) {
            return true;
          } elseif (isset($resp->status) && $resp->status !== 200) {
            info(json_encode($resp));
          }
          $tries++;
      } while ($tries < 1);
      return false;
  }
}
