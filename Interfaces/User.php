<?php namespace Interfaces;

interface User
{
    public function isAdmin(): bool;
    public function isActive(): bool;
    public function isGuest(): bool;
}
