<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de mot de passe fort — Plateforme CSAR.
 *
 * Critères (conformes ANSSI / SENUM DCyber) :
 * - Minimum 12 caractères
 * - Au moins 1 majuscule
 * - Au moins 1 minuscule
 * - Au moins 1 chiffre
 * - Au moins 1 caractère spécial
 * - Pas de mots courants (csar, password, admin, 123456…)
 *
 * Usage :
 *   $request->validate([
 *       'password' => ['required', 'confirmed', new \App\Rules\StrongPassword()],
 *   ]);
 */
class StrongPassword implements ValidationRule
{
    /**
     * Mots interdits (liste non exhaustive).
     */
    protected array $blacklist = [
        'password', 'passw0rd', 'motdepasse', 'azerty', 'qwerty',
        'admin', 'administrateur', 'csar', 'senegal', 'dakar',
        '123456', '12345678', '00000000', 'iloveyou', 'welcome',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('Le :attribute doit être une chaîne de caractères.');
            return;
        }

        if (mb_strlen($value) < 12) {
            $fail('Le :attribute doit contenir au moins 12 caractères.');
            return;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Le :attribute doit contenir au moins une lettre majuscule.');
            return;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('Le :attribute doit contenir au moins une lettre minuscule.');
            return;
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('Le :attribute doit contenir au moins un chiffre.');
            return;
        }

        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('Le :attribute doit contenir au moins un caractère spécial (!@#$%^&*…).');
            return;
        }

        $lower = mb_strtolower($value);
        foreach ($this->blacklist as $forbidden) {
            if (str_contains($lower, $forbidden)) {
                $fail('Le :attribute contient un mot trop commun ou facile à deviner.');
                return;
            }
        }
    }
}
