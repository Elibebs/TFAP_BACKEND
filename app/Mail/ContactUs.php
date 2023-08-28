<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    
    protected $messageData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Array $messageData)
    {
        //
        $this->messageData = $messageData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@temafirstautoparts.com') 
                ->view('emails.contactus');
                /*->with([
                    'name' => $this->messageData['name'],
                    'email' => $this->messageData['email'],
                    'message' => $this->messageData['message']
                ]);*/
    }
}
