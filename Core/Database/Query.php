<?php namespace Core\Database;

use Interfaces\Database\Query as QueryInterface;

use Services\StringService;
use Services\ArrayService;

class Query implements QueryInterface
{
    private bool   $isLastWhere = true;

    private string $queryString = _APP_EMPTY_STRING_;
    private string $type        = _APP_EMPTY_STRING_;

    private array  $containers  = [
        QueryInterface::TYPE_SELECT_FROM => [
            'table'  => [
                'name'  => '',
                'alias' => ''
            ],
            'fields' => [],
            'join'   => [],
            'where'  => [],
            'having' => [],
            'orderBy'=> [],
            'groupBy'=> [],
            'limit'  => 0,
            'offset' => 0
        ],
        
        QueryInterface::TYPE_UPDATE_TABLE => [
            'table'  => '',
            'set'    => [],
            'where'  => []
        ],

        QueryInterface::TYPE_DELETE_FROM => [
            'table'  => [
                'name'  => '',
                'alias' => ''
            ],
            'where'  => []
        ],

        QueryInterface::TYPE_INSERT_INTO => [
            'table'  => '',
            'fields' => [],
            'values' => []
        ],
    ];

    public function setType(string $type): void
    {
        if ( isset($this->containers[$type]) === false ) {
            throw new Exception("Type '$type' is not exists");
        }

        $this->type = $type;
    }

    public function select(array $fields = [ '*' ]): self
    {
        $this->setType(QueryInterface::TYPE_SELECT_FROM);

        $this->containers[$this->type]['fields'] = $fields;

        return $this;
    }

    public function from(string $name, string $alias = _APP_EMPTY_STRING_): self
    {
        $this->containers[$this->type]['table']['name'] = $name;
        $this->containers[$this->type]['table']['alias'] = $alias;

        return $this;
    }

    public function join(string $table, string $alias = '', string $type = 'left', string $on = ''): self
    {
        $this->containers[$this->type]['join'][] = [
            'table' => $table,
            'alias' => $alias,
            'type'  => $type,
            'on'    => $on
        ];

        return $this;
    }

    public function where(string $whereStatement): self
    {
        $this->isLastWhere = true;

        $this->containers[$this->type]['where'][] = $whereStatement;

        return $this;
    }

    public function having(string $havingStatement): self
    {
        $this->isLastWhere = false;

        $this->containers[$this->type]['having'][] = $havingStatement;

        return $this;
    }

    public function and(string $statement): self
    {
        if ( $this->isLastWhere ) {
            $this->where($statement);
        } else {
            $this->having($statement);
        }

        return $this;
    }

    public function or(string $statement): self
    {
        if ( $this->isLastWhere ) {
            $lastStatement = ArrayService::pop($this->containers[$this->type]['where']);
            $lastStatement .= " OR $statement";

            $this->where($lastStatement);
        } else {
            $lastStatement = ArrayService::pop($this->containers[$this->type]['having']);
            $lastStatement .= " OR $statement";

            $this->having($lastStatement);
        }

        return $this;
    }

    public function groupBy(array $fields): self
    {
        $this->containers[$this->type]['groupBy'] = $fields;

        return $this;
    }

    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->containers[$this->type]['orderBy'][] = "$field $direction";

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->containers[$this->type]['limit'] = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->containers[$this->type]['offset'] = $offset;

