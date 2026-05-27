<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * Informations du cabinet et paramètres de configuration.
 * GET public   → infos vitrine + thème pour le frontend
 * PUT admin    → mise à jour des paramètres par l'administrateur
 */
class ClinicController extends Controller
{
    /**
     * Retourne les informations publiques du cabinet (vitrine + thème).
     * Appelé par le frontend au chargement pour appliquer le thème.
     */
    public function public(): JsonResponse
    {
        $general = Setting::group('general');
        $appearance = Setting::group('appearance');
        $modules = Setting::group('modules');

        return response()->json([
            'data' => [
                'name' => $general['clinic.name'] ?? '',
                'phone' => $general['clinic.phone'] ?? '',
                'whatsapp' => $general['clinic.whatsapp'] ?? '',
                'address' => $general['clinic.address'] ?? '',
                'maps_url' => $general['clinic.maps_url'] ?? '',
                'logo_url' => '', //$general['clinic.logo_path']
                    // ? asset('storage/' . $general['clinic.logo_path'])
                    // : null,
                'theme' => [
                    'primary' => $appearance['appearance.primary'] ?? '#0F6E56',
                    'primary_fg' => $appearance['appearance.primary_fg'] ?? '#ffffff',
                    'secondary' => $appearance['appearance.secondary'] ?? '#E1F5EE',
                    'radius' => $appearance['appearance.radius'] ?? '0.5rem',
                    'font' => $appearance['appearance.font'] ?? 'Inter',
                ],
                'modules' => [
                    'billing' => filter_var($modules['module.billing'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'consultation' => filter_var($modules['module.consultation'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'prescription' => filter_var($modules['module.prescription'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'patient_file' => filter_var($modules['module.patient_file'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'reports' => filter_var($modules['module.reports'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ],
            ],
        ]);
    }

    /**
     * Retourne tous les groupes de paramètres pour l'interface admin.
     */
    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'data' => [
                'general' => Setting::group('general'),
                'appearance' => Setting::group('appearance'),
                'modules' => Setting::group('modules'),
                'billing' => Setting::group('billing'),
            ],
        ]);
    }

    /**
     * Met à jour un ou plusieurs paramètres.
     * Chaque clé du body correspond à une clé de settings.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        foreach ($request->validated('settings') as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['message' => 'Paramètres mis à jour.']);
    }
}
