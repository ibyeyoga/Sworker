<?php

namespace IBye\memory;
use swoole_table as stable;
class TableWorkerSpace implements WorkerSpace
{
    private $_instance;

    public function __construct($max)
    {
            $this->_instance = new stable($max);
            $this->_instance->column(WorkerSpace::FIELD_WID, stable::TYPE_INT, 2);//strlen($_max)
//            $this->workerSpace->column('name', swoole_table::TYPE_STRING, $_nameLength);
            $this->_instance->column(WorkerSpace::FIELD_TYPE, stable::TYPE_INT, 1);
            $this->_instance->column(WorkerSpace::FIELD_STATUS, stable::TYPE_INT, 1);
            $this->_instance->create();
    }


    public function getWorkerInfo($wId)
    {
        return $this->_instance->get($wId);
    }

    public function setWorkerInfo($wId, array $info)
    {
        $this->_instance->set($wId, $info);
    }

    public function setWorkerInfoField($wId, $field, $value)
    {
        $this->_instance->set($wId, [
            $field => $value
        ]);
    }

    public function removeWorkerInfo($wId)
    {

    }

    public function getWorkerInfoField($wId, $field)
    {
        return $this->_instance->get($wId, $field);
    }
}