<?php

namespace Koine\Repository\Storage\Pdo;

use Koine\Repository\Exception\RecordNotFoundException;
use Koine\Repository\Storage\StorageInterface;
use PDO;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $idColumn;

    /**
     * @param PDO    $connection
     * @param string $tableName
     * @param string $idColumn
     */
    public function __construct(PDO $connection, $tableName, $idColumn = 'id')
    {
        $this->setConnection($connection);
        $this->setTableName($tableName);
        $this->setIdColumn($idColumn);
    }

    /**
     * @param PDO $connection
     *
     * @return self
     */
    public function setConnection(PDO $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $tableName
     *
     * @return self
     */
    public function setTableName($tableName)
    {
        $this->tableName = (string) $tableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $idColumn
     *
     * @return self
     */
    public function setIdColumn($idColumn)
    {
        $this->idColumn = (string) $idColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdColumn()
    {
        return $this->idColumn;
    }

    /**
     * @return int
     */
    public function getNumberOfRows()
    {
        $tableName = $this->getTableName();
        $records = $this->selectQuery("SELECT count(*) as count FROM $tableName");

        return (int) $records[0]['count'];
    }

    /**
     * @param string $sql
     * @param array  $params
     *
     * @return array
     */
    private function selectQuery($sql, array $params = array())
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $values
     */
    public function insert(array $values)
    {
        $sql = 'INSERT INTO %s (%s) VALUES (%s)';
        $columnNames = array_keys($values);
        $columns = implode(', ', $columnNames);
        $placeholders = implode(', ', $this->columnsToPlaceholders($columnNames));
        $sql = sprintf(
            $sql,
            $this->getTableName(),
            $columns,
            $placeholders
        );
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($values);

        return $this->getConnection()->lastInsertId();
    }

    /**
     * @param array  $columns
     * @param string $placeholderPrefix
     *
     * @return array
     */
    private function columnsToPlaceholders(array $columns, $placeholderPrefix = '')
    {
        $placeholders = array();

        foreach ($columns as $column) {
            $placeholders[$column] = ":$placeholderPrefix$column";
        }

        return $placeholders;
    }

    public function find($id)
    {
        $resultSet = $this->findAllBy(array(
            $this->getIdColumn() => $id,
        ));

        if (count($resultSet)) {
            return $resultSet[0];
        }

        throw new RecordNotFoundException(
            sprintf(
                '%s record not found by %s %s',
                $this->getTableName(),
                $this->getIdColumn(),
                $id
            )
        );
    }

    public function findAllBy(array $conditions = array(), $limit = null, $offset = 0)
    {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());

        if ($conditions) {
            $whereConditions = $this->assembleEquality($conditions);
            $where = sprintf('WHERE %s', implode(' AND ', $whereConditions));
            $sql .= " $where";
        }

        if ($limit) {
            $sql .= sprintf(' LIMIT %s, %s', (int) $offset, (int) $limit);
        }

        return $this->selectQuery($sql, $conditions);
    }

    private function assembleEquality(array $conditions, $placeholderPrefix = '')
    {
        $placeholders = $this->columnsToPlaceholders(array_keys($conditions), $placeholderPrefix);
        $equalities = array();

        foreach ($placeholders as $column => $placeholder) {
            $equalities[] = "$column = $placeholder";
        }

        return $equalities;
    }

    /**
     * @param array $conditions
     * @param array $values
     */
    public function updateWhere(array $conditions, array $values)
    {
        $conditionsParams = array();

        foreach ($conditions as $column => $value) {
            $conditionsParams["_$column"] = $value;
        }

        $conditions = $this->assembleEquality($conditions, '_');
        $conditionsString = implode(' AND ', $conditions);

        $modifications = $this->assembleEquality($values);
        $modificationsString = implode(', ', $modifications);

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->getTableName(),
            $modificationsString,
            $conditionsString
        );

        $params = array_merge($conditionsParams, $values);
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
    }

    public function findOneBy(array $conditions)
    {
        $result = $this->findAllBy($conditions, 1);

        if ($result) {
            return $result[0];
        }

        throw new RecordNotFoundException('Record not found');
    }

    public function exists(array $conditions)
    {
        $resultSet = $this->findAllBy($conditions, 1);
        return count($resultSet) !== 0;
    }

    /**
     * @param array $conditions
     */
    public function deleteWhere(array $conditions)
    {
        $equalities = $this->assembleEquality($conditions);
        $conditionsString = implode(' AND ', $equalities);

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->getTableName(),
            $conditionsString
        );

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($conditions);
    }
}
