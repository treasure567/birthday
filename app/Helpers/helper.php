<?php
use App\Models\Birthday;
use Carbon\Carbon;

if (! function_exists('send_birthday')) {
  function send_birthday(Birthday $birthday) {
    $server = "https://birthday.treasureuvietobore.com";
    $target = env('OAU_CSC');
    $message = generate_message($birthday);
    $message .= "\n\n@$birthday->whatsapp";
    try {
      if ($birthday && !empty($birthday->picture) && Storage::exists($birthday->picture)) {
        $filePath = $birthday->picture;
        $fileInfo = pathinfo($filePath);
        $extension = $fileInfo['extension'] ?? '';
        $data = get_file_type($extension);
        if ($data !== false) {
          $addMedia = $data['addMedia'] ?? false;
          $dataType = $data['type'] ?? 'text';
          $msgType = $data['msgType'] ?? 'text';
          $filePath = $server . '/storage/app/' . $birthday->picture;
          $media = [];
          $media['media_url'] = $filePath;
          $media['media_type'] = $dataType;
          $process = trenalyze($target, $message, $msgType, false, [], $addMedia, $media);
          trenalyze($birthday->whatsapp, $message, $msgType, false, [], $addMedia, $media);
          if ($process == true) {
            $birthday->update([
              'last_sent_at' => Carbon::now(),
              'status' => 'active'
            ]);
          } else {
            $birthday->update([
              'status' => 'active',
            ]);
          }
        }
      } else {
        $process = trenalyze($target, $message, 'text');
        trenalyze($birthday->whatsapp, $message, 'text');
        if ($process == true) {
          $birthday->update([
            'last_sent_at' => Carbon::now(),
            'status' => 'active'
          ]);
        } else {
          $birthday->update([
            'status' => 'active',
          ]);
        }
      }
      return;
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
      $prompt = "Create a birthday message for {$birthday->name}, who is celebrating today, {$today}. {$birthday->name} is a {$birthday->gender}. Mention that {$birthday->name} shares their birthday with famous persons celebrating today also mention the names of the famous persons also. Also, mention some notable computer or technological history that is remarkable to their birthday. Highlight how much they are valued by the Computer Science Department and express well wishes for many more years of success and celebration. Include a cheerful tone and wish them a fantastic day!. it's not a letter, so dont add sender name at the bottom";
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        'Content-Type' => 'application/json',
      ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-4o',
        'messages' => [
          [
            'role' => 'system',
            'content' => $prompt
          ]
        ]
      ]);
      $data = $response->json();
      info($data);
      if ($response->ok()) {
        return $data['choices'][0]['message']['content'] ?? "🎉🎂 Happy Birthday, {$birthday->name}! 🎂🎉\n\nOn this special day, we want you to know how much the CSC department values and appreciates you. Your dedication and positive spirit truly make a difference, and we’re all so lucky to have you in our team. Here’s to many more years of success and celebration! Enjoy your day to the fullest!\n\nBest wishes from all of us at CSC! 🥳🎈";
      } else {
        return "🎉🎂 Happy Birthday, {$birthday->name}! 🎂🎉\n\nOn this special day, we want you to know how much the CSC department values and appreciates you. Your dedication and positive spirit truly make a difference, and we’re all so lucky to have you in our team. Here’s to many more years of success and celebration! Enjoy your day to the fullest!\n\nBest wishes from all of us at CSC! 🥳🎈";
      }
    } catch (\Exception $e) {
      info($e->getMessage());
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
  function trenalyze ($receiver, $msg, $type = 'text', $addfile = false, $document = [], $addMedia = false, $media = []) {
      $secret = env('TRENALYZE_SECRET');
      $url = "https://trenalyze.com/api/send/whatsapp";
      $curl = curl_init($url);
      $tries = 0;
      $account_id = get_trenalyze_id();
      do {
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
            $chat['document_url'] = $document['document_url'] ?? '';
            $chat['document_type'] = $document['document_type'] ?? '';
            $chat['document_name'] = $document['document_name'] ?? '';
        }
        if ($addMedia) {
            $chat['media_url'] = $media['media_url'] ?? '';
            $chat['media_type'] = $media['media_type'] ?? '';
            $chat['media_file'] = $media['media_file'] ?? '';
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $chat);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp);
        if (isset($resp->status) && $resp->status === 200) {
            return true;
        }
        info(json_encode($resp));
        $tries++;
      } while ($tries < 1);
      return false;
  }
}

if (! function_exists('get_file_type')) {
  function get_file_type($extension) {
      $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
      $audioExtensions = ['mp3', 'ogg'];
      $videoExtensions = ['mp4'];
      $documentExtensions = ['pdf', 'xls', 'xlsx', 'doc', 'docs'];
      $isFile = false;
      $isMedia = false;
      $type = 'text';
      $messageType = 'text';
      if (in_array($extension, $imageExtensions)) {
          $isMedia = true;
          $type = 'image';
          $messageType = 'media';
      } 
      // elseif (in_array($extension, $audioExtensions)) {
      //     $isMedia = true;
      //     $type = 'audio';
      //     $messageType = 'media';
      // } elseif (in_array($extension, $videoExtensions)) {
      //     $isMedia = true;
      //     $type = 'video';
      //     $messageType = 'media';
      // } elseif (in_array($extension, $documentExtensions)) {
      //     $isFile = true;
      //     $type = $extension;
      //     $messageType = 'document';
      // } 
      else {
        return false;
      }
      return [
          'addMedia' => $isMedia,
          'type' => $type,
          'msgType' => $messageType
      ];

  }
}
