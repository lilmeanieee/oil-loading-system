<?php

namespace App\Support;

use App\Models\Driver;
use App\Models\Hauler;
use App\Models\Truck;

class OpsData
{
    // Raise this number whenever the browser's saved working copy must be thrown away.
    public const VERSION = 2;

    public static function seed(?string $user = null): array
    {
        $user = $user ?: 'Operations';

        return [
            'ver'     => self::VERSION,
            'user'    => $user,
            'staff'   => [$user],
            'seq'     => ['lt' => 0, 'dr' => 0, 'wc' => 0],
            'haulers' => Hauler::forOps(),
            'drivers' => Driver::forOps(),
            'trucks'  => Truck::forOps(),
            'atl'     => [],
            'act'     => [],
        ];
    }
}