<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property float $budget
 */
class Project extends Model
{
    protected static string $table = 'Projects';
    protected static array $columns = ['name', 'description', 'start_date', 'end_date', 'status', 'budget'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
    }

    public function employees(): BelongsToMany
    {
        return $this->BelongsToMany(
            Employee::class,
            'Employee_Projects',
            'project_id',
            'employee_id'
        );
    }
}
