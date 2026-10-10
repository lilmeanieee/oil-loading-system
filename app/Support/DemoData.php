<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * DEMO ONLY. Sample data so the front-end renders without a database.
 * The backend developer replaces each method with real queries.
 */
class DemoData
{
    public static function signers(): array
    {
        return ['owner' => 'MARY JOY LLENES', 'officer' => 'HEDDA LYNN C. LATORRE'];
    }

    /** One product, three names: on the ATL, on the Loading Ticket, and on the DR / Withdrawal Certificate. */
    public static function products(): array
    {
        return [
            ['atl' => 'EXTREME 95', 'ticket' => 'R95', 'doc' => 'MOGAS R95', 'tax_note' => ''],
            ['atl' => 'EXTREME U', 'ticket' => 'R91', 'doc' => 'MOGAS R91', 'tax_note' => ''],
            ['atl' => 'EXCEED DIESEL', 'ticket' => 'ADO', 'doc' => 'DIESEL', 'tax_note' => 'CME 3%'],
            ['atl' => 'ETHANOL', 'ticket' => 'ETH', 'doc' => 'ETHANOL', 'tax_note' => ''],
        ];
    }

    public static function productByAtl(string $name): ?array
    {
        foreach (self::products() as $p) {
            if ($p['atl'] === $name) {
                return $p;
            }
        }

        return null;
    }

    public static function certStatus(string $expiry): string
    {
        $date = Carbon::parse($expiry)->startOfDay();
        $today = now()->startOfDay();

        if ($date->lt($today)) {
            return 'Expired';
        }

        return $today->diffInDays($date) <= 30 ? 'Expiring soon' : 'Valid';
    }

    public static function trucks(): array
    {
        $raw = [
            ['plate' => 'LAN-1731', 'hauler' => 'South Oil Corporation', 'driver' => 'Romel Monin', 'substitutes' => ['Pedro Santos'], 'capacity' => 30000, 'compartments' => [5000, 3000, 3000, 3000, 6000, 6000, 4000], 'certs' => ['OR' => '2027-03-15', 'CR' => '2027-03-15', 'Calibration' => '2027-02-10']],
            ['plate' => 'LAJ 3616', 'hauler' => 'Toto Alba Bulilit Gasoline Station', 'driver' => 'Melvin Pacheco', 'substitutes' => ['Ramon Cruz'], 'capacity' => 30000, 'compartments' => [6000, 6000, 4000, 2000, 4000, 2000, 2000, 4000], 'certs' => ['OR' => '2027-01-20', 'CR' => '2026-10-22', 'Calibration' => '2027-05-01']],
            ['plate' => 'GAB 4521', 'hauler' => 'Sample Hauling Inc.', 'driver' => 'Juan Dela Cruz', 'substitutes' => [], 'capacity' => 20000, 'compartments' => [5000, 5000, 5000, 5000], 'certs' => ['OR' => '2027-06-30', 'CR' => '2027-06-30', 'Calibration' => '2026-09-30']],
        ];

        return array_map(function ($t) {
            $t['cert_list'] = [];
            $t['issues'] = [];
            foreach ($t['certs'] as $name => $expiry) {
                $status = self::certStatus($expiry);
                $pretty = Carbon::parse($expiry)->format('M d, Y');
                $t['cert_list'][] = ['name' => $name, 'expiry' => $pretty, 'status' => $status];
                if ($status === 'Expired') {
                    $t['issues'][] = "$name expired on $pretty";
                }
            }
            $t['eligible'] = count($t['issues']) === 0;

            return $t;
        }, $raw);
    }

    private static function plan(array $rows): array
    {
        $out = [];
        foreach ($rows as $i => [$liters, $product]) {
            $out[] = ['comp' => $i + 1, 'liters' => $liters, 'product' => $product];
        }

        return $out;
    }

    private static function base(): array
    {
        return [
            'customer' => 'MNT OIL CORPORATION', 'customer_address' => 'BRGY. COLON, MAASIM SARANGANI PROVINCE',
            'attention' => 'PA FUEL 118 CORPORATION', 'address' => '', 'to' => 'Depot Manager', 'thru' => 'Dispatchers',
            'total_seals' => '', 'decline_reason' => null, 'docs_issued' => false, 'actual' => null,
        ];
    }

