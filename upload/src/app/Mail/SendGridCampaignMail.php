<?php

namespace App\Mail;

use App\Config\Traits\CampaignMailMethod;
use App\Helpers\Core\Traits\FileHandler;
use App\Helpers\Traits\InteractsWithTemplate;
use App\Models\Campaign\Campaign;
use App\Models\Core\File\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SendGrid;
use SendGrid\Mail\Mail;

class SendGridCampaignMail extends Mailable
{
    use FileHandler, InteractsWithTemplate, CampaignMailMethod, Queueable, SerializesModels;

    public function __construct(Campaign $campaign, object $subscriber, $last = false)
    {
        $this->campaign = $campaign;
        $this->subscriber = $subscriber;
        $this->last = $last;
    }

    public function build()
    {
        $this->updateCampaignStatus();

        return $this->text('mail.campaign.campaign', ['html' => $this->getTemplate()]);
    }

    public function send($mailer)
    {
        try {
            $subject = $this->getSubject($this->campaign->subject);

            $email = new Mail();
            $email->setFrom(config('mail.from.address'), config('mail.from.name'));
            $email->addTo($this->to[0]['address'], $this->to[0]['name']);
            $email->setSubject($subject);
            $email->addContent("text/html", $this->view($this->view, $this->viewData)->render());

            if (optional($this->campaign->attachments)->count()) {
                $this->campaign->attachments->each(function (File $file) use ($email) {
                    $path = $this->removeStorage('public' . $file->path);
                    $fileName = basename(storage_path($path));
                    $mimeType = Storage::mimeType($path);
                    $fileContent = Storage::get($path);
                    $email->addAttachment(
                        base64_encode($fileContent),
                        $mimeType,
                        $fileName
                    );
                });
            }

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
