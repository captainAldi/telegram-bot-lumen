<?php

namespace App\Http\Controllers;

use Telegram\Bot\Api;

class TelegramController extends Controller
{
    
    public function teleWebHook()
    {

        // Inisialisasi Bot
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        // Get Webhook Update
        $updates = $telegram->getWebhookUpdates();


        // --- Check Webhook Types ---

        // Chat Biasa
        if (isset($updates['message'])) {
            
            // Get Isi Pesan
            $text_pesan = $updates['message']['text'];
            $array_tp = explode(" ", $text_pesan);
            
            // Kasih Kondisi berdasarkan Pesan
            switch (strtolower($array_tp[0])) {
                case '/start':
                        $telegram->sendMessage([
                            'chat_id' => $updates['message']['from']['id'], 
                            'text' => 'Selamat Datang !'
                        ]);
                    break;
                
                case 'approve':
                        $checkTokenApprove = array_key_exists(1, $array_tp) == true ? $array_tp[1] : '';

                        $inlineKB = json_encode([
                            'inline_keyboard' => [
                                // Baris 1
                                [
                                    // Kolom 1
                                    [
                                        'text' => 'Ya',
                                        'callback_data' => 'approved bill ' . $checkTokenApprove
                                    ],
                                    // Kolom 2
                                    [
                                        'text' => 'Tidak',
                                        'callback_data' => 'declined bill ' . $checkTokenApprove
                                    ]

                                ],
                            ]
                        ]);

                        $telegram->sendMessage([
                            'chat_id' => $updates['message']['from']['id'], 
                            'text' => 'Anda yakin menyetujui ' . $checkTokenApprove . ' ?',
                            'reply_markup' => $inlineKB

                        ]);
                    break;
                
                default:
                        $telegram->sendMessage([
                            'chat_id' => $updates['message']['from']['id'], 
                            'text' => 'Perintah Tidak Ada !'
                        ]);
                    break;
            }

        } elseif (isset($updates['callback_query'])) {

            $query = $updates['callback_query'];
            $query_id = $updates['callback_query']['id'];
            $query_data  = $updates['callback_query']['data'];
            $chat_id = $updates['callback_query']['message']['chat']['id'];

            // // Get Isi Callback Data
            $text_callback_data = $query_data;
            $array_tcd = explode(" ", $text_callback_data);

            // // Cek Approved or Declined
            switch ($array_tcd[0]) {
                case 'approved':
                    
                    // Cek Tipe
                    switch ($array_tcd[1]) {
                        case 'bill':
                                $telegram->answerCallbackQuery([
                                    'callback_query_id' => $query_id,
                                    'cache_time' => 2,
                                    'text'  => 'Berhasil Menyetujui Tagihan - ' . $array_tcd[2]
                                ]);

                                $telegram->sendMessage([
                                    'chat_id' => $chat_id, 
                                    'text' => 'Berhasil Menyetujui Tagihan - ' . $array_tcd[2]
                                ]);
                            break;
                        
                        default:
                                $telegram->answerCallbackQuery([
                                    'callback_query_id' => $query_id,
                                    'cache_time' => 2,
                                    'text'  => 'Tipe Tidak Ada !'
                                ]);
                            break;
                    }

                    break;

                case 'declined':
                    
                    // Cek Tipe
                    switch ($array_tcd[1]) {
                        case 'bill':
                                $telegram->answerCallbackQuery([
                                    'callback_query_id' => $query_id,
                                    'cache_time' => 2,
                                    'text'  => 'Anda Tidak Menyetujui Tagihan - ' . $array_tcd[2]
                                ]);

                                $telegram->sendMessage([
                                    'chat_id' => $chat_id, 
                                    'text' => 'Anda Tidak Menyetujui Tagihan - ' . $array_tcd[2]
                                ]);
                            break;
                        
                        default:
                                $telegram->answerCallbackQuery([
                                    'callback_query_id' => $query_id,
                                    'cache_time' => 2,
                                    'text'  => 'Tipe Tidak Ada !'
                                ]);
                            break;
                    }

                    break;
                
                default:
                        $telegram->answerCallbackQuery([
                            'callback_query_id' => $query_id,
                            'cache_time' => 2,
                            'text'  => 'Ada Gangguan !'
                        ]);
                    break;
            }

        } else {
            $telegram->sendMessage([
                'chat_id' => env('CHAT_ID_SAMPLE'), 
                'text' => 'Bukan Message dan Callback ! ' . $updates
            ]);
        }

        return response('ok', 200);

    }

}