    public static function atls(): array
    {
        $b = self::base();

        $list = [
            $b + [
                'id' => 524, 'so_no' => '2026-10-0655', 'control_no' => '2026-423', 'date' => '2026-10-08',
                'authorized' => 'Romel Monin', 'hauler' => 'South Oil Corporation', 'plate' => 'LAN-1731', 'capacity' => '30KL',
                'destination' => 'Surallah', 'creator' => 'Ma\'am Jhona', 'status' => 'Draft',
                'items' => [['product' => 'EXTREME U', 'volume' => 15000]], 'plan' => [],
                'timeline' => [['label' => 'Draft saved', 'by' => 'Ma\'am Jhona', 'at' => '2026-10-08 09:10', 'note' => '']],
            ],
            $b + [
                'id' => 523, 'so_no' => '2026-10-0652', 'control_no' => '2026-422', 'date' => '2026-10-08',
                'authorized' => 'Romel Monin', 'hauler' => 'South Oil Corporation', 'plate' => 'LAN-1731', 'capacity' => '30KL',
                'destination' => 'Tupi', 'creator' => 'Ma\'am Jhona', 'status' => 'Submitted',
                'items' => [['product' => 'EXTREME 95', 'volume' => 15000], ['product' => 'EXCEED DIESEL', 'volume' => 15000]],
                'plan' => self::plan([[5000, 'EXCEED DIESEL'], [3000, 'EXCEED DIESEL'], [3000, 'EXTREME 95'], [3000, 'EXCEED DIESEL'], [6000, 'EXTREME 95'], [6000, 'EXTREME 95'], [4000, 'EXCEED DIESEL']]),
                'timeline' => [
                    ['label' => 'Draft saved', 'by' => 'Ma\'am Jhona', 'at' => '2026-10-08 08:40', 'note' => ''],
                    ['label' => 'Submitted for approval', 'by' => 'Ma\'am Jhona', 'at' => '2026-10-08 08:55', 'note' => ''],
                ],
            ],
            $b + [
                'id' => 522, 'so_no' => '2026-10-0649', 'control_no' => '2026-421', 'date' => '2026-10-07',
                'authorized' => 'Melvin Pacheco', 'hauler' => 'Toto Alba Bulilit Gasoline Station', 'plate' => 'LAJ 3616', 'capacity' => '30KL',
                'destination' => 'Bulilit', 'creator' => 'Ma\'am Jhona', 'status' => 'Declined',
                'decline_reason' => 'Compartment 3 is over capacity. Correct the allocation and resubmit.',
                'items' => [['product' => 'EXTREME 95', 'volume' => 20000], ['product' => 'EXTREME U', 'volume' => 10000]],
                'plan' => self::plan([[6000, 'EXTREME 95'], [6000, 'EXTREME 95'], [5000, 'EXTREME 95'], [3000, 'EXTREME 95'], [4000, 'EXTREME U'], [2000, 'EXTREME U'], [2000, 'EXTREME U'], [2000, 'EXTREME U']]),
                'timeline' => [
                    ['label' => 'Submitted for approval', 'by' => 'Ma\'am Jhona', 'at' => '2026-10-07 14:05', 'note' => ''],
                    ['label' => 'Declined', 'by' => 'Ma\'am Joy', 'at' => '2026-10-07 14:30', 'note' => 'Compartment 3 is over capacity. Correct the allocation and resubmit.'],
                ],
            ],
            $b + [
                'id' => 521, 'so_no' => '2026-10-0646', 'control_no' => '2026-420', 'date' => '2026-10-01',
                'authorized' => 'Melvin Pacheco', 'hauler' => 'Toto Alba Bulilit Gasoline Station', 'plate' => 'LAJ 3616', 'capacity' => '30KL',
                'destination' => 'Bulilit', 'creator' => 'Ma\'am Jhona', 'status' => 'Loading',
                'items' => [['product' => 'EXTREME 95', 'volume' => 18000], ['product' => 'EXTREME U', 'volume' => 6000], ['product' => 'EXCEED DIESEL', 'volume' => 6000]],
                'plan' => self::plan([[6000, 'EXTREME 95'], [6000, 'EXTREME 95'], [4000, 'EXTREME 95'], [2000, 'EXTREME 95'], [4000, 'EXTREME U'], [2000, 'EXTREME U'], [2000, 'EXCEED DIESEL'], [4000, 'EXCEED DIESEL']]),
                'docs_issued' => true,
                'timeline' => [
                    ['label' => 'Submitted for approval', 'by' => 'Ma\'am Jhona', 'at' => '2026-10-01 07:50', 'note' => ''],
                    ['label' => 'Approved', 'by' => 'Ma\'am Joy', 'at' => '2026-10-01 08:10', 'note' => ''],
                    ['label' => 'Documents issued', 'by' => 'Ma\'am Joy', 'at' => '2026-10-01 08:30', 'note' => 'Delivery receipt and withdrawal certificate'],
                    ['label' => 'Loading started', 'by' => 'Ma\'am Joy', 'at' => '2026-10-01 09:00', 'note' => ''],
                ],
            ],
            $b + [
                'id' => 520, 'so_no' => '2026-09-0605', 'control_no' => '2026-419', 'date' => '2026-09-30',
                'authorized' => 'Romel Monin', 'hauler' => 'South Oil Corporation', 'plate' => 'LAN-1731', 'capacity' => '30KL',
                'total_seals' => '15 PCS', 'destination' => 'Surallah', 'creator' => 'Ma\'am Jhona', 'status' => 'Approved',
                'items' => [['product' => 'EXTREME 95', 'volume' => 12000], ['product' => 'EXTREME U', 'volume' => 10000], ['product' => 'EXCEED DIESEL', 'volume' => 8000]],
                'plan' => self::plan([[5000, 'EXCEED DIESEL'], [3000, 'EXCEED DIESEL'], [3000, 'EXTREME U'], [3000, 'EXTREME U'], [6000, 'EXTREME 95'], [6000, 'EXTREME 95'], [4000, 'EXTREME U']]),
                'timeline' => [
                    ['label' => 'Submitted for approval', 'by' => 'Ma\'am Jhona', 'at' => '2026-09-30 10:00', 'note' => ''],
                    ['label' => 'Approved', 'by' => 'Ma\'am Joy', 'at' => '2026-09-30 10:20', 'note' => ''],
                ],
            ],
            $b + [
                'id' => 519, 'so_no' => '2026-09-0590', 'control_no' => '2026-418', 'date' => '2026-09-29',
                'authorized' => 'Juan Dela Cruz', 'hauler' => 'Sample Hauling Inc.', 'plate' => 'GAB 4521', 'capacity' => '20KL',
                'destination' => 'Koronadal', 'creator' => 'Ma\'am Jhona', 'status' => 'Completed',
                'items' => [['product' => 'EXCEED DIESEL', 'volume' => 20000]], 'plan' => [], 'docs_issued' => true, 'actual' => 20000,
                'timeline' => [
                    ['label' => 'Approved', 'by' => 'Ma\'am Joy', 'at' => '2026-09-29 09:00', 'note' => ''],
                    ['label' => 'Documents issued', 'by' => 'Ma\'am Joy', 'at' => '2026-09-29 09:20', 'note' => ''],
                    ['label' => 'Completed', 'by' => 'Ma\'am Joy', 'at' => '2026-09-29 11:45', 'note' => 'Actual 20,000 L confirmed'],
                ],
            ],
            $b + [
                'id' => 518, 'so_no' => '2026-09-0577', 'control_no' => '2026-417', 'date' => '2026-09-29',
                'authorized' => 'Romel Monin', 'hauler' => 'South Oil Corporation', 'plate' => 'LAN-1731', 'capacity' => '30KL',
                'destination' => 'Tupi', 'creator' => 'Ma\'am Jhona', 'status' => 'Completed',
                'items' => [['product' => 'EXTREME U', 'volume' => 30000]], 'plan' => [], 'docs_issued' => true, 'actual' => 30000,
                'timeline' => [
                    ['label' => 'Approved', 'by' => 'Ma\'am Joy', 'at' => '2026-09-29 07:30', 'note' => ''],
                    ['label' => 'Completed', 'by' => 'Ma\'am Joy', 'at' => '2026-09-29 10:05', 'note' => 'Actual 30,000 L confirmed'],
                ],
            ],
        ];

        return array_map(fn ($a) => $a + ['atl_no' => 'ATL#'.$a['id']], $list);
    }

