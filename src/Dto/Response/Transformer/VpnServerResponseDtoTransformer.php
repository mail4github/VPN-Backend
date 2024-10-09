<?php

declare(strict_types=1);

namespace App\Dto\Response\Transformer;

use App\Entity\FavoriteServer;
use App\Entity\VpnServer;
use Doctrine\ORM\EntityManagerInterface;

class VpnServerResponseDtoTransformer
{
    /**
     * @param VpnServer              $server
     * @param EntityManagerInterface $entityManager
     * @param int                    $user_id
     *
     * @return array
     */
    public function transformFromVpnServer(VpnServer $server, EntityManagerInterface $entityManager, int $user_id): array
    {
        if (!$server instanceof VpnServer) {
            throw new Exception('Expected type of VpnServer but got '.$server::class);
        }
        $res =
        [
            'id' => $server->getId(),
            'network' => [
                'ip' => $server->getIp(),
                'signal_level' => $server->getConnectionQuality(),
                'protocol' => $server->getProtocol(),
                'network_type' => $server->isResidentialIp() ? 'RESIDENTAL' : 'COMMERCIAL',
            ],
            'country' => $server->getCountry(),
            'type' => $server->isForFree() ? 'FREE' : 'PAID',
            // TODO: OMG FIX ME!!
            'is_favourite' => $entityManager->getRepository(FavoriteServer::class)->findOneBy(['serverId' => $server->getId(), 'userId' => $user_id]) ? true : false,
            'info' => [],
            'creator' => [
                'id' => $server->getCreatedBy(),
                'user_name' => $server->getUserName(),
                'avatar' => '',
            ],
            'workload' => 0, // The server load in percents. Must be coded in future
            'is_purchased' => 0, // Must be coded in future
            'used_value' => 0.5, // Must be coded in future
            'created' => $server->getCreated()->format('Y-m-d H:i:s'),
            'modified' => $server->getModified()->format('Y-m-d H:i:s'),
            'wallet_address' => $server->getWalletAddress(),
            'price' => $server->getPrice() ?: 0,
            'service_commission' => $server->getServiceCommission(),
            'maximum_active_connections' => $server->getMaximumActiveConnections(),
            'traffic_vs_period' => $server->isTrafficVsPeriod(),
            'test_packages' => $server->getTestPackages(),
            'paid_packages' => $server->getPaidPackages(),
        ];

        if ($server->getCreatedBy() == $user_id) {
            $res['info'][] = 'MY_SERVER';
        }

        if ($server->isForFree()) {
            $res['info'][] = 'FREE';
        }

        if ((time() - $server->getCreated()->getTimestamp()) < 60 * 60 * 24) {
            $res['info'][] = 'NEW';
        }

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('usr.picture')
            ->from('app.public."user"', 'usr')
            ->where('usr.id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $server->getCreatedBy())
        ;
        $users = $queryBuilder->execute()->fetchAll();
        if (null != $users && $users && \count($users) > 0) {
            $res['creator']['avatar'] = $users[0]['picture'];
        }

        return $res;
    }
}
