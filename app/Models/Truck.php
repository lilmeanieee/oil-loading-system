<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model
{
    protected $fillable = ['plate', 'hauler_id', 'driver_id', 'substitute_driver_id', 'capacity', 'compartments', 'created_by'];

    protected function casts(): array
    {
        return ['compartments' => 'array'];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TruckDocument::class);
    }

    /** The shape the Operations pages (ops.js) expect. "tid" is the database id, used to save changes. */
    public function toOps(): array
    {
        $docs = [];

        foreach (TruckDocument::TYPES as $type => $meta) {
            $doc = $this->documents->firstWhere('type', $type);

            $docs[$type] = [
                'exp'  => $doc?->expiry_date?->format('Y-m-d') ?? '',
                'file' => $doc?->original_name ?? '',
                'url'  => ($doc && $doc->file_path)
                    ? route('trucks.document', ['id' => $this->id, 'type' => $type], false)
                    : '',
            ];
        }

        return [
            'tid'   => $this->id,
            'plate' => $this->plate,
            'h'     => 'H'.$this->hauler_id,
            'd'     => $this->driver_id ? 'D'.$this->driver_id : '',
            's'     => $this->substitute_driver_id ? 'D'.$this->substitute_driver_id : '',
            'cap'   => (int) $this->capacity,
            'comps' => array_map('intval', $this->compartments ?? []),
            'docs'  => $docs,
        ];
    }

    public static function forOps(): array
    {
        return static::with('documents')->orderBy('id')->get()->map(fn (self $t) => $t->toOps())->all();
    }
}