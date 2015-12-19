<?php

namespace AppBundle\Util;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;

class TagManager
{
    const DEFAULT_NUM_SUGGESTIONS = 5;

    /** @var EntityManager */
    protected $em;

    /** @var Connection */
    protected $conn;

    protected $default_suggestions_high = array(
        'work',
        'finances',
        'family',
        'lover',
        'multitasking',
        'tension: neck/shoulders',
        'tension: stomach',
        'physical pain',
        'sick',
        'tired',
    );

    protected $default_suggestions_low = array(
        'meditation',
        'abdominal breathing',
        'friends',
        'family',
        'lover',
        'music',
        'faith',
        'focus',
    );

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->conn = $em->getConnection();
    }

    public function getTextsByUser(User $user)
    {
        $result = $this->conn->fetchAll('
            SELECT DISTINCT text
            FROM stress_manifestation
            JOIN stress_log ON stress_log_id = stress_log.id
            WHERE user_id = ?
            ORDER BY text ASC
        ', array($user->getId()));

        return array_column($result, 'text');
    }

    /**
     * @param User $user
     * @param array $options<pre>
     *     limit
     * </pre>
     * @return array An array of suggestions in the following format:<pre>
     *     array('high' => array('text1', 'text2', 'text3', ...), // Suggestions for high stress levels (>=5)
     *           'low' => array('texta', 'textb', 'textc', ...))  // Suggestions for low stress levels (<5)
     * </pre>
     */
    public function getSuggestions(User $user, array $options = array())
    {
        $limit = array_key_exists('limit', $options) ? $options['limit'] : self::DEFAULT_NUM_SUGGESTIONS;

        $high = $this->getRecentFrequent($user, array('level' => array('>=' => 5)), $limit);
        // Pad out any empty slots with default suggestions.
        for ($i = count($high), $j = 0; $i < $limit; $i++, $j++) {
            $high[] = $this->default_suggestions_high[$j];
        }

        $low = $this->getRecentFrequent($user, array('level' => array('<' => 5)), $limit);
        // Pad out any empty slots with default suggestions.
        for ($i = count($low), $j = 0; $i < $limit; $i++, $j++) {
            if (!array_key_exists($j, $this->default_suggestions_low)) {
                break;
            }

            $low[] = $this->default_suggestions_low[$j];
        }

        return array(
            'high' => $high,
            'low' => $low,
        );
    }

    protected function getRecentFrequent(User $user, array $criteria = null, $limit = 10)
    {
        $sql = '
            SELECT text, MAX(time) AS recency, COUNT(*) AS frequency
            FROM stress_manifestation
            JOIN stress_log ON stress_log_id = stress_log.id
            WHERE user_id = ?
        ';
        $params = array($user->getId());

        if (is_array($criteria)) {
            foreach ($criteria as $key => $val) {
                $op = '=';
                if (is_array($val)) {
                    reset($val);
                    $op = key($val);
                    $val = current($val);
                }
                $sql .= 'AND `' . $key . '` ' . $op . ' ? ';
                $params[] = $val;
            }
        }

        $sql .= 'GROUP BY text ';
        $sql .= 'ORDER BY recency DESC, frequency DESC ';
        $sql .= 'LIMIT ' . (int) $limit;

        $result = $this->conn->fetchAll($sql, $params);

        return array_column($result, 'text');
    }
}