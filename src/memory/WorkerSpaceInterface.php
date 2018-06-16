<?php
/**
 * Created by PhpStorm.
 * User: liyongjia
 * Date: 2018/6/16
 * Time: 17:34
 */

namespace IBye\memory;


interface WorkerSpaceInterface
{
    const FIELD_WID = 'wid';
    const FIELD_STATUS = 'status';
    const FIELD_TYPE = 'type';

    public function getWorkerInfo($wId);

    public function setWorkerInfo($wId, array $info);

    public function getWorkerInfoField($wId, $field);

    public function setWorkerInfoField($wId, $field, $value);

    public function removeWorkerInfo($wId);

}