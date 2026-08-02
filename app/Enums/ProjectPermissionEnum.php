<?php

namespace App\Enums;

enum ProjectPermissionEnum: string
{
    case View = 'view_projects';
    case Create = 'create_projects';
    case Update = 'update_projects';
    case Delete = 'delete_projects';
    case Export = 'export_projects';
}
