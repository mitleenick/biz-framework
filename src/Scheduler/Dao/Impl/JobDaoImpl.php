<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'job';

    public function findWaitingJobsByLessThanFireTime($fireTime)
    {
        $sql = "SELECT * FROM 
                (
                  SELECT *, floor(createdTime/60)*60 as formattedCreatedTime FROM {$this->table} 
                  WHERE status = 'waiting' AND enabled = 1 AND deleted = 0 AND nextFireTime <= ?
                ) as {$this->table} 
                ORDER BY formattedCreatedTime ASC , priority DESC";

        return $this->db()->fetchAll($sql, array($fireTime));
    }

    public function getByName($name)
    {
        return $this->getByFields(array(
            'name' => $name,
            'deleted' => 0
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'args' => 'json',
            ),
            'conditions' => array(
                'deletedTime < :lessThanDeletedTime'
            )
        );
    }
}