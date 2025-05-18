<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 */
class Role extends Model
{
    protected static string $table = 'Roles';
    protected static array $columns = ['name', 'description'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
    }


    public function employees()
    {
        return $this->hasMany(Employee::class, 'role_id');
    }


    public static function findByName(string $name): ?Role
    {
        return self::findBy(['name' => $name]);
    }
}
