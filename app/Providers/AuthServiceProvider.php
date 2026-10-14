<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $permissionGates = [
            'employee_setting' => 1,
            'allowance' => 21,
            'deduction' => 22,
            'salary_structure' => 23,
            'allowance_template' => 24,
            'deduction_template' => 25,
            'salary_template' => 26,
            'monthly_update' => 31,
            'group_update' => 32,
            'annual_increment' => 33,
            'loan_deduction' => 34,
            'salary_posting' => 35,
            'group_report' => 41,
            'individual_report' => 42,
            'nominal_roll' => 43,
            'annual_inc_history' => 44,
            'loan_dedc_history' => 45,
            'retired_staff' => 46,
            'backup_history' => 51,
            'restore_history' => 52,
            'audit_log' => 53,
            'analytic' => 54,
            'backup' => 61,
            'restore' => 62,
            'app_setting' => 63,
            'admin_user' => 64,
            'can_save' => 71,
            'can_edit' => 72,
            'can_export' => 73,
            'can_mail' => 74,
            'can_report' => 75,
            'can_promote' => 76,
            'repository' => 77,
            'terminated_list' => 78,
            'restore_point' => 79,
            'help' => 80,
        ];

        foreach ($permissionGates as $ability => $code) {
            Gate::define($ability, fn (User $user) => $this->userHasPermission($user, $code));
        }

        Gate::define('can_admin', function (User $user) {
            return $user->role != 1;
        });
    }

    private function userHasPermission(User $user, int $code): bool
    {
        if ($user->id == 1) {
            return true;
        }

        $permissions = $this->normalizePermissions($user->permission);

        return in_array((string) $code, $permissions, true) || in_array($code, $permissions, true);
    }

    private function normalizePermissions($rawPermission): array
    {
        if (is_array($rawPermission)) {
            return $rawPermission;
        }

        if (is_numeric($rawPermission) || is_int($rawPermission)) {
            return [];
        }

        if (!is_string($rawPermission) || trim($rawPermission) === '') {
            return [];
        }

        $decoded = json_decode($rawPermission, true);

        return is_array($decoded) ? $decoded : [];
    }
}
