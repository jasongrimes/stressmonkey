<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * StressLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StressLogRepository extends EntityRepository
{
    /**
     * Find log entries made by the given user that match the given filter criteria.
     *
     * @param User $user
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function findFiltered(User $user, array $filter = array(), array $options = array())
    {
        list($sql, $params) = $this->commonSql($user, $filter);
        $sql = 'SELECT l.id ' . $sql . ' ORDER BY l.local_time DESC ';

        $db = $this->getEntityManager()->getConnection();
        $result = $db->fetchAll($sql, $params);

        return $this->findByIds(array_column($result, 'id'));
    }

    /**
     * @param User $user
     * @param array $filter
     * @return array
     */
    protected function commonSql(User $user, array $filter = array())
    {
        $params = array();

        //
        // FROM and JOINs
        //
        $sql = 'FROM stress_logs l ';

        // JOIN for factors.
        $factors = array();
        $factorJoinType = '';
        if (isset($filter['factors'])) {
            $factors = array_map('trim', explode(',', $filter['factors']));
            $factorJoinType = (isset($filter['factorOp']) && $filter['factorOp'] == 'or') ? 'left' : 'inner';
        }
        foreach ($factors as $i => $factor) {
            if ($factorJoinType == 'left') {
                $sql .= 'LEFT ';
            }
            $sql .= 'JOIN stress_log_factors f' . $i . ' ';
            $sql .= 'ON (l.id = f' . $i . '.stress_log_id AND f' . $i . '.text = :factor' . $i . ') ';
            $params['factor' . $i] = $factor;
        }

        //
        // WHERE clause
        //
        $sql .= 'WHERE user_id = :user_id ';
        $params['user_id'] = $user->getId();

        // OR factors.
        if ($factorJoinType == 'left') {
            $sql .= 'AND (';
            foreach ($factors as $i => $factor) {
                if ($i > 0) $sql .= 'OR ';
                $sql .= 'f' . $i . '.text IS NOT NULL ';
            }
            $sql .= ') ';
        }

        // From local time
        if (isset($filter['from'])) {
            if (!$filter['from'] instanceof \DateTime) {
                throw new \InvalidArgumentException('Expected filter "from" value to be a \DateTime object.');
            }
            $sql .= 'AND l.local_time >= :from ';
            $params['from'] = $filter['from']->format('Y-m-d H:i:s');
        }

        // To local time
        if (isset($filter['to'])) {
            if (!$filter['to'] instanceof \DateTime) {
                throw new \InvalidArgumentException('Expected filter "to" value to be a \DateTime object.');
            }
            $sql .= 'AND l.local_time >= :to ';
            $params['to'] = $filter['to']->format('Y-m-d H:i:s');
        }

        // Level
        if (isset($filter['level'])) {
            $op = '=';
            if (isset($filter['levelOp']) && in_array($filter['levelOp'], array('=', '<=', '>='))) {
                $op = $filter['levelOp'];
            }
            $sql .= 'AND l.level ' . $op . ' :level ';
            $params['level'] = $filter['level'];
        }

        // With notes
        if (isset($filter['withNotes']) && $filter['withNotes']) {
            $sql .= 'AND l.notes IS NOT NULL AND l.notes != "" ';
        }


        return array($sql, $params);

    }

    /**
     * @param array $ids
     * @param bool $keepOrder
     * @return array
     */
    public function findByIds(array $ids, $keepOrder = true)
    {
        $unsorted = $this->findBy(array('id' => $ids));

        if ($keepOrder) {
            return $this->reorderObjectsById($unsorted, array_flip($ids));
        }

        return $unsorted;

    }

    /**
     * @param array $objects
     * @param array $idSortMap
     * @return array
     */
    public function reorderObjectsById(array $objects, array $idSortMap)
    {
        usort($objects, function ($a, $b) use ($idSortMap) {
            return ($idSortMap[$a->getId()] < $idSortMap[$b->getId()]) ? -1 : 1;
        });

        return $objects;
    }
}
