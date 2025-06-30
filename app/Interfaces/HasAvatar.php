<?php

namespace App\Interfaces;

/**
 * Interface for models that can have an avatar
 */
interface HasAvatar
{
    /**
     * Get the avatar name
     *
     * @return string|null
     */
    public function getAvatarName(): ?string;

    /**
     * Set the avatar name
     *
     * @param string|null $avatarName
     * @return bool
     */
    public function setAvatarName(?string $avatarName): bool;
}
