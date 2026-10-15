<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['hauler_id', 'name', 'phone'];

    /** The shape the Operations pages (ops.js) expect. The id is "D" + the database id, for example D7. */
    public function toOps(): array
    {
        return [
            'id'    => 'D'.$this->id,
            'name'  => $this->name,
            'phone' => $this->phone ?? '',
            'h'     => 'H'.$this->hauler_id,
        ];
    }

    public static function forOps(): array
    {
        return static::orderBy('id')->get()->map(fn (self $d) => $d->toOps())->all();
    }
}