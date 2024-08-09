<?php

namespace App\Http\Controllers\WebForm;

use App\Http\Controllers\Controller;
use App\Models\Subscriber\API;
use App\Models\Subscriber\Subscriber;
use App\Repositories\CustomField\CustomFieldRepository;
use Illuminate\Http\Request;

class WebFormController extends Controller
{
    public function subscriberCreate(){
        $getApiurl = $this->getApiUrl();
        return view('brands.subscribers.webForm.create', ['api_key' => $getApiurl['api_key'], 'store_url' => $getApiurl['url']['store'], 'update_url' => $getApiurl['url']['update']]);
    }

    public function subscriberEdit($brand, $id)
    {
        $getApiurl = $this->getApiUrl();
        return view('brands.subscribers.webForm.create', ['api_key' => $getApiurl['api_key'],'store_url' => $getApiurl['url']['store'], 'update_url'=>$getApiurl['url']['update'], 'brand'=>$brand,'id' => $id]);
    }

    public function getSubscriberById($brand, $id)
    {
        return Subscriber::find($id)->load(
            'lists:id,name',
            'customFields:id,value,custom_field_id,contextable_type,contextable_id',
            'customFields.customField:id,name'
        );
    }

    public function getApiUrl()
    {
        $api = API::query()
            ->where('brand_id', brand()->id)
            ->firstOrNew();

        return [
            'api_key' => $api->key,
            'url' => [
                'store' => route('subscriber-external-api', [
                    'brand' => brand()->short_name
                ]),
                'update' => route('subscriber-update-api', [
                    'brand' => brand()->short_name,
                ])
            ]
        ];
    }

    public function getCustomField($brand, $context = 'subscriber')
    {
        request()->merge(['brand_id'=> brand()->id]);
       return resolve(CustomFieldRepository::class)->fields($context);
    }
}
