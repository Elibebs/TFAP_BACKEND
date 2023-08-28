<?php 

namespace App\TemaFirst\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;

class Twilio{

	public static function sendSMS($phoneNumber, $message){
		


			
			try {
				
				$account_sid = env('TWILIO_ACCOUNT_SID');
				$auth_token = env('TWILIO_AUTH_TOKEN');
				$twilio_number = env('TWILLIO_NUMBER');
				$twillio_sender_id = env('APP_NAME');

				Log::notice("Sending Twillio sms to " . $phoneNumber);

				$client = new Client($account_sid, $auth_token);
                $client->messages->create(
					$phoneNumber,
					array(
						'from' => $twillio_sender_id,
						'body' => $message
					)
				);
			} catch (\Exception $e) {
				 Log::notice($e);
				 return $e->getMessage();
            }

			Log::notice("Sent!!");
	} 
}