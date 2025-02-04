<?php


namespace App\Hooks\User;


use App\Helpers\Core\Traits\InstanceCreator;
use App\Hooks\HookContract;

class AfterUserPivotAction extends HookContract
{
    use InstanceCreator;

    public function handle()
    {
        cache()->forget('app-admin-'.$this->model->id.'-'.request('brand_id',''));
        cache()->forget('brand-admin-'.$this->model->id.'-'.request('brand_id',''));
        cache()->forget('user-'.$this->model->id);
        cache()->forget('user-roles-permissions-'.$this->model->id);
        cache()->forget('user-roles-'.$this->model->id);
        cache()->forget('auth-user-permissions-'.$this->model->id);
    }
}