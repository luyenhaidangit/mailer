<?php

namespace App\Http\Requests\Core\Brand;

use App\Http\Requests\BaseRequest;
use App\Models\Core\App\Brand\Brand;

class BrandRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws \App\Exceptions\GeneralException
     */
    public function rules()
    {
        return $this->initRules(new Brand());
    }
}
