<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hauler extends Model
{
    protected $fillable = ['name', 'contact', 'phone', 'address', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** The shape the Operations pages (ops.js) expect. The id is "H" + the database id, for example H5. */
    public function toOps(): array
    {
        return [
            'id'      => 'H'.$this->id,
            'name'    => $this->name,
            'contact' => $this->contact ?? '',
            'phone'   => $this->phone ?? '',
            'addr'    => $this->address,
        ];
    }

    /** All active haulers, oldest first, in the shape ops.js expects. */
    public static function forOps(): array
    {
        return static::where('is_active', true)->orderBy('id')->get()->map(fn (self $h) => $h->toOps())->all();
    }
}