<?php

namespace MagdKudama\Datatables;

/**
 * Handle server-side work for Datatables  JQuery plugin
 *
 * @package    magdkudama/datatables
 * @author     Magd Kudama (magdkudama@gmail.com)
 */
class Datatables
{
    /**
     * The name of the query builder instance required
     *
     * @var string
     */
    private static $class = 'Illuminate\Database\Query\Builder';

    /**
     * The query builder instance
     *
     * @var Illuminate\Database\Query\Builder
     */
    private $query;

    /**
     * The column names given on the query method
     *
     * @var array
     */
    private $columns = array();

    /**
     * The total number of records
     *
     * @var int
     */
    private $totalRecords;

    /**
     * The filtered total records
     *
     * @var int
     */
    private $queryRecords;

    /**
     * Recieves the query instance
     *
     * @param  Illuminate\Database\Query\Builder $query
     * @return MagdKudama\Datatables\Datatables
     * @throws InvalidArgumentException
     */
    public function of($query)
    {
        if (get_class($query) !== static::$class) {
            throw new \InvalidArgumentException();
        }

        $this->query = $query;

        return $this;
    }

    /**
     * Composes the query, and creates the response
     *
     * @return void
     */
    public function make()
    {
        $this->setColumns();
        $this->setTotalCount();
        $this->filter();
        $this->setQueryCount();
        $this->order();
        $this->paginate();

        return $this->getResults();
    }

    /**
     * Sets the column names based on the select() statement given
     *
     * @return void
     */
    private function setColumns()
    {
        foreach ($this->query->columns as $column) {
            $values = explode(' as ', strtolower($column));
            if (count($values) == 2) {
                $columnName = $values[0];
            } else {
                $columnName = $values[0];
            }

            $this->columns[] = $columnName;
        }
    }

    /**
     * Sets the total number of rows of the query
     *
     * @return void
     */
    private function setTotalCount()
    {
        $this->totalRecords = $this->query->count();
    }

    private function filter()
    {
        if (\Input::get('sSearch', '') !== '') {
            $query = $this->query;
            $columns = $this->columns;
            $this->query->where(function($query) use ($columns) {
                foreach ($columns as $key => $column) {
                    if (\Input::get('bSearchable_' . $key) === "true") {
                        $query->orWhere($column, 'like', '%' . \Input::get('sSearch') . '%');
                    }
                }
            });
        }
    }

    /**
     * Sets the number of rows, including filters provided
     *
     * @return void
     */
    private function setQueryCount()
    {
        $this->queryRecords = $this->query->count();
    }

    /**
     * Adds order statements to the query builder, based on the input
     *
     * @return void
     */
    private function order()
    {
        for ($i = 0 ; $i < count(\Input::get('iColumns')) ; $i++) {
            if (\Input::get('iSortCol_' . $i, '') !== '') {
                $this->query->orderBy($this->columns[\Input::get('iSortCol_' . $i)], \Input::get('sSortDir_' . $i));
            }
        }
    }

    /**
     * Sets the pagination
     *
     * @return void
     */
    private function paginate()
    {
        if (\Input::get('iDisplayStart', '') !== '') {
            $this->query->skip(\Input::get('iDisplayStart'));
        }

        if (\Input::get('iDisplayLength', '') !== '') {
            $this->query->take(\Input::get('iDisplayLength'));
        }
    }

    /**
     * Returns the JSON response (regulating the results)
     *
     * @return Response
     */
    private function getResults()
    {
        $results = $this->query->get();

        $data = array();
        foreach ($results as $result) {
            $individual = array_values((array) $result);
            $data[] = $individual;
        }

        $output = array(
            'sEcho' => intval(\Input::get('sEcho')),
            'iTotalRecords' => $this->totalRecords,
            'iTotalDisplayRecords' => $this->queryRecords,
            'aaData' => $data
        );

        return \Response::json($output);
    }

}
