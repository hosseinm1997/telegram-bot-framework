<?php

namespace Robot\Core\Database;

class BaseModel extends DB
{
    protected static $table;
    private static $connection;
    protected static $select = 'select ';
    protected static $where = '';
    protected static $query;

    function __construct(...$args)
    {
        self::$connection = parent::getInstance();

    }

    private static function generateWhere($field,$values,$operator = 'or')
    {
        $where_clause = '';
        $first = true;
        foreach ($values as $value)
        {
            if(!$first)
                $where_clause .= ' ' . $operator;
            else
                $first = false;

            $where_clause .= $field . '=' . $value;
        }
    }

    public static function buildConnection()
    {

    }

    /**
     * @param array|int $id
     */
    public static function find($ids)
    {
        if(!is_array($ids))
            $ids = [$ids];

        self::$where = self::generateWhere('id',$ids);

        return new self();
    }

    public function get($columns)
    {

        $column_clause = implode(' , ',$columns);
        $this->query = $this->select . $column_clause . ' from ' . static::$table;
        $builder = self::$connection->prepare($this->query);
        $builder->execute();
        return $builder->fetchAll(PDO::FETCH_ASSOC);

    }
}