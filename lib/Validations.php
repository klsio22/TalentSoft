<?php

namespace Lib;

use Core\Database\Database;

class Validations
{
    public static function notEmpty($attribute, $obj)
    {
        if ($obj->$attribute === null || $obj->$attribute === '') {
            $obj->addError($attribute, 'não pode ser vazio!');
            return false;
        }

        return true;
    }

    public static function passwordConfirmation($obj)
    {
        // Usar password_confirmation se disponível, caso contrário usar passwordConfirmation
        $confirmationValue = property_exists($obj, 'password_confirmation') && $obj->password_confirmation !== null ?
            $obj->password_confirmation :
            ($obj->passwordConfirmation ?? null);

        if ($obj->password !== $confirmationValue) {
            $obj->addError('password', 'as senhas devem ser idênticas!');
            return false;
        }

        return true;
    }

    public static function uniqueness($fields, $object)
    {
        $dbFieldsValues = [];
        $objFieldValues = [];

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if (!$object->newRecord()) {
            $dbObject = $object::findById($object->id);

            foreach ($fields as $field) {
                $dbFieldsValues[] = $dbObject->$field;
                $objFieldValues[] = $object->$field;
            }

            if (
                !empty($dbFieldsValues) &&
                !empty($objFieldValues) &&
                $dbFieldsValues === $objFieldValues
            ) {
                return true;
            }
        }

        $table = $object::table();
        $conditions = implode(' AND ', array_map(fn($field) => "{$field} = :{$field}", $fields));

        $sql = <<<SQL
            SELECT id FROM {$table} WHERE {$conditions};
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        foreach ($fields as $field) {
            $stmt->bindValue($field, $object->$field);
        }

        $stmt->execute();

        if ($stmt->rowCount() !== 0) {
            foreach ($fields as $field) {
                $object->addError($field, 'já existe um registro com esse dado');
            }
            return false;
        }

        return true;
    }
}
