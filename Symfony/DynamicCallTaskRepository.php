<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 02.08.18
 * Time: 15:16
 */

namespace CallCenterBundle\Repository;

use CallCenterBundle\Entity\DynamicCallTask;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use GepurIt\CallTaskBundle\Entity\CallTaskMark;
use GepurIt\CallTaskBundle\Repository\CallTaskMarkRepository;

/**
 * Class DynamicCallTaskRepository
 * @package CallCenterBundle\Repository
 */
class DynamicCallTaskRepository extends EntityRepository
{
    /**
     * @param string $status
     * @param string $mark
     *
     * @return DynamicCallTask|null
     */
    public function findOneOldestByStatusAndMark(string $status, string $mark)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('dct');

        $start           = new \DateTime('now');
        $lockedClientIds = $this->getLockedClintIds();

        $queryBuilder
            ->where("dct.status = :status")->setParameter('status', $status)
            ->andWhere('dct.startAt <= :start')->setParameter('start', $start)
            ->andWhere('dct.isDone = :done')->setParameter('done', false)
            ->andWhere('dct.mark = :mark')->setParameter('mark', $mark)
            ->orderBy('dct.startAt', 'ASC')
            ->setMaxResults(1);

        if (!empty($lockedClientIds)) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('dct.clientId', $lockedClientIds));
        }

        $orderCallTask = $queryBuilder->getQuery()->getOneOrNullResult();

        return $orderCallTask;
    }

    /**
     * @return string[]
     */
    private function getLockedClintIds(): array
    {
        /** @var CallTaskMarkRepository $ctmRepository */
        $ctmRepository = $this->getEntityManager()->getRepository(CallTaskMark::class);

        return $ctmRepository->getLockedClintIds();
    }
}
