<?php

namespace Database\Seeders;

use App\Models\ActCatalog;
use App\Models\Doctor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder principal — installe le cabinet avec toutes les données initiales.
 * À lancer après chaque nouveau déploiement : php artisan db:seed.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedUsers();
        $this->seedClinicalData();
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    private function seedSettings(): void
    {
        $defaults = [
            // Infos cabinet
            ['key' => 'clinic.name', 'value' => 'Clinique Espoir', 'group' => 'general', 'label' => 'Nom du cabinet'],
            ['key' => 'clinic.phone', 'value' => '+228 90 00 00 00', 'group' => 'general', 'label' => 'Téléphone'],
            ['key' => 'clinic.whatsapp', 'value' => '+22890000000', 'group' => 'general', 'label' => 'WhatsApp'],
            ['key' => 'clinic.address', 'value' => 'Lomé, Togo', 'group' => 'general', 'label' => 'Adresse'],
            ['key' => 'clinic.maps_url', 'value' => '', 'group' => 'general', 'label' => 'Lien Google Maps'],

            // Apparence
            ['key' => 'appearance.primary', 'value' => '#0F6E56', 'group' => 'appearance', 'label' => 'Couleur principale'],
            ['key' => 'appearance.primary_fg', 'value' => '#ffffff', 'group' => 'appearance', 'label' => 'Texte sur couleur principale'],
            ['key' => 'appearance.secondary', 'value' => '#E1F5EE', 'group' => 'appearance', 'label' => 'Couleur secondaire'],
            ['key' => 'appearance.radius', 'value' => '0.5rem', 'group' => 'appearance', 'label' => 'Arrondi des bordures'],
            ['key' => 'appearance.font', 'value' => 'Inter', 'group' => 'appearance', 'label' => 'Police'],
            ['key' => 'clinic.logo_path', 'value' => '', 'group' => 'appearance', 'label' => 'Logo'],

            // Facturation
            ['key' => 'billing.currency', 'value' => 'XOF', 'group' => 'billing', 'label' => 'Devise'],
            ['key' => 'billing.invoice_prefix', 'value' => 'INV', 'group' => 'billing', 'label' => 'Préfixe facture'],

            // Modules
            ['key' => 'module.billing', 'value' => 'true', 'group' => 'modules', 'label' => 'Facturation'],
            ['key' => 'module.consultation', 'value' => 'true', 'group' => 'modules', 'label' => 'Consultations'],
            ['key' => 'module.prescription', 'value' => 'true', 'group' => 'modules', 'label' => 'Ordonnances'],
            ['key' => 'module.patient_file', 'value' => 'true', 'group' => 'modules', 'label' => 'Dossiers patients'],
            ['key' => 'module.reports', 'value' => 'true', 'group' => 'modules', 'label' => 'Rapports'],

            // Flux visites
            ['key' => 'visit.skip_payment_step', 'value' => 'false', 'group' => 'general', 'label' => 'Flux simplifié (sans étape paiement distincte)'],
        ];

        foreach ($defaults as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }

    // ─── Permissions ──────────────────────────────────────────────────────────

    private function seedPermissions(): void
    {
        $permissions = [
            // Rendez-vous
            ['name' => 'appointments.view', 'module' => 'appointments', 'label' => 'Voir les rendez-vous'],
            ['name' => 'appointments.create', 'module' => 'appointments', 'label' => 'Créer un rendez-vous'],
            ['name' => 'appointments.update', 'module' => 'appointments', 'label' => 'Modifier un rendez-vous'],
            ['name' => 'appointments.delete', 'module' => 'appointments', 'label' => 'Supprimer un rendez-vous'],

            // Patients
            ['name' => 'patients.view', 'module' => 'patients', 'label' => 'Voir les dossiers patients'],
            ['name' => 'patients.create', 'module' => 'patients', 'label' => 'Créer un dossier patient'],
            ['name' => 'patients.update', 'module' => 'patients', 'label' => 'Modifier un dossier patient'],
            ['name' => 'patients.delete', 'module' => 'patients', 'label' => 'Supprimer un dossier patient'],

            // Visites
            ['name' => 'visits.view', 'module' => 'visits', 'label' => 'Voir les visites'],
            ['name' => 'visits.create', 'module' => 'visits', 'label' => 'Enregistrer une visite'],
            ['name' => 'visits.update', 'module' => 'visits', 'label' => 'Mettre à jour une visite'],

            // Consultations
            ['name' => 'consultations.view', 'module' => 'consultations', 'label' => 'Voir les consultations'],
            ['name' => 'consultations.create', 'module' => 'consultations', 'label' => 'Créer une consultation'],
            ['name' => 'consultations.update', 'module' => 'consultations', 'label' => 'Modifier une consultation'],

            // Facturation
            ['name' => 'billing.view', 'module' => 'billing', 'label' => 'Voir les factures'],
            ['name' => 'billing.create', 'module' => 'billing', 'label' => 'Créer une facture'],
            ['name' => 'billing.update', 'module' => 'billing', 'label' => 'Modifier une facture'],
            ['name' => 'billing.delete', 'module' => 'billing', 'label' => 'Annuler une facture'],
            ['name' => 'payments.create', 'module' => 'billing', 'label' => 'Enregistrer un paiement'],

            // Médecins & horaires
            ['name' => 'doctors.view', 'module' => 'doctors', 'label' => 'Voir les médecins'],
            ['name' => 'doctors.manage', 'module' => 'doctors', 'label' => 'Gérer les médecins'],
            ['name' => 'schedules.manage', 'module' => 'doctors', 'label' => 'Gérer les horaires'],

            // Rapports
            ['name' => 'reports.view', 'module' => 'reports', 'label' => 'Voir les rapports'],

            // Administration
            ['name' => 'users.view', 'module' => 'admin', 'label' => 'Voir les utilisateurs'],
            ['name' => 'users.manage', 'module' => 'admin', 'label' => 'Gérer les utilisateurs'],
            ['name' => 'roles.manage', 'module' => 'admin', 'label' => 'Gérer les rôles'],
            ['name' => 'settings.manage', 'module' => 'admin', 'label' => 'Gérer les paramètres'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }
    }

    // ─── Rôles ────────────────────────────────────────────────────────────────

    private function seedRoles(): void
    {
        $allPermissions = Permission::pluck('id')->toArray();
        $apptPermissions = Permission::whereIn('module', ['appointments', 'patients', 'visits'])->pluck('id')->toArray();
        $billPermissions = Permission::whereIn('module', ['billing', 'appointments', 'patients', 'visits'])->pluck('id')->toArray();
        $doctorPermissions = Permission::whereIn('module', ['consultations', 'appointments', 'patients', 'visits'])->pluck('id')->toArray();

        $roles = [
            [
                'name' => 'super_admin',
                'label' => 'Super administrateur',
                'is_default' => false,
                'is_system' => true,
                'permissions' => $allPermissions,
            ],
            [
                'name' => 'secretaire',
                'label' => 'Secrétaire',
                'is_default' => true,
                'is_system' => false,
                'permissions' => $apptPermissions,
            ],
            [
                'name' => 'comptable',
                'label' => 'Comptable',
                'is_default' => false,
                'is_system' => false,
                'permissions' => $billPermissions,
            ],
            [
                'name' => 'medecin',
                'label' => 'Médecin',
                'is_default' => false,
                'is_system' => false,
                'permissions' => $doctorPermissions,
            ],
        ];

        foreach ($roles as $r) {
            $permissions = $r['permissions'];
            unset($r['permissions']);
            $role = Role::firstOrCreate(['name' => $r['name']], $r);
            $role->permissions()->sync($permissions);
        }
    }

    // ─── Utilisateurs ─────────────────────────────────────────────────────────

    private function seedUsers(): void
    {
        $adminRole = Role::where('name', 'super_admin')->first();
        $secretRole = Role::where('name', 'secretaire')->first();

        User::firstOrCreate(
            ['email' => 'admin@cabinet.local'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'secretaire@cabinet.local'],
            [
                'role_id' => $secretRole->id,
                'name' => 'Secrétaire',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }

    // ─── Données cliniques de démo ────────────────────────────────────────────

    private function seedClinicalData(): void
    {
        $generaliste = Specialty::firstOrCreate(['name' => 'Médecine générale'], ['color' => '#0F6E56', 'sort_order' => 1]);
        $pediatrie = Specialty::firstOrCreate(['name' => 'Pédiatrie'], ['color' => '#185FA5', 'sort_order' => 2]);
        $gyneco = Specialty::firstOrCreate(['name' => 'Gynécologie'], ['color' => '#993556', 'sort_order' => 3]);

        $doctor1 = Doctor::firstOrCreate(
            ['name' => 'Dr. Kofi Mensah'],
            [
                'specialty_id' => $generaliste->id,
                'slot_duration_minutes' => 30,
                'accepts_online_booking' => true,
                'uses_consultation_module' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $doctor2 = Doctor::firstOrCreate(
            ['name' => 'Dr. Ama Sow'],
            [
                'specialty_id' => $pediatrie->id,
                'slot_duration_minutes' => 30,
                'accepts_online_booking' => true,
                'uses_consultation_module' => false, // préfère son carnet
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Horaires Dr. Mensah : lun-ven 08:00-17:00, sam 08:00-12:00
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            Schedule::firstOrCreate(
                ['doctor_id' => $doctor1->id, 'day_of_week' => $day, 'start_time' => '08:00'],
                ['end_time' => '17:00', 'is_active' => true]
            );
        }
        Schedule::firstOrCreate(
            ['doctor_id' => $doctor1->id, 'day_of_week' => 'saturday', 'start_time' => '08:00'],
            ['end_time' => '12:00', 'is_active' => true]
        );

        // Horaires Dr. Sow : lun-jeu 09:00-16:00
        foreach (['monday', 'tuesday', 'wednesday', 'thursday'] as $day) {
            Schedule::firstOrCreate(
                ['doctor_id' => $doctor2->id, 'day_of_week' => $day, 'start_time' => '09:00'],
                ['end_time' => '16:00', 'is_active' => true]
            );
        }

        // Catalogue d'actes
        $acts = [
            ['name' => 'Consultation générale', 'category' => 'consultation', 'default_price' => 5000, 'sort_order' => 1],
            ['name' => 'Consultation spécialisée', 'category' => 'consultation', 'default_price' => 10000, 'sort_order' => 2],
            ['name' => 'Ordonnance', 'category' => 'consultation', 'default_price' => 1000, 'sort_order' => 3],
            ['name' => 'Prise de tension', 'category' => 'soin', 'default_price' => 500, 'sort_order' => 4],
            ['name' => 'Pansement simple', 'category' => 'soin', 'default_price' => 2000, 'sort_order' => 5],
            ['name' => 'Injection', 'category' => 'soin', 'default_price' => 1500, 'sort_order' => 6],
            ['name' => 'Analyse sanguine', 'category' => 'biologie', 'default_price' => 8000, 'sort_order' => 7],
            ['name' => 'Radiographie', 'category' => 'imagerie', 'default_price' => 15000, 'sort_order' => 8],
            ['name' => 'Échographie', 'category' => 'imagerie', 'default_price' => 20000, 'sort_order' => 9],
        ];

        foreach ($acts as $act) {
            ActCatalog::firstOrCreate(['name' => $act['name']], array_merge($act, ['currency' => 'XOF', 'is_active' => true]));
        }
    }
}