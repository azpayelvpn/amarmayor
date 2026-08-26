<?php

declare(strict_types=1);

namespace AmarMayor\Auth;

class UserScope
{
    public int $id;
    public int $userId;
    public string $scopeType; // citywide, zone, ward, department, team, individual
    public ?int $scopeId;
    public string $effectiveFrom;
    public ?string $effectiveTo;

    public function __construct(array $attributes)
    {
        $this->id = (int)($attributes['id'] ?? 0);
        $this->userId = (int)($attributes['user_id'] ?? 0);
        $this->scopeType = (string)($attributes['scope_type'] ?? 'citywide');
        $this->scopeId = isset($attributes['scope_id']) ? (int)$attributes['scope_id'] : null;
        $this->effectiveFrom = (string)($attributes['effective_from'] ?? date('Y-m-d H:i:s'));
        $this->effectiveTo = $attributes['effective_to'] ?? null;
    }

    public function isCitywide(): bool
    {
        return $this->scopeType === 'citywide';
    }

    public function isZone(int $zoneId): bool
    {
        return $this->scopeType === 'zone' && $this->scopeId === $zoneId;
    }

    public function isWard(int $wardId): bool
    {
        return $this->scopeType === 'ward' && $this->scopeId === $wardId;
    }

    public function isDepartment(int $departmentId): bool
    {
        return $this->scopeType === 'department' && $this->scopeId === $departmentId;
    }

    public function isTeam(int $teamId): bool
    {
        return $this->scopeType === 'team' && $this->scopeId === $teamId;
    }
}
