<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $project_id
 * @property string $type
 * @property string $status
 * @property string $request_date
 * @property string $approval_date
 */
class Approval extends Model
{
    protected static string $table = 'Approvals';
    protected static array $columns = ['employee_id', 'project_id', 'type', 'status', 'request_date', 'approval_date'];

    public function validates(): void
    {
        Validations::notEmpty('employee_id', $this);
        Validations::notEmpty('type', $this);
    }


    /**
     * @return Employee|null
     */
    public function employee(): ?Employee
    {
        $result = $this->belongsTo(Employee::class, 'employee_id')->get();
        return $result instanceof Employee ? $result : null;
    }

    /**
     * @return Project|null
     */
    public function project(): ?Project
    {
        if ($this->project_id === null) {
            return null;
        }

        $result = $this->belongsTo(Project::class, 'project_id')->get();
        return $result instanceof Project ? $result : null;
    }


    public function approve(): bool
    {
        $this->status = 'Approved';
        $this->approval_date = date('Y-m-d H:i:s');
        return $this->save();
    }


    public function reject(): bool
    {
        $this->status = 'Rejected';
        $this->approval_date = date('Y-m-d H:i:s');
        return $this->save();
    }
}
