<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Journal non modifiable des transitions de statut des opérations
 * carburant. Insert-only par convention : jamais de update/delete
 * applicatif — une correction se fait par contre-écriture.
 */
class FuelStatusLog extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'action',
        'from_status', 'to_status', 'reason', 'actor_id',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
