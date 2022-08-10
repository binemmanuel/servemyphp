<?php

namespace Binemmanuel\ServeMyPhp;

use mysqli;
use mysqli_stmt;
use PDO;
use PDOStatement;


enum Rule
{
    case REQUIRED;
    case UNIQUE;
    case MATCH;
    case MAX_LENGTH;
    case MIN_LENGTH;
    case EMAIL;
    case NUMBER;
}

abstract class BaseModel
{
    public static mysqli|PDO $db;
    protected array $errors;
    private static array $rules;
    protected static String $table;

    public function __construct(
        mysqli|PDO $db,
        public String $id = '',
        public String $date = '',
    ) {
        self::$db = $db;

        // Set ID
        $this->id = empty($id)
            ? (new \Ramsey\Uuid\UuidFactory)->uuid4()
            : self::sanitize($id);

        $this->errors = [];

        // Set the DB Table
        self::$table = $this->__setTable();
    }

    abstract protected function __setTable(): String;
    abstract protected function rules(): array;
    abstract public function makeRules(array $rules): void;

    public function find(array $keyValues): array
    {
        $table = $this::$table;
        $column = $this::getColumns($keyValues);
        $params = $this::getParams($keyValues);
        $paramTypes = $this::getParamTypes($keyValues);
        $placeholders = $this::getPlaceholders($keyValues);

        $stmt = $this->prepare(
            "SELECT
                *
            FROM
                $table
            WHERE
                $column = $placeholders"
        );

        $stmt->bind_param($paramTypes, ...$params);

        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $value) {
                $data[$paramKeys[$i]] = $value;
                $i++;
            }
        }

        $stmt->close();

        return $this::filterOut($data ?? [], ['password', 'OTPToken']) ?? [];
    }

    public function findAll(array $keyValues, String $orderBy = 'id'): array
    {
        $table = $this::$table;
        $column = $this::getColumns($keyValues);
        $params = $this::getParams($keyValues);
        $paramTypes = $this::getParamTypes($keyValues);
        $placeholders = $this::getPlaceholders($keyValues);

        $stmt = $this->prepare(
            "SELECT
                *
            FROM
                $table
            WHERE
                $column = $placeholders
            ORDER BY
                $orderBy DESC"
        );

        $stmt->bind_param($paramTypes, ...$params);

        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);


        $all = [];

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $value) {
                $data[$paramKeys[$i]] = $value;
                $i++;
            }

            array_push($all, $data);
        }

        $stmt->close();

        return $this::filterOut($all ?? [], ['password', 'OTPToken']) ?? [];
    }

    public function findLike(array $keyValues): array
    {
        $table = $this::$table;
        $column = $this::getColumns($keyValues);
        $params = $this::getParamsLike($keyValues);
        $paramTypes = $this::getParamTypes($keyValues);
        $placeholders = $this::getPlaceholders($keyValues);

        $stmt = $this->prepare(
            "SELECT
                *
            FROM
                $table
            WHERE
                $column LIKE $placeholders"
        );

        $stmt->bind_param($paramTypes, ...$params);

        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $value) {
                $data[$paramKeys[$i]] = $value;
                $i++;
            }
        }

        $stmt->close();

        return $this::filterOut($data ?? [], ['password', 'OTPToken']) ?? [];
    }

    public function fetchAll(
        String $orderBy = 'id',
    ): array {
        $table = $this::$table;

        $stmt = $this->prepare(
            "SELECT
                *
            FROM
                $table
            ORDER BY
                $orderBy DESC"
        );

        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $result) {
                $singleData[$paramKeys[$i]] = $result;
                $i++;
            }
            $data[] = $this::filterOut($singleData, ['password', 'OTPToken']);
        }

        $stmt->close();

        return $data ?? [];
    }

    public function update(array $where): array
    {
        $table = self::$table;
        $props = $this->getProps();
        $columnsWithPlaceholders = $this->getColumns($props, withPlaceholders: true);
        $condition = $this::getColumns($where, withPlaceholders: true);
        $conditionArray = $this::getParams($where);
        $params = $this::getParams(array_merge($props, $conditionArray));
        $paramTypes = $this::getParamTypes(array_merge($props, $conditionArray));

        $stmt = $this->prepare(
            "UPDATE
                $table
            SET
                $columnsWithPlaceholders
            WHERE $condition"
        );

        $stmt->bind_param($paramTypes, ...$params);
        $stmt->execute();
        $madeUpdate = (bool) $stmt->affected_rows;
        $stmt->close();

        return $madeUpdate ? $this->find($where) : [];
    }

    /**
     * Save data in the database
     */
    public function save(): array
    {
        $table = $this::$table;
        $props = $this->getProps();
        $columns = $this::getColumns($props);
        $placeholders = $this::getPlaceholders($props);
        $params = $this::getParams($props);
        $paramTypes = $this::getParamTypes($props);

        $stmt = $this->prepare(
            "INSERT INTO 
                $table(
                    $columns
                )
            VALUES(
                $placeholders
            )"
        );

        $stmt->bind_param($paramTypes, ...$params);

        $stmt->execute();

        $userId = $stmt->insert_id;

        $stmt->close();

        return $this->find(['id' => $userId]);
    }

    public function verify(): array
    {
        $table = self::$table;
        $props = $this->getProps();
        [$idColumn, $passwordColumn] = $this::getColumns($props, returnArray: true);
        [$id, $password] = $this::getParams($props);

        $stmt = $this->prepare(
            "SELECT
               *
            FROM
                $table
            WHERE
                $idColumn = ?"
        );

        $stmt->bind_param('s', $id);
        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);

        $data = [];

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $result) {
                $data[$paramKeys[$i]] = $result;
                $i++;
            }
        }

        return (password_verify($this->password, $data['password'] ?? ''))
            ? $this->filterOut($data, ['password', 'OTPToken'])
            : [];
    }

    public function authenticate(array ...$data): array
    {
        $table = self::$table;
        $props = $this->getProps();
        [$idColumn, $passwordColumn] = $this::getColumns($data, returnArray: true);
        [$id, $password] = $this::getParams($data);

        echo  print_r($id, true);

        return [];

        $stmt = $this->prepare(
            "SELECT
               *
            FROM
                $table
            WHERE
                $idColumn = ?"
        );

        $stmt->bind_param('s', $id);
        $stmt->execute();

        [$paramKeys, $resultPrams] = Database::sqlResult($stmt);

        $stmt->bind_result(...$resultPrams);

        $data = [];

        while ($stmt->fetch()) {
            $i = 0;
            foreach ($resultPrams as $result) {
                $data[$paramKeys[$i]] = $result;
                $i++;
            }
        }

        return (password_verify($this->password, $data['password'] ?? ''))
            ? $this->filterOut($data, ['password', 'OTPToken'])
            : [];
    }

    /**
     * Delete data
     */
    public function delete(array $where): bool
    {
        $table = self::$table;
        $column = $this::getColumns($where);
        $placeholders = $this::getPlaceholders($where);
        $params = $this::getParams($where);
        $paramTypes = $this::getParamTypes($where);

        $stmt = $this->prepare(
            "DELETE FROM
                $table
            WHERE
                $column = $placeholders"
        );

        $stmt->bind_param($paramTypes, ...$params);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Check if a field exists
     */
    private function fieldExists(
        String $field,
        String $table,
    ): bool {
        $stmt = self::$db->prepare(
            "SELECT
                {$field}
            FROM
                {$table}
            WHERE
                {$field} = ?"
        );

        $stmt->bind_param('s', $this->$field);
        $stmt->execute();
        $stmt->bind_result($data);
        $stmt->fetch();
        $stmt->close();

        return !empty($data);
    }

    public static function filterOut(array $data, array $filters): array
    {
        foreach ($data as $key => $value) {
            foreach ($filters as $filter) {
                if ($filter === $key)
                    unset($data[$key]);
            }
        }

        return $data ?? [];
    }

    /**
     * Prepare an SQL Statement
     */
    public function prepare(String $stmt): PDOStatement|mysqli_stmt|false
    {
        return self::$db->prepare($stmt);
    }

    /**
     * Check if there are errors
     *
     * @return bool false | true if there are error
     */
    public function hasError(): bool
    {
        foreach ($this->rules() as $attr => $rules) {
            $value = $this->$attr ?? '';

            foreach ($rules as $rule) {
                $ruleName = $rule;

                if (is_array($rule)) {
                    $ruleName = $rule[0];
                }

                switch ($ruleName) {
                    case Rule::REQUIRED:
                        empty($value) ?
                            $this->addError($attr, Rule::REQUIRED) : null;
                        break;

                    case Rule::EMAIL:
                        (!filter_var($value, FILTER_VALIDATE_EMAIL)) ?
                            $this->addError($attr, Rule::EMAIL) : null;
                        break;

                    case Rule::MATCH:
                        ($value !== $this->{$rule[1]}) ?
                            $this->addError($attr, Rule::MATCH, $rule) : null;
                        break;

                    case Rule::MAX_LENGTH:
                        (strlen($value) > $rule[1]) ?
                            $this->addError($attr, Rule::MAX_LENGTH, $rule) : null;
                        break;

                    case Rule::MIN_LENGTH:
                        (strlen($value) < $rule[1]) ?
                            $this->addError($attr, Rule::MIN_LENGTH, $rule) : null;
                        break;

                    case Rule::NUMBER:
                        (!is_numeric($value)) ?
                            $this->addError($attr, Rule::NUMBER) : null;
                        break;

                    case Rule::UNIQUE:
                        $table = $rule['class']::$table;

                        $this->fieldExists($attr, $table) ?
                            $this->addError(
                                $attr,
                                Rule::UNIQUE,
                                ['field', $this->$attr]
                            ) : null;

                        break;
                }
            }
        }

        return !empty($this->errors);
    }

    protected function getProps(): array
    {
        foreach (get_object_vars($this) as $key => $value) {
            if (
                $key === 'id' || $key === 'errors' || $key === 'date'
            ) continue;

            if ($key === 'password')
                $value = password_hash($value, PASSWORD_DEFAULT);

            $data[$key] = $value;
        }

        if (empty($data)) {
            $class = $this::class;
            throw new \Exception("Trying to update an object ({$class}) without setting it's properties", 1);
        }

        return $data ?? [];
    }

    private function getPropNames(): array
    {
        foreach (get_object_vars($this) as $key => $value)
            $names[] = $key;


        return $names ?? [];
    }

    protected static function getColumns(
        array $data,
        bool $returnArray = false,
        bool $withPlaceholders = false,
    ): String|array {
        foreach ($data as $key => $value) {
            if ($withPlaceholders)
                $keys[] = "$key = ?";
            else
                $keys[] = $key;
        }

        if ($returnArray)
            return $keys ?? [];

        return implode(', ', $keys ?? []) ?? '';
    }

    protected static function getParams(array $data): array
    {
        foreach ($data as $key => $value)
            $values[] = $value;

        return $values ?? [];
    }

    protected static function getParamsLike(array $data): array
    {
        foreach ($data as $key => $value)
            $values[] = "%$value%";

        return $values ?? [];
    }

    protected static function getPlaceholders(array $data): string
    {
        for ($i = 0; $i < count($data); $i++)
            $placeholders[] = '?';

        if (empty($placeholders)) return '';

        return implode(', ', $placeholders) ?? '';
    }

    protected static function getParamTypes(array $data): String
    {
        for ($i = 0; $i < count($data); $i++)
            $types[] = 's';

        if (empty($types)) return '';

        return  implode('', $types) ?? '';
    }

    /**
     * Get a list of errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Add to the list of arrays if there is an error
     */
    private function addError(
        String $attribute,
        Rule $rule,
        ...$params,
    ): void {
        $message = $this->errorMessages()[$rule->name] ?? '';

        foreach ($params as $param) {
            [$key, $value] = $param;

            if ($key === 'field') {
                $key = strtolower($key);

                $message = str_replace("{{$key}}", $value, $message);
            } else {
                $message = str_replace("{{$key->name}}", $value, $message);
            }
        }

        $this->errors[$attribute]['error'] = true;
        $this->errors[$attribute]['type'] = $attribute;
        $this->errors[$attribute]['message'] = $message;
    }

    /**
     * A list of posible error messages
     */
    private function errorMessages(): array
    {
        return [
            Rule::REQUIRED->name => 'This field is required',
            Rule::EMAIL->name => 'You need to enter a valid email address',
            Rule::MATCH->name => 'This field must be the same as {match}',
            Rule::MAX_LENGTH->name => 'You can\'t enter more than {max} characters',
            Rule::MIN_LENGTH->name => 'You need to enter atleast {min} or more characters',
            Rule::UNIQUE->name => '{field} is already taken',
            Rule::NUMBER->name => 'You need to enter a valid number',
        ];
    }

    /**
     * Loads the data to the model
     */
    public function loadData(array $data): self
    {

        foreach ($data as $key => $value) {
            if (!isset($value)) continue;

            $this->$key = is_string($value) ? trim($value) : $value;
        }

        return $this;
    }

    private static function sanitize(String|array $data): String|array
    {
        if (is_iterable($data)) {
            $tempData = [];

            foreach ($data as $key => $value) {
                if (is_iterable($value)) {
                    foreach ($value as $v) {
                        $tempData[$key] = htmlspecialchars(stripslashes(trim($v)));
                    }
                } else {
                    $tempData[$key] = htmlspecialchars(stripslashes(trim($value)));
                }
            }

            return $tempData;
        }

        return htmlspecialchars(stripslashes(trim($data)));
    }

    private static function unsanitize(String|array $data): String|array
    {
        if (is_iterable($data)) {
            $tempData = [];

            foreach ($data as $key => $value) {
                $tempData[$key] = htmlspecialchars_decode(trim($value));
            }

            return $tempData;
        }

        return htmlspecialchars_decode(trim($data));
    }

    public static function makeId(): String
    {
        return (string) bin2hex(random_bytes(3));
    }

    public static function isPhoneNumber(String $value)
    {
        return (preg_match('/^(234)(81|80|90|91|70|71)[0-9]{8}/s', $value) ||
            preg_match('/(^(081|080|090|091|070|071))[0-9]{8}/s', $value) ||
            preg_match('/(^(81|80|90|91|70|71))[0-9]{8}/s', $value)
        );
    }
}
