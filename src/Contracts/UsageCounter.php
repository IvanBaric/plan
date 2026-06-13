<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UsageCounter
{
    public function countPublicQrMenus(Model $billable): int;

    public function countMenuItems(Model $billable): int;

    public function countLanguages(Model $billable): int;

    public function countItemImages(Model $billable): int;

    public function countTeamMembers(Model $billable): int;
}
