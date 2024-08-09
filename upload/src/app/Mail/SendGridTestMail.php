<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SendGrid;
use SendGrid\Mail\Mail;

class SendGridTestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;

    public function __construct($subject, $message)
    {
        $this->subject = $subject;
        $this->message = $message;
    }


    public function build()
    {
        return $this->text('notification.mail.test.index', ['template' => $this->message]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function send($mailer)
    {
        try {

            $email = new Mail();
            $email->setFrom(config('mail.from.address'), config('mail.from.name'));
            $email->addTo($this->to[0]['address'], $this->to[0]['name']);
            $email->setSubject($this->subject);
            $email->addContent("text/html", $this->view($this->view, $this->viewData)->render());

            $sendgrid = new SendGrid(config('services.sendgrid.api_key'));

            $response = $sendgrid->send($email);

            if ($response->statusCode() != 202) {
                throw new \Exception('Failed to send email: ' . $response->body());
            }

            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
