<?php

namespace App\Services\Access;

use App\Models\Member;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Support\AdminScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminUserService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected AccessNotificationService $accessNotificationService
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['member', 'kanda', 'jumuiya', 'roles'])
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhereHas('member', function ($memberQuery) use ($term) {
                        $memberQuery
                            ->where('first_name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('member_code', 'like', "%{$term}%");
                    });
            });
        }

        if (! empty($filters['role_id'])) {
            $roleId = (int) $filters['role_id'];

            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $roleId));
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getEligibleMembers(?int $kandaId = null, ?int $jumuiyaId = null, ?string $search = null): Collection
    {
        $query = Member::query()
            ->with(['familia.jumuiya.kanda'])
            ->whereDoesntHave('user')
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($jumuiyaId) {
            $query->whereHas('familia', fn ($familiaQuery) => $familiaQuery->where('jumuiya_id', $jumuiyaId));
        } elseif ($kandaId) {
            $query->whereHas('familia.jumuiya', fn ($jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', $kandaId));
        }

        if (filled($search)) {
            $term = trim($search);

            $query->where(function ($memberQuery) use ($term) {
                $memberQuery
                    ->where('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('member_code', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        return $query->limit(100)->get();
    }

    public function create(array $data, User $actor): array
    {
        return DB::transaction(function () use ($data, $actor) {
            $member = Member::query()
                ->with(['familia.jumuiya'])
                ->findOrFail((int) $data['member_id']);

            $plainPassword = $data['password_mode'] === 'manual'
                ? (string) $data['password']
                : $this->generatePassword();

            $scope = $this->resolveScopeColumns($data);

            $user = User::query()->create([
                'name' => $this->buildMemberName($member),
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'locale' => $data['locale'],
                'member_id' => $member->id,
                'kanda_id' => $scope['kanda_id'],
                'jumuiya_id' => $scope['jumuiya_id'],
                'phone' => $member->phone,
                'is_active' => (bool) $data['is_active'],
            ]);

            $roleIds = collect($data['role_ids'])->map(fn ($id) => (int) $id)->all();
            $roles = Role::query()->whereIn('id', $roleIds)->pluck('name')->all();

            $user->syncRoles($roles);

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'created',
                'module' => 'access',
                'action' => 'Admin user created',
                'subject' => $user,
                'subject_label' => $user->name,
                'description' => 'Admin user account created from member record',
                'new_values' => [
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'member_id' => $user->member_id,
                    'scope_type' => $data['scope_type'] ?? AdminScope::TYPE_GLOBAL,
                    'kanda_id' => $user->kanda_id,
                    'jumuiya_id' => $user->jumuiya_id,
                    'is_active' => $user->is_active,
                    'roles' => $roles,
                ],
                'risk_level' => 'medium',
            ]);

            if ((bool) ($data['send_sms'] ?? false)) {
                $templateLocale = $data['sms_template_locale'] ?? $user->locale ?? 'sw';
                $message = $this->accessNotificationService->renderCredentialsMessage(
                    $user,
                    $plainPassword,
                    implode(', ', $roles),
                    $templateLocale
                );

                if ($message) {
                    $this->accessNotificationService->sendCredentialsSms($user, $message, $actor);
                }
            }

            return [
                'user' => $user->load(['roles', 'member', 'kanda', 'jumuiya']),
                'plain_password' => $plainPassword,
            ];
        });
    }

    public function update(User $user, array $data, User $actor): array
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $oldValues = [
                'email' => $user->email,
                'locale' => $user->locale,
                'is_active' => $user->is_active,
                'kanda_id' => $user->kanda_id,
                'jumuiya_id' => $user->jumuiya_id,
                'scope_type' => $this->detectScopeType($user),
                'roles' => $user->roles->pluck('name')->all(),
            ];

            $scope = $this->resolveScopeColumns($data);

            $payload = [
                'email' => $data['email'],
                'locale' => $data['locale'],
                'is_active' => (bool) $data['is_active'],
                'kanda_id' => $scope['kanda_id'],
                'jumuiya_id' => $scope['jumuiya_id'],
            ];

            $plainPassword = null;
            $passwordMode = $data['password_mode'] ?? 'keep';

            if ($passwordMode === 'manual') {
                $plainPassword = (string) $data['password'];
                $payload['password'] = Hash::make($plainPassword);
            }

            if ($passwordMode === 'generated') {
                $plainPassword = $this->generatePassword();
                $payload['password'] = Hash::make($plainPassword);
            }

            $user->update($payload);

            $roleIds = collect($data['role_ids'])->map(fn ($id) => (int) $id)->all();
            $roles = Role::query()->whereIn('id', $roleIds)->pluck('name')->all();
            $user->syncRoles($roles);

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'updated',
                'module' => 'access',
                'action' => 'Admin user updated',
                'subject' => $user,
                'subject_label' => $user->name,
                'description' => 'Admin user account updated',
                'old_values' => $oldValues,
                'new_values' => [
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'is_active' => $user->is_active,
                    'scope_type' => $data['scope_type'] ?? $this->detectScopeType($user),
                    'kanda_id' => $user->kanda_id,
                    'jumuiya_id' => $user->jumuiya_id,
                    'roles' => $roles,
                ],
                'risk_level' => 'medium',
            ]);

            if ((bool) ($data['send_sms'] ?? false) && $plainPassword) {
                $templateLocale = $data['sms_template_locale'] ?? $user->locale ?? 'sw';
                $message = $this->accessNotificationService->renderCredentialsMessage(
                    $user,
                    $plainPassword,
                    implode(', ', $roles),
                    $templateLocale
                );

                if ($message) {
                    $this->accessNotificationService->sendCredentialsSms($user, $message, $actor);
                }
            }

            return [
                'user' => $user->fresh(['roles', 'member', 'kanda', 'jumuiya']),
                'plain_password' => $plainPassword,
            ];
        });
    }

    public function activate(User $user, User $actor): void
    {
        $user->update(['is_active' => true]);

        $this->auditLogService->log([
            'user' => $actor,
            'event' => 'updated',
            'module' => 'access',
            'action' => 'Admin user activated',
            'subject' => $user,
            'subject_label' => $user->name,
            'description' => 'Admin user account activated',
            'new_values' => ['is_active' => true],
            'risk_level' => 'medium',
        ]);
    }

    public function deactivate(User $user, User $actor): void
    {
        $user->update(['is_active' => false]);

        $this->auditLogService->log([
            'user' => $actor,
            'event' => 'updated',
            'module' => 'access',
            'action' => 'Admin user deactivated',
            'subject' => $user,
            'subject_label' => $user->name,
            'description' => 'Admin user account deactivated',
            'new_values' => ['is_active' => false],
            'risk_level' => 'high',
        ]);
    }

    public function delete(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $name = $user->name;
            $user->syncRoles([]);
            $user->delete();

            $this->auditLogService->log([
                'user' => $actor,
                'event' => 'deleted',
                'module' => 'access',
                'action' => 'Admin user deleted',
                'subject_type' => User::class,
                'subject_id' => null,
                'subject_label' => $name,
                'description' => 'Admin user account deleted',
                'risk_level' => 'high',
            ]);
        });
    }

    protected function buildMemberName(Member $member): string
    {
        return trim(collect([
            $member->first_name,
            $member->middle_name,
            $member->last_name,
        ])->filter()->implode(' '));
    }

    protected function generatePassword(): string
    {
        return 'CH-' . strtoupper(Str::random(8));
    }

    protected function resolveScopeColumns(array $data): array
    {
        $scopeType = $data['scope_type'] ?? AdminScope::TYPE_GLOBAL;

        return match ($scopeType) {
            AdminScope::TYPE_KANDA => [
                'kanda_id' => ! empty($data['kanda_id']) ? (int) $data['kanda_id'] : null,
                'jumuiya_id' => null,
            ],
            AdminScope::TYPE_JUMUIYA => [
                'kanda_id' => ! empty($data['kanda_id']) ? (int) $data['kanda_id'] : null,
                'jumuiya_id' => ! empty($data['jumuiya_id']) ? (int) $data['jumuiya_id'] : null,
            ],
            default => [
                'kanda_id' => null,
                'jumuiya_id' => null,
            ],
        };
    }

    protected function detectScopeType(User $user): string
    {
        if ($user->jumuiya_id) {
            return AdminScope::TYPE_JUMUIYA;
        }

        if ($user->kanda_id) {
            return AdminScope::TYPE_KANDA;
        }

        return AdminScope::TYPE_GLOBAL;
    }
}