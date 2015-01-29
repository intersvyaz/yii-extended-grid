<?php
namespace Intersvyaz\ExtendedGrid;

use CActiveDataProvider;
use CException;
use CGridColumn;
use CGridView;
use IDataProvider;
use Yii;

class ExtGridView extends CGridView
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @inheritdoc
     */
    protected function initColumns()
    {
        $id=$this->getId();
        if ($this->columns === []) {
            $this->columns = $this->getDefaultColumnNames();
        }

        $this->headers = $this->processColumns($this->columns);
        $this->columns = [];
        $index = 0;
        foreach ($this->headers as &$column) {
            if (is_array($column)) {
                foreach($column['columns'] as &$subcolumn){
                    $this->columns[$index] = $subcolumn;
                }
            }else{
                $column->headerHtmlOptions = array_merge($column->headerHtmlOptions, ['rowspan' => 2]);
                $this->columns[$index] = $column;
            }

            $index++;
        }

        foreach($this->columns as $i => $column){
            if($column->id===null) {
                $column->id = $id . '_c' . $i;
            }
            $column->init();
        }
    }

    /**
     * @param array $columns
     * @return array
     */
    protected function processColumns(array $columns)
    {
        $newColumns = [];
        foreach ($columns as $i => $column) {
            if (is_array($column) && array_key_exists('columns', $column)) {
                $subcolumns = $this->processColumns($column['columns']);
                if ($subcolumns !== []) {
                    $newColumns[$i] = [
                        'header' => array_key_exists('header', $column) ? $column['header'] : '',
                        'columns' => $subcolumns,
                    ];
                }
            } else {
                $column = $this->initColumn($column);
                if ($column->visible) {
                    $newColumns[$i] = $this->initColumn($column);
                }
            }
        }

        return $newColumns;
    }

    /**
     * Return default column names. Get it from dataProvider if it possible.
     * @return array
     */
    protected function getDefaultColumnNames()
    {
        if ($this->dataProvider instanceof CActiveDataProvider) {
            return $this->dataProvider->model->attributeNames();
        } elseif ($this->dataProvider instanceof IDataProvider) {
            // use the keys of the first row of data as the default columns
            $data = $this->dataProvider->getData();
            if (isset($data[0]) && is_array($data[0])) {
                return array_keys($data[0]);
            }
        }

        return [];
    }

    /**
     * @param array|string $params
     * @return CGridColumn
     * @throws CException
     */
    protected function initColumn($params)
    {
        if (is_string($params)) {
            $column = $this->createDataColumn($params);
        } else {
            if (!array_key_exists('class', $params)) {
                $params['class'] = 'CDataColumn';
            }
            $column = Yii::createComponent($params, $this);
        }
        if (!$column->visible) {
            return false;
        }

        return $column;
    }

    /**
     * Renders the table header.
     */
    public function renderTableHeader()
    {
        if (!$this->hideHeader) {
            echo "<thead>\n";

            if ($this->filterPosition === self::FILTER_POS_HEADER) {
                $this->renderFilter();
            }

            echo "<tr>\n";
            foreach ($this->headers as $column) {
                if(!is_array($column)) {
                    $column->renderHeaderCell();
                }else{
                    echo '<th colspan="'.count($column['columns']).'">'.$column['header'].'</th>';
                }
            }
            echo "</tr>\n";

            echo "<tr>\n";
            foreach ($this->headers as $column) {
                if(is_array($column)) {
                    foreach($column['columns'] as $subcolumn) {
                        $subcolumn->renderHeaderCell();
                    }
                }
            }
            echo "</tr>\n";


            if ($this->filterPosition === self::FILTER_POS_BODY) {
                $this->renderFilter();
            }

            echo "</thead>\n";
        } elseif ($this->filter !== null && ($this->filterPosition === self::FILTER_POS_HEADER || $this->filterPosition === self::FILTER_POS_BODY)) {
            echo "<thead>\n";
            $this->renderFilter();
            echo "</thead>\n";
        }
    }
}
