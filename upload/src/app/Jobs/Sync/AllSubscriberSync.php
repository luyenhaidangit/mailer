<?php

namespace App\Jobs\Sync;

use App\Models\Lists\Lists;
use App\Models\Subscriber\Subscriber;
use App\Repositories\App\StatusRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AllSubscriberSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    public function __construct(Lists $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $statuses = resolve(StatusRepository::class)->subscriber('status_subscribed');

        Subscriber::query()
            ->select('id', 'status_id')
            ->whereIn('status_id', array_keys($statuses))
            ->chunk(1000, function (Collection $subscribers) {
                $this->model
                    ->subscribers()
                    ->attach( $subscribers->pluck('id')->toArray() );
            });

    }
}
