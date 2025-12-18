<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommissionerApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject('Commissioner Application Received')
                    ->view('emails.commissioner_application_received')
                    ->with('application', $this->application);
    }
}
