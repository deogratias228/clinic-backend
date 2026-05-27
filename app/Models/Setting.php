<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuration du cabinet sous forme clé-valeur.
 * Accès via Setting::get() et Setting::set() plutôt que directement.
 */
class Setting extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'key',
        'value',
        'group',
        'label'
    ];

    // ─── Accesseurs statiques ─────────────────────────────────────────────────

    /** Retourne la valeur d'une clé, avec fallback. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /** Persiste ou met à jour une valeur. */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /** Retourne un groupe entier sous forme de tableau associatif. */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /** Vérifie si un module est activé. */
    public static function moduleEnabled(string $module): bool
    {
        return filter_var(
            static::get("module.{$module}", false),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}