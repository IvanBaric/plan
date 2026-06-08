<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CurrentTeamResolver
{
    public function current(): Model;
}
