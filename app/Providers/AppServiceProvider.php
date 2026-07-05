<?php

namespace App\Providers;

use App\Enums\TeamPermission;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureTeamPermissions();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * 前端团队行级权限：当 Gate 鉴权的模型是 Team 且 ability 是 team 相关操作时，
     * 先检查用户在当前 team 的行级权限。返回 null 则继续走 Shield TeamPolicy。
     *
     * 与 Admin Panel (Shield) 完全解耦 —— Gate::before 回调只对 Team 模型生效，
     * 非 Team 模型在 instanceof 处直接退出，零开销。
     */
    protected function configureTeamPermissions(): void
    {
        Gate::before(function (?User $user, string $ability, array $arguments) {
            $model = $arguments[0] ?? null;

            if (! $model instanceof Team) {
                return null; // 非 Team，交给其他 Policy
            }

            // 个人 team 不允许删除
            if ($ability === 'delete' && $model->is_personal) {
                return false;
            }

            $permission = $this->mapTeamAbility($ability);

            if ($permission === null) {
                return null; // 非 team 相关 ability，交给 Shield
            }

            return $user->hasTeamPermission($model, $permission) ?: null;
        });
    }

    private function mapTeamAbility(string $ability): ?TeamPermission
    {
        return match ($ability) {
            'update' => TeamPermission::UpdateTeam,
            'delete' => TeamPermission::DeleteTeam,
            'updateMember' => TeamPermission::UpdateMember,
            'removeMember' => TeamPermission::RemoveMember,
            'inviteMember' => TeamPermission::CreateInvitation,
            'cancelInvitation' => TeamPermission::CancelInvitation,
            default => null,
        };
    }
}
