<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 23.07.18
 * Time: 18:09
 */

namespace AsteriskBundle\Command;

use AsteriskBundle\ARI\Client;
use AsteriskBundle\ARIEntity\Endpoint;
use AsteriskBundle\Document\SipHistory;
use AsteriskBundle\Repository\Mongo\SipHistoryRepository;
use CallCenterBundle\Entity\SipToManagerRelation;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use GepurIt\User\Entity\UserProfile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateEndpointOnlineStatusCommand
 * @package AsteriskBundle\Command
 */
class ExecuteUpdateSipStatusCommand extends Command
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;
    /**
     * @var ObjectManager
     */
    private $documentManager;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UpdateEndpointOnlineStatusCommand constructor.
     * @param DocumentManager $documentManager
     * @param EntityManagerInterface $entityManager
     * @param Client $client
     */
    public function __construct(
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager,
        Client $client
    ) {
        parent::__construct();
        $this->documentManager = $documentManager;
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('asterisk:sip:status:execute')
            ->setDescription('Check sip online status')
            ->setHelp('Example usage: bin/console asterisk:sip:status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->input  = $input;
        $this->output = $output;

        /** @var SipHistoryRepository $sipHistoryRepo */
        $sipHistoryRepo = $this->documentManager->getRepository(SipHistory::class);

        $countCreated = 0;
        $countUpdated = 0;

        try {
            $endpoints = $this->client->getOnlineEndpoints();
        } catch (\Exception $exception) {
            /** @var SipHistory[] $sips */
            $sips = $sipHistoryRepo->findBy(['dateStop' => null]);

            foreach ($sips as $sip) {
                $sip->setDateStop(new \DateTime('now'));
            }

            $this->documentManager->flush();

            throw $exception;
        }


        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select(' up.managerName, stmr.managerId, stmr.sipIncoming, stmr.sipOutgoing')
            ->from(SipToManagerRelation::class, 'stmr')
            ->join(UserProfile::class, 'up', 'WITH', 'stmr.managerId = up.managerId');

        $sipToManagerRel = $queryBuilder->getQuery()->getResult();

        foreach ($endpoints as $endpoint) {
            $sip = $sipHistoryRepo->findOnlineBySip($resource = $endpoint->getResource());

            $managerId = null;
            $managerName = null;

            $managerIndex = array_search($resource, array_column($sipToManagerRel, 'sipIncoming'))
                            ?? array_search($resource, array_column($sipToManagerRel, 'sipOutgoing'));

            if (false !== $managerIndex) {
                $managerId = $sipToManagerRel[$managerIndex]['managerId'];
                $managerName = $sipToManagerRel[$managerIndex]['managerName'];
            }

            if (Endpoint::STATE_ONLINE !== $endpoint->getState() && null !== $sip) {
                $sip->setDateStop(new \DateTime('now'));

                $countUpdated++;
            }

            if (Endpoint::STATE_ONLINE === $endpoint->getState()) {
                if (null === $sip) {
                    $addedSip = new SipHistory();
                    $addedSip->setResource($resource);
                    $addedSip->setManagerId($managerId);
                    $addedSip->setManagerName($managerName);
                    $addedSip->setDateStart(new \DateTime('now'));

                    $this->documentManager->persist($addedSip);

                    $countCreated++;
                }
            }
        }

        $this->documentManager->flush();

        $message = "<fg=green>UPDATED</> <fg=blue>{$countUpdated}</> sips.";
        $message .= " <fg=green>CREATED</> <fg=blue>{$countCreated}</> sips.";
        $this->output->writeln($message);
    }
}