        return $this;
    }

    public function update(string $name): self
    {
        $this->setType(QueryInterface::TYPE_UPDATE_TABLE);

        $this->containers[$this->type]['table'] = $name;

        return $this;
    }

    public function set(string $field, string $value): self
    {
        $this->containers[$this->type]['set'][] = "$field='$value'";

        return $this;
    }

    public function delete(): self
    {
        $this->setType(QueryInterface::TYPE_DELETE_FROM);

        return $this;
    }

    public function insert(string $intoTable, array $fields = []): self
    {
        $this->setType(QueryInterface::TYPE_INSERT_INTO);

        $this->containers[$this->type]['table'] = $intoTable;
        $this->containers[$this->type]['fields'] = $fields;

        return $this;
    }
    /**
     * @param array $values = [ [ 1, 2, 3 ], [ 4, 5, 6 ] ]
     */
    public function values(array $values = []): self
    {
        foreach ( $values as &$tuple ) {
            foreach ( $tuple as &$value ) {
                $value = "'$value'";
            }
        }

        $this->containers[$this->type]['values'] = $values;

        return $this;
    }

    public function getString(): string
    {
        if ( StringService::isEmpty($this->queryString) ) {
            $this->buildString();
        }

        return $this->queryString;
    }

    private function buildString(): void
    {
        $query = _APP_EMPTY_STRING_;

        if ( StringService::isEmpty($this->type) === false ) {
            $container = $this->getContainer($this->type);

            switch ( $this->type ) {
                case QueryInterface::TYPE_SELECT_FROM:
                        $query .= $this->type . ' ' . implode(',', $container['fields']) . ' ';
                        $query .= 'FROM ' . $container['table']['name'] . ' ';
                        $query .= $container['table']['alias'] . ' ';

                        if ( ArrayService::isEmpty($container['join']) === false ) {
                            foreach ( $container['join'] as $join ) {
                                $query .= $join['type'] . ' JOIN ';
                                $query .= $join['table'] . ' ' . $join['alias'] . ' ';
                                $query .= StringService::isEmpty($join['on']) ? ' ' :  'ON ' . $join['on'] . ' ';
                            }
                        }

                        if ( ArrayService::isEmpty($container['where']) === false ) {
                            $query .= 'WHERE ' . implode(' AND ' , $container['where']) . ' ';
                        }

                        if ( ArrayService::isEmpty($container['having']) === false ) {
                            $query .= 'HAVING ' . implode(' AND ' , $container['having']) . ' ';
                        }

                        if ( ArrayService::isEmpty($container['groupBy']) === false ) {
                            $query .= 'GROUP BY ' . implode(',', $container['orderBy']) . ' ';
                        }

                        if ( ArrayService::isEmpty($container['orderBy']) === false ) {
                            $query .= 'ORDER BY ' . implode(',', $container['orderBy']) . ' ';
                        }

                        if ( $container['limit'] ) {
                            $query .= "LIMIT {$container['limit']} ";
                        }

                        if ( $container['offset'] ) {
                            $query .= "OFFSET {$container['offset']} ";
                        }

                    break;

                case QueryInterface::TYPE_UPDATE_TABLE:
                        $query .= $this->type . ' ' . $container['table'] . ' ';
                        $query .= 'SET ' . implode(',', $container['set']) . ' ';

                        if ( ArrayService::isEmpty($container['where']) === false ) {
                            $query .= 'WHERE ' . implode(' AND ', $container['where']) . ' ';
                        }

                    break;

                case QueryInterface::TYPE_DELETE_FROM:
                        $query .= $this->type . ' ' . $container['table']['name'] . ' ';
                        $query .= $container['table']['alias'] . ' ';

                        if ( ArrayService::isEmpty($container['where']) === false ) {
                            $query .= 'WHERE ' . implode(' AND ', $container['where']);
                        }

                    break;

                case QueryInterface::TYPE_INSERT_INTO:
                        $query .= $this->type . ' ' . $container['table'] . ' ';
                        $query .= '(' . implode(',', $container['fields']) . ') ';
                        $query .= 'VALUES ';

                        $values = [];

                        foreach ( $container['values'] as $fieldsValuesArray ) {
                            $values[] = '(' . implode(',', $fieldsValuesArray) . ')';
                        }

                        $query .= implode(',', $values);

                    break;
            }
        }

        $this->queryString = $query;
    }

    private function getContainer(string $type): array
    {
        if ( isset($this->containers[$type]) === false ) {
            throw new Exception("Error. Container by type '$type' not exists");
        }

        return $this->containers[$type];
    }

    public function setString(string $query): void
    {
        $this->queryString = $query;
    }
}
