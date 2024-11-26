<?php

namespace Lib\Authentication;

use App\Models\User;

class Auth
{
  public static function check(): bool
  {
    return isset($_SESSION['user']['id']) && self::user() !== null;
  }

  public static function login(User $user): void
  {
    $_SESSION['user_id'] = $user->id;
  }

  public static function logout(): void
  {
    unset($_SESSION['user_id']);
  }

  public static function user(): ?User
  {
    if (self::check()) {
      return User::findById($_SESSION['user_id']);
    }
    return null;
  }
}
