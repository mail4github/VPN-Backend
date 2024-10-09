<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\TransactionsGetDto;
use App\Service\UsefulToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TransactionController.
 */
class TransactionController extends AbstractController
{
    /**
     * Retrieves a list of transactions.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param EntityManagerInterface $entityManager
     * @param TransactionsGetDto     $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/transactions', name: 'list_of_transactions', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/transactions',
        summary: 'Retrieves a list of transactions',
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort transactions based on specified criteria.',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['created', 'tr_type', 'amount', 'currency']
                )
            ),
            new OA\Parameter(
                name: 'sort_order',
                description: 'Sort order. Ascend or descend',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'desc',
                    enum: ['asc', 'desc']
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Retrieve list of transactions which have user_id like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
            new OA\Parameter(
                name: 'server_id',
                description: 'Retrieve list of transactions which have server_id like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 456
                )
            ),
            new OA\Parameter(
                name: 'connection_type',
                description: 'Filter transactions by the connection type. List of the connection types separated by comma',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'test_traffic,test_period,traffic,period'
                )
            ),
            new OA\Parameter(
                name: 'offset',
                description: 'Skip the first `offset` rows',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 0
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Return the number of values not exceeding that specified in the parameter:`limit`',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 24
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A list of transactions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'user_id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'created',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'modified',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'tr_type',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'amount',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'currency',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'status',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'date_will_active',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'description',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'balance',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'txid',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'items_spent',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'items_total',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'item_name',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'connection_id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'connection_type',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'server_id',
                                type: 'integer'
                            ),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function listOfTransactions(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        TransactionsGetDto $dto
    ): JsonResponse {
        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'trns.id',
                'trns.user_id',
                'trns.created',
                'trns.modified',
                'trns.tr_type',
                'trns.amount',
                'trns.currency',
                'trns.status',
                'trns.date_will_active',
                'trns.description',
                'trns.balance',
                'trns.txid',
                'trns.items_spent',
                'trns.items_total',
                'trns.item_name',
                'trns.connection_id',
                'MIN(cnct.connection_type) AS connection_type',
                'MIN(cnct.server_id) AS server_id'
            )
            ->from('app.public."transaction"', 'trns')
            ->leftJoin('trns', 'app.public."vpn_connection"', 'cnct', 'trns.connection_id = cnct.id')
            ->groupBy('trns.id')
            ->where('TRUE')
            ->setFirstResult($dto->offset)
            ->setMaxResults($dto->limit)
        ;

        if (!empty($dto->user_id)) {
            $queryBuilder->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
        }

        if (!empty($dto->server_id)) {
            $queryBuilder->andWhere('cnct.server_id = :server_id')->setParameter('server_id', $dto->server_id);
        }

        if (!empty($dto->connection_type)) {
            $queryBuilder->andWhere('cnct.connection_type IN (:connection_type)')->setParameter('connection_type', explode(',', $dto->connection_type), \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }

        $queryBuilder->orderBy('trns.'.$usefulToolsHelper->sanitizeString($dto->sort_by), $dto->sort_order);

        // echo $queryBuilder->getSQL(); exit; // SHOW SQL

        $transactions = $queryBuilder->execute()->fetchAll();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($transactions);
    }

    /**
     * Retrieves a transaction based on the transaction id.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param EntityManagerInterface $entityManager
     * @param string                 $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/transaction/{id}', name: 'get_transaction', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/transaction/{id}',
        summary: 'Retrieves a transaction based on the transaction id.',
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Transaction ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A record, which has a value called `results`, which contains the resulting transaction',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'user_id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'created',
                            type: 'string',
                            format: 'date-time'
                        ),
                        new OA\Property(
                            property: 'modified',
                            type: 'string',
                            format: 'date-time'
                        ),
                        new OA\Property(
                            property: 'tr_type',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'amount',
                            type: 'number'
                        ),
                        new OA\Property(
                            property: 'currency',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'date_will_active',
                            type: 'string',
                            format: 'date-time'
                        ),
                        new OA\Property(
                            property: 'description',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'balance',
                            type: 'number'
                        ),
                        new OA\Property(
                            property: 'txid',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'items_spent',
                            type: 'number'
                        ),
                        new OA\Property(
                            property: 'items_total',
                            type: 'number'
                        ),
                        new OA\Property(
                            property: 'item_name',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'connection_id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'connection_type',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'server_id',
                            type: 'integer'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function getTransaction(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        string $id
    ): JsonResponse {
        // Query the database based on the transaction id
        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'trns.id',
                'trns.user_id',
                'trns.created',
                'trns.modified',
                'trns.tr_type',
                'trns.amount',
                'trns.currency',
                'trns.status',
                'trns.date_will_active',
                'trns.description',
                'trns.balance',
                'trns.txid',
                'trns.items_spent',
                'trns.items_total',
                'trns.item_name',
                'trns.connection_id',
                'MIN(cnct.connection_type) AS connection_type',
                'MIN(cnct.server_id) AS server_id'
            )
            ->from('app.public."transaction"', 'trns')
            ->leftJoin('trns', 'app.public."vpn_connection"', 'cnct', 'trns.connection_id = cnct.id')
            ->groupBy('trns.id')
            ->where('trns.id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $id)
        ;
        $transactions = $queryBuilder->execute()->fetchAll();
        if (null != $transactions && $transactions && \count($transactions) > 0) {
            $transaction = $transactions[0];
        } else {
            return $usefulToolsHelper->generate_answer('', 'No transaction with such id', 'ERROR_GET_TRANSACTION_1', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($transaction);
    }
}
