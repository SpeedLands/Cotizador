<?php

namespace App\Services;

class AuthService
{
    protected $userId = null;

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function id(): ?int
    {
        return $this->userId;
    }
}