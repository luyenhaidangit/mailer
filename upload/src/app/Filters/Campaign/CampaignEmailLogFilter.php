<?php


namespace App\Filters\Campaign;


use App\Filters\CollectionFilter;

class CampaignEmailLogFilter extends CollectionFilter
{
    public function init()
    {
        $term = request('search', '');
        $dates = [];
        if ($range = json_decode(request('date'), true))
            $dates =  array_values($range);
        $statuses = request('status') ? explode(',', request('status')) : [];
        $openCount = request('open_count') ?? 0;
        $clickCount = request('click_count') ?? 0;
        
        return $this->whereHasLike([
                'subscriber' => 'email',
                'campaign' => 'name'
            ],  $term)
            ->dateBetween('email_date', $dates)
            ->within('status_id', $statuses)
            ->whenHasCount('open_count', $openCount)
            ->whenHasCount('click_count', $clickCount)
            ->paginate(request('per_page', 10))
            ->trigger();
    }
}
