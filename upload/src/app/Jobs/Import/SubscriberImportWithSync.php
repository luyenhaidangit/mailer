<?php

namespace App\Jobs\Import;

use App\Models\Lists\Lists;
use App\Models\Subscriber\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriberImportWithSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunks;
    protected $lists;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chunks, $lists)
    {
        $this->chunks = $chunks;
        $this->lists = $lists;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Subscriber::query()->insert($this->chunks);

        $chunk_subscribers = Subscriber::query()
            ->whereIn('email', collect($this->chunks)->pluck('email'))
            ->pluck('id');
        $this->lists->each(function (Lists $list) use ($chunk_subscribers){
            $list->subscribers()->attach($chunk_subscribers);
        });
    }
}
