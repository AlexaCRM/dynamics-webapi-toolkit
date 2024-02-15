<?php

namespace AlexaCRM\Xrm\Query;

use Closure;

class FilterSet
{
    public array $filters = [];

    public function __construct(public $logicalOperator = 'and')
    {
    }

    /**
     * @param $logicalOperator
     * @return void
     */
    public function setLogicalOperator($logicalOperator)
    {
        $this->logicalOperator = $logicalOperator;
    }

    /**
     * @param $column
     * @param $value
     * @param  string  $operator
     * @param  bool  $negate
     * @return $this
     * @throws \Exception
     */
    public function where($column, $value = null, string $operator = 'eq', bool $negate = false)
    {
        if ($column instanceof Filter || $column instanceof static) {
            $this->filters[] = $column;
            return $this;
        }

        if ($column instanceof Closure) {
            return $this->whereClosure($column);
        }

        $this->filters[] = new Filter($column, $value, $operator, $negate);
        return $this;
    }

    /**
     * @param  \Closure  $callback
     * @return $this
     * @throws \Exception
     */
    public function whereClosure(Closure $callback): static
    {
        $callback($filterSet = new static());

        return $this->where($filterSet);
    }

    /**
     * @return string
     */
    public function toString()
    {
        $filters = array_map(function ($filter) { return $filter->toString(); }, $this->filters);
        return '(' . implode(' ' . $this->logicalOperator . ' ', $filters) . ')';
    }
}
