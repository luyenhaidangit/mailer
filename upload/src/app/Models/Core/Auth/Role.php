<?php

namespace App\Models\Core\Auth;

use Altek\Eventually\Eventually;
use App\Models\Core\Auth\Traits\Boot\RoleBootTrait;
use App\Models\Core\Auth\Traits\Method\RoleMethod;
use App\Models\Core\Auth\Traits\Relationship\RoleRelationship;
use App\Models\Core\Auth\Traits\Rules\RoleRules;
use App\Models\Core\BaseModel;
use App\Models\Core\Traits\DescriptionGeneratorTrait;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends BaseModel
{

    protected $fillable = [
        'name', 'is_admin', 'is_default', 'type_id', 'brand_id', 'created_by'
    ];

    protected static $logAttributes = [
        'name', 'is_admin', 'createdBy.name', 'type.name', 'brand.name'
    ];

    use RoleMethod, RoleRelationship, Eventually, RoleRules, RoleBootTrait, DescriptionGeneratorTrait;

    protected $casts = [
        'is_admin' => 'boolean',
        'is_default' => 'boolean',
    ];

}
