<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $type
 * @property string $message
 * @property string $sent_date
 * @property string $status
 */
class Notification extends Model
{
    protected static string $table = 'Notifications';
    protected static array $columns = ['employee_id', 'type', 'message', 'sent_date', 'status'];

    public function validates(): void
    {
        Validations::notEmpty('employee_id', $this);
        Validations::notEmpty('type', $this);
        Validations::notEmpty('message', $this);
    }

    /**
     * Obtém o funcionário associado a esta notificação
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->get();
    }

    /**
     * Marca a notificação como lida
     */
    public function markAsRead(): bool
    {
        $this->status = 'Read';
        return $this->save();
    }
}
