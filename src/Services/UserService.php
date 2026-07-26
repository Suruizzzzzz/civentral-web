<?php

namespace App\Services;

class UserService
{
    private $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getCurrentUserDetails($userId, $employeeId = null)
    {
        return $this->userRepository->getUserWithRelations($userId, $employeeId);
    }
}