    /** Numbers already used, so the form can warn about duplicates. */
    public static function taken(?int $exceptId = null): array
    {
        $rows = array_filter(self::atls(), fn ($a) => $a['id'] !== $exceptId);

        return [
            'so'      => array_values(array_column($rows, 'so_no')),
            'control' => array_values(array_column($rows, 'control_no')),
            'atl'     => array_values(array_column($rows, 'atl_no')),
        ];
    }

    public static function findAtl(int $id): ?array
    {
        foreach (self::atls() as $atl) {
            if ($atl['id'] === $id) {
                return $atl;
            }
        }

        return null;
    }

    public static function documents(): array
    {
        return [
            ['type' => 'Delivery Receipt', 'no' => '1189', 'atl' => 521, 'so' => '2026-10-0646', 'issued' => '2026-10-01', 'by' => 'Ma\'am Joy', 'prints' => 1],
            ['type' => 'Withdrawal Certificate', 'no' => 'WCP2025 02460097', 'atl' => 521, 'so' => '2026-10-0646', 'issued' => '2026-10-01', 'by' => 'Ma\'am Joy', 'prints' => 1],
            ['type' => 'Delivery Receipt', 'no' => '1188', 'atl' => 519, 'so' => '2026-09-0590', 'issued' => '2026-09-29', 'by' => 'Ma\'am Joy', 'prints' => 2],
            ['type' => 'Withdrawal Certificate', 'no' => 'WCP2025 02460096', 'atl' => 519, 'so' => '2026-09-0590', 'issued' => '2026-09-29', 'by' => 'Ma\'am Joy', 'prints' => 2],
        ];
    }

    /** Suggested next numbers. IT configures the real ranges. Operations can overwrite them. */
    public static function nextNumbers(): array
    {
        return ['dr' => '1190', 'wcp' => 'WCP2025 02460098'];
    }
}
