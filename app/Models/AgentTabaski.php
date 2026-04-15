<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentTabaski extends Model
{
    protected $table = 'agents_tabaski';

    protected $fillable = [
        'prenom', 'nom', 'poste', 'direction', 'region',
        'prenom_normalise', 'nom_normalise',
    ];

    public function inscription()
    {
        return $this->hasOne(AvanceTabaski::class, 'agent_id');
    }

    public static function normaliser(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = str_replace([' ', '-', "'"], '', $str);
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

    public static function rechercher(string $prenom, string $nom): \Illuminate\Database\Eloquent\Collection
    {
        $prenomN = self::normaliser($prenom);
        $nomN    = self::normaliser($nom);

        return self::where('prenom_normalise', 'LIKE', "%{$prenomN}%")
                   ->where('nom_normalise', 'LIKE', "%{$nomN}%")
                   ->get();
    }
}
