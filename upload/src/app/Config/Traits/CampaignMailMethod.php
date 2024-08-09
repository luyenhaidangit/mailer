<?php

namespace App\Config\Traits;

use App\Models\Campaign\Campaign;
use App\Repositories\App\StatusRepository;

trait CampaignMailMethod
{

    public int $tries = 3;
    public int $timeout = 30;

    public $last;
    public string $tracker_id;

    public Campaign $campaign;

    public object $subscriber;

    public function getSubject($subject): string
    {
        $vars = [
            '{campaign_name}' => $this->campaign->name,
            '{brand_name}' => optional(optional($this->campaign)->brand)->name,
            '{subscriber_name}' => optional($this->subscriber)->full_name,
            '{subscriber_email}' => optional($this->subscriber)->email,
        ];

        return strtr($subject, $vars);
    }

    public function getTemplate(): string
    {
        $logo = config()->get('settings.application.company_logo');
        $img = asset(empty($logo) ? '/images/logo.png' : $logo);

        $vars = [
            '{app_name}' => config('app.name'),
            '{app_logo}' => "<img src='$img'>",
            '{subscriber_name}' => optional($this->subscriber)->full_name,
            '{subscriber_email}' => optional($this->subscriber)->email,
            '{brand_name}' => optional(optional($this->campaign)->brand)->name,
            '{campaign_name}' => $this->campaign->name,
            '{unsubscribe_link}' => $this->subscriber->unsubscribe_url,
            '{app_link}' => url('/')
        ];

        $body = "<meta charset='UTF-8'>" . $this->campaign
            ->parseContent($vars);

        if (config('mail.driver') == 'smtp') {
            $url = route('webhook.smtp', ['hook' => 'opened', 'tracker_id' => $this->tracker_id]);
            $body .= "<img src='$url' style='visibility:hidden;'>";

            return $this->template($body)
                ->bypassAnchors(fn ($item) => route('webhook.smtp', [
                    'hook' => 'clicked',
                    'tracker_id' => $this->tracker_id,
                    'to' => $item->getAttribute('href')
                ]))->get();
        }

        return $body;
    }

    public function updateCampaignStatus(): bool
    {
        if (!$this->is_log_disabled() && $this->is_last()) {
            $sent = resolve(StatusRepository::class)->getStatusId('campaign', 'status_sent');
            $this->campaign->update(['status_id' => $sent]);
        }
        return true;
    }

    private function is_log_disabled(): bool
    {
        return $this->last === 'disable-log';
    }

    private function is_last(): bool
    {
        return $this->last === true;
    }

    public function setTrackerId(string $tracker_id)
    {
        $this->tracker_id = $tracker_id;

        return $this;
    }
}
