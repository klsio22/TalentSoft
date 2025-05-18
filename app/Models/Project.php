<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 */
class Project extends Model
{
    protected static string $table = 'Projects';
    protected static array $columns = ['name', 'description', 'start_date', 'end_date', 'status'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
    }

    public function employees()
    {
        return $this->BelongsToMany(
            Employee::class,
            'Employee_Projects',
            'project_id',
            'employee_id'
        );
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'project_id');
    }
}
