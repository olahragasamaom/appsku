<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ModuleSeeder extends Seeder
{
    /**
     * The granular actions generated for every module.
     *
     * @var list<string>
     */
    public const ACTIONS = ['view', 'edit', 'delete'];

    /**
     * Seed the fixed list of superadmin modules that can be assigned to user levels.
     *
     * The `icon` column stores the inner SVG path(s) so the sidebar can render
     * each module dynamically without hardcoding markup.
     *
     * @return list<array{key:string,label:string,route_name:string,route_pattern:string,icon:string,grup:string,urutan:int}>
     */
    private function modules(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route_name' => 'superadmin.dashboard', 'route_pattern' => 'superadmin.dashboard', 'grup' => 'Utama', 'urutan' => 1, 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['key' => 'dashboard2', 'label' => 'Dashboard IKU', 'route_name' => 'superadmin.dashboard2', 'route_pattern' => 'superadmin.dashboard2', 'grup' => 'Utama', 'urutan' => 2, 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],

            ['key' => 'plans', 'label' => 'Plans', 'route_name' => 'superadmin.plans.index', 'route_pattern' => 'superadmin.plans.*', 'grup' => 'Manajemen', 'urutan' => 10, 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['key' => 'jenis-ujian', 'label' => 'Jenis Ujian', 'route_name' => 'superadmin.jenis-ujian.index', 'route_pattern' => 'superadmin.jenis-ujian.*', 'grup' => 'Manajemen', 'urutan' => 11, 'icon' => 'M9 5h6m-6 4h6m-6 4h4m-6 8h10a2 2 0 002-2V7.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0014.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['key' => 'sub-jenis-ujian', 'label' => 'Sub Jenis Ujian', 'route_name' => 'superadmin.sub-jenis-ujian.index', 'route_pattern' => 'superadmin.sub-jenis-ujian.*', 'grup' => 'Manajemen', 'urutan' => 12, 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10'],
            ['key' => 'sub-indikator', 'label' => 'Sub Indikator', 'route_name' => 'superadmin.sub-indikator.index', 'route_pattern' => 'superadmin.sub-indikator.*', 'grup' => 'Manajemen', 'urutan' => 13, 'icon' => 'M4 6h16M7 12h13M10 18h10'],
            ['key' => 'soal', 'label' => 'Bank Soal', 'route_name' => 'superadmin.soal.index', 'route_pattern' => 'superadmin.soal.*', 'grup' => 'Manajemen', 'urutan' => 14, 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'ujian', 'label' => 'Manajemen Ujian', 'route_name' => 'superadmin.ujian.index', 'route_pattern' => 'superadmin.ujian.*', 'grup' => 'Manajemen', 'urutan' => 15, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['key' => 'paket', 'label' => 'Paket Member', 'route_name' => 'superadmin.paket.index', 'route_pattern' => 'superadmin.paket.*', 'grup' => 'Manajemen', 'urutan' => 16, 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['key' => 'peserta', 'label' => 'Master Peserta', 'route_name' => 'superadmin.peserta.index', 'route_pattern' => 'superadmin.peserta.*', 'grup' => 'Manajemen', 'urutan' => 17, 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65'],
            ['key' => 'subscriptions', 'label' => 'Subscriptions', 'route_name' => 'superadmin.subscriptions.index', 'route_pattern' => 'superadmin.subscriptions.*', 'grup' => 'Manajemen', 'urutan' => 18, 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
            ['key' => 'companies', 'label' => 'Perusahaan', 'route_name' => 'superadmin.companies.index', 'route_pattern' => 'superadmin.companies.*', 'grup' => 'Manajemen', 'urutan' => 19, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['key' => 'user-levels', 'label' => 'Manajemen Modul', 'route_name' => 'superadmin.user-levels.index', 'route_pattern' => 'superadmin.user-levels.*', 'grup' => 'Manajemen', 'urutan' => 20, 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],

            ['key' => 'payment-gateways', 'label' => 'Payment Gateways', 'route_name' => 'superadmin.payment-gateways.index', 'route_pattern' => 'superadmin.payment-gateways.*', 'grup' => 'Pembayaran', 'urutan' => 30, 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['key' => 'payments', 'label' => 'Riwayat Pembayaran', 'route_name' => 'superadmin.payments.index', 'route_pattern' => 'superadmin.payments.*', 'grup' => 'Pembayaran', 'urutan' => 31, 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],

            ['key' => 'system-health', 'label' => 'System Health', 'route_name' => 'superadmin.system.health', 'route_pattern' => 'superadmin.system.health', 'grup' => 'Sistem', 'urutan' => 40, 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['key' => 'system-queue', 'label' => 'Queue Monitor', 'route_name' => 'superadmin.system.queue.index', 'route_pattern' => 'superadmin.system.queue.*', 'grup' => 'Sistem', 'urutan' => 41, 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['key' => 'system-email-logs', 'label' => 'Email Logs', 'route_name' => 'superadmin.system.email-logs.index', 'route_pattern' => 'superadmin.system.email-logs.*', 'grup' => 'Sistem', 'urutan' => 42, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['key' => 'system-notifications', 'label' => 'Notifications', 'route_name' => 'superadmin.system.notifications.index', 'route_pattern' => 'superadmin.system.notifications.*', 'grup' => 'Sistem', 'urutan' => 43, 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['key' => 'system-audit-logs', 'label' => 'Audit Logs', 'route_name' => 'superadmin.system.audit-logs.index', 'route_pattern' => 'superadmin.system.audit-logs.*', 'grup' => 'Sistem', 'urutan' => 44, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['key' => 'system-sessions', 'label' => 'Sessions', 'route_name' => 'superadmin.system.sessions.index', 'route_pattern' => 'superadmin.system.sessions.*', 'grup' => 'Sistem', 'urutan' => 45, 'icon' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'system-rate-limits', 'label' => 'Rate Limits', 'route_name' => 'superadmin.system.rate-limits.index', 'route_pattern' => 'superadmin.system.rate-limits.*', 'grup' => 'Sistem', 'urutan' => 46, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],

            ['key' => 'security-logs', 'label' => 'Attack Logs', 'route_name' => 'superadmin.security.logs.index', 'route_pattern' => 'superadmin.security.logs.*', 'grup' => 'Security', 'urutan' => 50, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['key' => 'security-blocked-ips', 'label' => 'Blocked IPs', 'route_name' => 'superadmin.security.blocked-ips.index', 'route_pattern' => 'superadmin.security.blocked-ips.*', 'grup' => 'Security', 'urutan' => 51, 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ];
    }

    public function run(): void
    {
        // Superadmin roles/permissions live in the null team context.
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        foreach ($this->modules() as $module) {
            Module::updateOrCreate(
                ['key' => $module['key']],
                [
                    'label' => $module['label'],
                    'route_name' => $module['route_name'],
                    'route_pattern' => $module['route_pattern'],
                    'icon' => $module['icon'],
                    'grup' => $module['grup'],
                    'urutan' => $module['urutan'],
                    'is_active' => true,
                ]
            );

            foreach (self::ACTIONS as $action) {
                $permission = Permission::firstOrCreate([
                    'name' => "{$module['key']}.{$action}",
                    'guard_name' => 'web',
                ]);

                $superAdminRole->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
