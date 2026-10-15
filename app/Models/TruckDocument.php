<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TruckDocument extends Model
{
    /** The three documents every truck needs, and the folder each one is saved in. */
    public const TYPES = [
        'or'  => ['label' => 'Official Receipt (OR)',             'folder' => 'OR'],
        'cr'  => ['label' => 'Certificate of Registration (CR)',  'folder' => 'CR'],
        'cal' => ['label' => 'Calibration certificate',           'folder' => 'Calibration'],
    ];

    /** Private disk (see config/filesystems.php): storage/app/WebsiteFiles */
    public const DISK = 'website_files';

    /** Folder inside that disk: storage/app/WebsiteFiles/TruckMasterFiles/{OR|CR|Calibration} */
    public const ROOT = 'TruckMasterFiles';

    protected $fillable = ['truck_id', 'type', 'expiry_date', 'file_path', 'original_name', 'uploaded_by'];

    protected function casts(): array
    {
        return ['expiry_date' => 'date'];
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}