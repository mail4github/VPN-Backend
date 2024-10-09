<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\StatisticsSequenceBillingGetDto;
use App\Dto\StatisticsSequenceConnectionsGetDto;
use App\Service\UsefulToolsHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StatisticsController.
 */
class StatisticsController extends AbstractController
{
    /**
     * Retrieves a sequence of values, which is calculated on the base of transactions.
     *
     * @param UsefulToolsHelper               $usefulToolsHelper
     * @param User|null                       $user
     * @param EntityManagerInterface          $entityManager
     * @param StatisticsSequenceBillingGetDto $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/statistics/sequence/billing', name: 'statistics_sequence_billing', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/statistics/sequence/billing',
        summary: 'Retrieves a sequence of values, which is calculated on the base of transactions',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'currency',
                description: 'Retrieve transactions which have currency equals that value',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'USD'
                )
            ),
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort transactions based on specified criteria.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'created',
                    enum: ['created']
                )
            ),
            new OA\Parameter(
                name: 'sort_order',
                description: 'Sort order. Ascend or descend',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'asc',
                    enum: ['asc', 'desc']
                )
            ),
            new OA\Parameter(
                name: 'period_type',
                description: 'Type of period of the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'today',
                    enum: [
                        'interval', 'today', 'day_before', 'last_7_days',
                        'previous_7_days', 'during_month', 'during_previous_month',
                        'during_year', 'during_previous_year', 'all_time',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'interval_begin',
                description: 'A start date and time of the sequence (if `period_type` = `interval`)',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '2024-02-22 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'interval_end',
                description: 'A date and time when the sequence ends (if `period_type` = `interval`)',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '2024-02-23 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'step',
                description: 'Duration of each item in the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: null,
                    enum: ['HOUR', 'DAY', 'WEEK', 'MONTH']
                )
            ),
            new OA\Parameter(
                name: 'tr_type',
                description: 'Filter transactions by type. List of the transaction types separated by comma',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'add,sub'
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Filter transactions by user id. Transactions which belong to this user only.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Return the number of values not exceeding that specified in the parameter: `limit`',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 0
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An array of values',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'date_time',
                                type: 'string',
                                example: '2024-02-22 00:00:00'
                            ),
                            new OA\Property(
                                property: 'value',
                                type: 'number',
                                example: 25.01
                            ),
                            new OA\Property(
                                property: 'unix_time',
                                type: 'integer',
                                example: 1708905600
                            ),
                            new OA\Property(
                                property: 'transactions_total',
                                type: 'integer',
                                example: 21
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function statisticsSequenceBilling(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        StatisticsSequenceBillingGetDto $dto
    ): JsonResponse {
        if (empty($dto->sort_by)) {
            $dto->sort_by = 'created';
        }

        $sequence = [];

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('ROUND(AVG(EXTRACT(EPOCH FROM trns.created))) AS unix_time', 'SUM(amount) AS value', 'COUNT(*) AS transactions_total')
            ->from('app.public."transaction"', 'trns')
        ;
        $unixTimeBegin = 0;
        $unixTimeEnd = 0;
        $stepInSeconds = 0;
        switch ($dto->period_type) {
            case 'interval':
                $queryBuilder->where('trns.created >= :interval_begin')->setParameter('interval_begin', $dto->interval_begin);
                $queryBuilder->andWhere('trns.created <= :interval_end')->setParameter('interval_end', $dto->interval_end);

                $unixTimeBegin = strtotime($dto->interval_begin);
                $unixTimeEnd = strtotime($dto->interval_end);

                break;
            case 'all_time':
                $queryBuilder->where('TRUE');

                // Retrieving the date and time of first transaction
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM trns.created)) AS unix_time')
                    ->from('app.public."transaction"', 'trns')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('trns.created', 'ASC')
                ;

                $tmpQB->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);

                if (!empty($dto->tr_type)) {
                    $tmpQB->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
                }

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeBegin = $tmpResArr[0]['unix_time'];
                }

                // Retrieving the date and time of last transaction
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM trns.created)) AS unix_time')
                    ->from('app.public."transaction"', 'trns')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('trns.created', 'DESC');

                if (!empty($dto->currency)) {
                    $tmpQB->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);
                }

                if (!empty($dto->tr_type)) {
                    $tmpQB->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
                }

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeEnd = $tmpResArr[0]['unix_time'];
                }

                if (empty($unixTimeBegin) || empty($unixTimeEnd)) {
                    return $usefulToolsHelper->generate_answer('', 'No transactions for all time', 'ERROR_STATISTICS_SEQUENCE_BILLING_3', 404);
                }

                if (empty($dto->step)) {
                    if ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 24 * 30 * 4) { // Months
                        $dto->step = 'MONTH';
                    } elseif ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 24 * 4) { // Days
                        $dto->step = 'DAY';
                    } elseif ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 4) { // Hours
                        $dto->step = 'HOUR';
                    } else {
                        $dto->step = 'MINUTE';
                    }
                }
                break;
            case 'during_previous_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 YEAR\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 YEAR\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * 365;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 365;
                break;
            case 'during_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 YEAR\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 365;
                break;
            case 'during_previous_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 MONTH\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 MONTH\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * (365 / 12);
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * (365 / 12);
                break;
            case 'during_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 MONTH\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * (365 / 12);
                break;
            case 'previous_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 WEEK\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 WEEK\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * 7;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 7;
                break;
            case 'last_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 WEEK\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 7;
                break;
            case 'day_before':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 DAY\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 DAY\')');
                $unixTimeEnd = time() - 60 * 60 * 24;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24;
                break;
            default:
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 DAY\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24;
                break;
        }

        if (!empty($dto->currency)) {
            $queryBuilder->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);
        }

        if (!empty($dto->tr_type)) {
            $queryBuilder->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
        }

        if (!empty($dto->user_id)) {
            $queryBuilder->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
        }

        switch ($dto->step) {
            case 'MINUTE':
                $queryBuilder->groupBy('CONCAT(DATE(trns.created), DATE_PART(\'HOUR\', trns.created), DATE_PART(:step, trns.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60;
                break;
            case 'HOUR':
                $queryBuilder->groupBy('CONCAT(DATE(trns.created), DATE_PART(:step, trns.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60;
                break;
            case 'DAY':
                $queryBuilder->groupBy('DATE(trns.created)');
                $stepInSeconds = 60 * 60 * 24;
                break;
            case 'WEEK':
                $queryBuilder->groupBy('CONCAT(DATE_PART(\'YEAR\', trns.created), DATE_PART(:step, trns.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60 * 24 * 7;
                break;
            case 'MONTH':
                $queryBuilder->groupBy('CONCAT(DATE_PART(\'YEAR\', trns.created), DATE_PART(:step, trns.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60 * 24 * (365 / 12);

                $start_date = new \DateTime(date('Y-m-01 00:00:00', $unixTimeBegin));
                $end_date = new \DateTime(date('Y-m-01 00:00:00', $unixTimeEnd));
                $interval = $start_date->diff($end_date);

                $days = (int) $interval->format('%a');
                $numberOfItems = floor($days / (365 / 12)) + 1;

                for ($j = 0; $j < $numberOfItems; ++$j) {
                    $interval = new \DateInterval('P'.$j.'M');
                    if ('desc' == $dto->sort_order) {
                        $sd = new \DateTime(date('Y-m-01 00:00:00', $unixTimeEnd));
                        $d = date_sub($sd, $interval);
                    } else {
                        $sd = new \DateTime(date('Y-m-01 00:00:00', $unixTimeBegin));
                        $d = date_add($sd, $interval);
                    }

                    $sequence[] = [
                        'unix_time' => $d->format('U'),
                        'value' => 0,
                        'date_time' => $d->format('Y-m-d H:i:s'),
                        'transactions_total' => 0,
                    ];
                    if (!empty($dto->limit) && $j >= $dto->limit - 1) {
                        break;
                    }
                }
                break;
        }
        // echo $queryBuilder->getSQL(); exit; // SHOW SQL

        $results = $queryBuilder->execute()->fetchAll();

        if ('MONTH' != $dto->step) {
            $sequence = [];
            $unixTimeBegin = floor($unixTimeBegin / $stepInSeconds) * $stepInSeconds;
            $unixTimeEnd = (floor($unixTimeEnd / $stepInSeconds) + 1) * $stepInSeconds;
            $numberOfItems = round(($unixTimeEnd - $unixTimeBegin) / $stepInSeconds);

            for ($j = 0; $j < $numberOfItems; ++$j) {
                if ('desc' == $dto->sort_order) {
                    $t = (int) ($unixTimeEnd - $stepInSeconds * $j);
                } else {
                    $t = (int) ($unixTimeBegin + $stepInSeconds * $j);
                }
                $sequence[] = [
                    'unix_time' => $t,
                    'value' => 0,
                    'date_time' => date('Y-m-d H:i:s', $t),
                    'transactions_total' => 0,
                ];
                if (!empty($dto->limit) && $j >= $dto->limit - 1) {
                    break;
                }
            }
        }

        // Fill in the sequence array with values from database
        for ($j = 0; $j < \count($sequence); ++$j) {
            $t_begin = $sequence[$j]['unix_time'];
            if ($j < \count($sequence) - 1) {
                $t_end = $sequence[$j + 1]['unix_time'];
            } else {
                $t_end = $sequence[$j]['unix_time'] + $stepInSeconds;
            }

            for ($i = 0; $i < \count($results); ++$i) {
                if (null != $results[$i]['unix_time'] && null != $results[$i]['value'] && $results[$i]['unix_time'] >= $t_begin && $results[$i]['unix_time'] < $t_end) {
                    $sequence[$j]['value'] = $results[$i]['value'];
                    $sequence[$j]['transactions_total'] = $results[$i]['transactions_total'];
                    break;
                }
            }
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($sequence);
    }

    /**
     * Retrieves a sequence of values, which is calculated on the base of transactions grouped by countries.
     *
     * @param UsefulToolsHelper               $usefulToolsHelper
     * @param User|null                       $user
     * @param EntityManagerInterface          $entityManager
     * @param StatisticsSequenceBillingGetDto $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/statistics/sequence/billing/countries', name: 'statistics_sequence_billing_countries', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/statistics/sequence/billing/countries',
        summary: 'Retrieves a sequence of values, which is calculated on the base of transactions grouped by countries',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'currency',
                description: 'Name of currency',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'USD'
                )
            ),
            new OA\Parameter(
                name: 'period_type',
                description: 'Type of period of the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'today',
                    enum: [
                        'interval',
                        'today',
                        'day_before',
                        'last_7_days',
                        'previous_7_days',
                        'during_month',
                        'during_previous_month',
                        'during_year',
                        'during_previous_year',
                        'all_time',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'interval_begin',
                description: 'A start date and time of the sequence (if `period_type` = `interval`)',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '2024-02-22 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'interval_end',
                description: 'An end date and time of the sequence (if `period_type` = `interval`)',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '2024-02-24 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'tr_type',
                description: 'Type of transactions',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'any'
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'User ID',
                in: 'query',
                schema: new OA\Schema(
                    type: 'integer',
                    format: 'int64',
                    example: 0
                )
            ),
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort by this field',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'amount',
                    enum: [
                        'amount',
                        'transactions_total',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'sort_order',
                description: 'Sort order',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'asc',
                    enum: [
                        'asc',
                        'desc',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Maximum number of items in the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'integer',
                    format: 'int64',
                    example: 0
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An array of values',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'country',
                                type: 'string',
                                example: 'RU'
                            ),
                            new OA\Property(
                                property: 'amount',
                                type: 'number',
                                example: 25.01
                            ),
                            new OA\Property(
                                property: 'transactions_total',
                                type: 'integer',
                                example: 21
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
            ),
        ]
    )]
    public function statisticsSequenceBillingCountries(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        StatisticsSequenceBillingGetDto $dto
    ): JsonResponse {
        if ('amount' != $dto->sort_by) {
            $dto->sort_by = 'amount';
        }

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from('app.public."transaction"', 'trns')
        ;

        $finalQB = $entityManager->getConnection()->createQueryBuilder();

        $unixTimeBegin = 0;
        $unixTimeEnd = 0;
        switch ($dto->period_type) {
            case 'interval':
                $queryBuilder->where('trns.created >= :interval_begin')->setParameter('interval_begin', $dto->interval_begin);
                $queryBuilder->andWhere('trns.created <= :interval_end')->setParameter('interval_end', $dto->interval_end);

                $finalQB->setParameter('interval_begin', $dto->interval_begin);
                $finalQB->setParameter('interval_end', $dto->interval_end);

                break;
            case 'all_time':
                $queryBuilder->where('TRUE');

                // Retrieving the date and time of first transaction
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM trns.created)) AS unix_time')
                    ->from('app.public."transaction"', 'trns')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('trns.created', 'ASC')
                ;

                if (!empty($dto->currency)) {
                    $tmpQB->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);
                }

                if (!empty($dto->tr_type)) {
                    $tmpQB->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
                }

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeBegin = $tmpResArr[0]['unix_time'];
                }

                // Retrieving the date and time of last transaction
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM trns.created)) AS unix_time')
                    ->from('app.public."transaction"', 'trns')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('trns.created', 'DESC');

                if (!empty($dto->currency)) {
                    $tmpQB->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);
                }

                if (!empty($dto->tr_type)) {
                    $tmpQB->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
                }

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeEnd = $tmpResArr[0]['unix_time'];
                }

                if (empty($unixTimeBegin) || empty($unixTimeEnd)) {
                    return $usefulToolsHelper->generate_answer('', 'No transactions', 'ERROR_STATISTICS_SEQUENCE_BILLING_COUNTRIES_3', 404);
                }

                break;
            case 'during_previous_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 YEAR\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 YEAR\')');
                break;
            case 'during_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 YEAR\')');
                break;
            case 'during_previous_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 MONTH\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 MONTH\')');
                break;
            case 'during_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 MONTH\')');
                break;
            case 'previous_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 WEEK\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 WEEK\')');
                break;
            case 'last_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 WEEK\')');
                break;
            case 'day_before':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 DAY\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 DAY\')');
                break;
            default:
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 DAY\')');
                break;
        }

        if (!empty($dto->currency)) {
            $queryBuilder->andWhere('trns.currency = :currency');
        }

        if (!empty($dto->tr_type)) {
            $queryBuilder->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
        }

        if (!empty($dto->user_id)) {
            $queryBuilder->andWhere('trns.user_id = :user_id');
        }

        $finalQB
            ->select('SUM(amount) AS amount', 'UPPER(country) AS country', 'COUNT(*) AS transactions_total')
            ->from('('.
                $entityManager->getConnection()->createQueryBuilder()
                    ->select('*')
                    ->from('('.$queryBuilder->getSQL().')', 'tb1')
                    ->leftJoin('tb1', 'vpn_server', 'srv', 'tb1.user_id = srv.created_by')
                    ->getSQL().
            ')', 'tb2')
            ->where('UPPER(country) IS NOT NULL')
            ->groupBy('UPPER(country)')
            ->setParameter('currency', $dto->currency)
            ->setParameter('user_id', $dto->user_id)
        ;

        $finalQB->orderBy($dto->sort_by, $dto->sort_order);

        if (!empty($dto->limit)) {
            $finalQB->setMaxResults($dto->limit);
        }

        // echo $finalQB->getSQL(); exit; // SHOW SQL

        $sequence = $finalQB->execute()->fetchAll();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($sequence);
    }

    /**
     * Retrieves a sequence of total connections and traffic by date / time.
     *
     * @param UsefulToolsHelper                   $usefulToolsHelper
     * @param User|null                           $user
     * @param EntityManagerInterface              $entityManager
     * @param StatisticsSequenceConnectionsGetDto $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/statistics/sequence/connections', name: 'statistics_sequence_connections', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/statistics/sequence/connections',
        summary: 'Retrieves a sequence of total connections and traffic by date / time',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'sort_order',
                description: 'Sort order. Ascend or descend',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'asc',
                    enum: ['asc', 'desc']
                )
            ),
            new OA\Parameter(
                name: 'period_type',
                description: 'Type of period of the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'today',
                    enum: [
                        'interval',
                        'today',
                        'day_before',
                        'last_7_days',
                        'previous_7_days',
                        'during_month',
                        'during_previous_month',
                        'during_year',
                        'during_previous_year',
                        'all_time',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'interval_begin',
                description: 'A start date and time of the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date-time',
                    example: '2024-02-22 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'interval_end',
                description: 'A date and time when the sequence ends',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date-time',
                    example: '2024-02-23 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'step',
                description: 'Duration of each item in the sequence',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'hour',
                    enum: ['HOUR', 'DAY', 'WEEK', 'MONTH']
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Filter connections by user id. Connections which belong to this user only.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
            new OA\Parameter(
                name: 'server_id',
                description: 'Filter connections by VPN serve id. Connections which belong to this VPN server only.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 567
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'An array of values',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                'unix_time',
                                type: 'integer',
                                example: 1708905600
                            ),
                            new OA\Property(
                                'date_time',
                                type: 'string',
                                format: 'date-time',
                                example: '2024-02-22 10:49:18'
                            ),
                            new OA\Property(
                                'connections',
                                type: 'integer',
                                example: 10000
                            ),
                            new OA\Property(
                                'total_traffic',
                                type: 'number',
                                example: 21
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
    public function statisticsSequenceConnections(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        StatisticsSequenceConnectionsGetDto $dto
    ): JsonResponse {
        $sequence = [];

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('ROUND(AVG(EXTRACT(EPOCH FROM cncts.created))) AS unix_time, SUM(total_traffic) AS total_traffic', 'COUNT(*) AS connections')
            ->from('app.public."vpn_connection"', 'cncts')
        ;
        $unixTimeBegin = 0;
        $unixTimeEnd = 0;
        $stepInSeconds = 0;
        switch ($dto->period_type) {
            case 'interval':
                $queryBuilder->where('cncts.created >= :interval_begin')->setParameter('interval_begin', $dto->interval_begin);
                $queryBuilder->andWhere('cncts.created <= :interval_end')->setParameter('interval_end', $dto->interval_end);

                $unixTimeBegin = strtotime($dto->interval_begin);
                $unixTimeEnd = strtotime($dto->interval_end);

                break;
            case 'all_time':
                $queryBuilder->where('TRUE');

                // Retrieving the date and time of first connection
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM cncts.created)) AS unix_time')
                    ->from('app.public."vpn_connection"', 'cncts')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('cncts.created', 'ASC')
                ;

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('cncts.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                if (!empty($dto->server_id)) {
                    $tmpQB->andWhere('cncts.server_id = :server_id')->setParameter('server_id', $dto->server_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeBegin = $tmpResArr[0]['unix_time'];
                }

                // Retrieving the date and time of last connection
                $tmpQB = $entityManager->getConnection()->createQueryBuilder();
                $tmpQB
                    ->select('ROUND(EXTRACT(EPOCH FROM cncts.created)) AS unix_time')
                    ->from('app.public."vpn_connection"', 'cncts')
                    ->where('TRUE')
                    ->setMaxResults(1)
                    ->orderBy('cncts.created', 'DESC');

                if (!empty($dto->user_id)) {
                    $tmpQB->andWhere('cncts.user_id = :user_id')->setParameter('user_id', $dto->user_id);
                }

                if (!empty($dto->server_id)) {
                    $tmpQB->andWhere('cncts.server_id = :server_id')->setParameter('server_id', $dto->server_id);
                }

                $tmpResArr = $tmpQB->execute()->fetchAll();

                if ($tmpResArr && null != $tmpResArr && \count($tmpResArr) > 0) {
                    $unixTimeEnd = $tmpResArr[0]['unix_time'];
                }

                if (empty($unixTimeBegin) || empty($unixTimeEnd)) {
                    return $usefulToolsHelper->generate_answer('', 'No connections for all time', 'ERROR_STATISTICS_SEQUENCE_CONNECTIONS_3', 404);
                }

                if (empty($dto->step)) {
                    if ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 24 * 30 * 4) { // Months
                        $dto->step = 'MONTH';
                    } elseif ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 24 * 4) { // Days
                        $dto->step = 'DAY';
                    } elseif ($unixTimeEnd - $unixTimeBegin > 60 * 60 * 4) { // Hours
                        $dto->step = 'HOUR';
                    } else {
                        $dto->step = 'MINUTE';
                    }
                }
                break;
            case 'during_previous_year':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'2 YEAR\')')->andWhere('cncts.created <= (NOW() - INTERVAL \'1 YEAR\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * 365;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 365;
                break;
            case 'during_year':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'1 YEAR\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 365;
                break;
            case 'during_previous_month':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'2 MONTH\')')->andWhere('cncts.created <= (NOW() - INTERVAL \'1 MONTH\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * (365 / 12);
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * (365 / 12);
                break;
            case 'during_month':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'1 MONTH\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * (365 / 12);
                break;
            case 'previous_7_days':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'2 WEEK\')')->andWhere('cncts.created <= (NOW() - INTERVAL \'1 WEEK\')');
                $unixTimeEnd = time() - 60 * 60 * 24 * 7;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 7;
                break;
            case 'last_7_days':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'1 WEEK\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24 * 7;
                break;
            case 'day_before':
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'2 DAY\')')->andWhere('cncts.created <= (NOW() - INTERVAL \'1 DAY\')');
                $unixTimeEnd = time() - 60 * 60 * 24;
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24;
                break;
            default:
                $queryBuilder->where('cncts.created >= (NOW() - INTERVAL \'1 DAY\')');
                $unixTimeEnd = time();
                $unixTimeBegin = $unixTimeEnd - 60 * 60 * 24;
                break;
        }

        if (!empty($dto->user_id)) {
            $queryBuilder->andWhere('cncts.user_id = :user_id')->setParameter('user_id', $dto->user_id);
        }

        if (!empty($dto->server_id)) {
            $queryBuilder->andWhere('cncts.server_id = :server_id')->setParameter('server_id', $dto->server_id);
        }

        switch ($dto->step) {
            case 'MINUTE':
                $queryBuilder->groupBy('CONCAT(DATE(cncts.created), DATE_PART(\'HOUR\', cncts.created), DATE_PART(:step, cncts.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60;
                break;
            case 'HOUR':
                $queryBuilder->groupBy('CONCAT(DATE(cncts.created), DATE_PART(:step, cncts.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60;
                break;
            case 'DAY':
                $queryBuilder->groupBy('DATE(cncts.created)');
                $stepInSeconds = 60 * 60 * 24;
                break;
            case 'WEEK':
                $queryBuilder->groupBy('CONCAT(DATE_PART(\'YEAR\', cncts.created), DATE_PART(:step, cncts.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60 * 24 * 7;
                break;
            case 'MONTH':
                $queryBuilder->groupBy('CONCAT(DATE_PART(\'YEAR\', cncts.created), DATE_PART(:step, cncts.created))')->setParameter('step', $dto->step);
                $stepInSeconds = 60 * 60 * 24 * (365 / 12);

                $start_date = new \DateTime(date('Y-m-01 00:00:00', $unixTimeBegin));
                $end_date = new \DateTime(date('Y-m-01 00:00:00', $unixTimeEnd));
                $interval = $start_date->diff($end_date);

                $days = (int) $interval->format('%a');
                $numberOfItems = floor($days / (365 / 12)) + 1;

                for ($j = 0; $j < $numberOfItems; ++$j) {
                    $interval = new \DateInterval('P'.$j.'M');
                    if ('desc' == $dto->sort_order) {
                        $sd = new \DateTime(date('Y-m-01 00:00:00', $unixTimeEnd));
                        $d = date_sub($sd, $interval);
                    } else {
                        $sd = new \DateTime(date('Y-m-01 00:00:00', $unixTimeBegin));
                        $d = date_add($sd, $interval);
                    }

                    $sequence[] = [
                        'unix_time' => $d->format('U'),
                        'date_time' => $d->format('Y-m-d H:i:s'),
                        'connections' => 0,
                        'total_traffic' => 0,
                    ];
                    if (!empty($dto->limit) && $j >= $dto->limit - 1) {
                        break;
                    }
                }
                break;
        }

        // echo "step: '{$dto->step}'\r\n".$queryBuilder->getSQL(); exit; // SHOW SQL

        $results = $queryBuilder->execute()->fetchAll();

        if ('MONTH' != $dto->step) {
            $sequence = [];
            $unixTimeBegin = floor($unixTimeBegin / $stepInSeconds) * $stepInSeconds;
            $unixTimeEnd = (floor($unixTimeEnd / $stepInSeconds) + 1) * $stepInSeconds;
            $numberOfItems = round(($unixTimeEnd - $unixTimeBegin) / $stepInSeconds);

            for ($j = 0; $j < $numberOfItems; ++$j) {
                if ('desc' == $dto->sort_order) {
                    $t = (int) ($unixTimeEnd - $stepInSeconds * $j);
                } else {
                    $t = (int) ($unixTimeBegin + $stepInSeconds * $j);
                }
                $sequence[] = [
                    'unix_time' => $t,
                    'date_time' => date('Y-m-d H:i:s', $t),
                    'connections' => 0,
                    'total_traffic' => 0,
                ];
                if (!empty($dto->limit) && $j >= $dto->limit - 1) {
                    break;
                }
            }
        }

        // Fill in the sequence array with values from database
        for ($j = 0; $j < \count($sequence); ++$j) {
            $t_begin = $sequence[$j]['unix_time'];
            if ($j < \count($sequence) - 1) {
                $t_end = $sequence[$j + 1]['unix_time'];
            } else {
                $t_end = $sequence[$j]['unix_time'] + $stepInSeconds;
            }

            for ($i = 0; $i < \count($results); ++$i) {
                if (null != $results[$i]['unix_time'] && null != $results[$i]['total_traffic'] && $results[$i]['unix_time'] >= $t_begin && $results[$i]['unix_time'] < $t_end) {
                    $sequence[$j]['total_traffic'] = $results[$i]['total_traffic'];
                    $sequence[$j]['connections'] = $results[$i]['connections'];
                    break;
                }
            }
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($sequence);
    }

    /**
     * Retrieves sum of transactions from a period.
     *
     * @param UsefulToolsHelper               $usefulToolsHelper
     * @param User|null                       $user
     * @param EntityManagerInterface          $entityManager
     * @param StatisticsSequenceBillingGetDto $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/statistics/sum/billing', name: 'statistics_sum_billing', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/statistics/sum/billing',
        summary: 'Retrieves sum of transactions from a period',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'currency',
                description: 'Name of currency',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'USD'
                )
            ),
            new OA\Parameter(
                name: 'period_type',
                description: 'Type of period',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'today',
                    enum: [
                        'interval',
                        'today',
                        'day_before',
                        'last_7_days',
                        'previous_7_days',
                        'during_month',
                        'during_previous_month',
                        'during_year',
                        'during_previous_year',
                        'all_time',
                    ]
                )
            ),
            new OA\Parameter(
                name: 'interval_begin',
                description: 'A start date and time',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date-time',
                    example: '2024-02-22 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'interval_end',
                description: 'A date and time when the period ends',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date-time',
                    example: '2024-02-23 10:49:18'
                )
            ),
            new OA\Parameter(
                name: 'tr_type',
                description: 'Filter result by type. List of the transaction types separated by comma.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'add,sub'
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Filter result by user id. Includes values which belong to this user only.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A record, which has a value called `results`, which contains the resulting data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'value',
                            type: 'number',
                            example: 25000000
                        ),
                        new OA\Property(
                            'transactions_total',
                            type: 'integer',
                            example: 21
                        ),
                        new OA\Property(
                            'users_total',
                            type: 'integer',
                            example: 3
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
    public function statisticsSumBilling(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        StatisticsSequenceBillingGetDto $dto
    ): JsonResponse {
        if (empty($dto->sort_by)) {
            $dto->sort_by = 'created';
        }

        $data = ['value' => 0, 'transactions_total' => 0, 'users_total' => 0];

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('SUM(amount) AS value', 'COUNT(*) AS transactions_total')
            ->from('app.public."transaction"', 'trns')
        ;
        $numbOfUsersQB1 = $entityManager->getConnection()->createQueryBuilder();
        $numbOfUsersQB1
            ->select('user_id')
            ->from('app.public."transaction"', 'trns')
            ->where('TRUE')
        ;

        switch ($dto->period_type) {
            case 'interval':
                $queryBuilder->where('trns.created >= :interval_begin')->setParameter('interval_begin', $dto->interval_begin);
                $queryBuilder->andWhere('trns.created <= :interval_end')->setParameter('interval_end', $dto->interval_end);

                $numbOfUsersQB1->where('trns.created >= :interval_begin')->setParameter('interval_begin', $dto->interval_begin);
                $numbOfUsersQB1->andWhere('trns.created <= :interval_end')->setParameter('interval_end', $dto->interval_end);

                break;
            case 'all_time':
                $queryBuilder->where('TRUE');
                break;
            case 'during_previous_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 YEAR\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 YEAR\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'2 YEAR\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 YEAR\')');
                break;
            case 'during_year':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 YEAR\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'1 YEAR\')');
                break;
            case 'during_previous_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 MONTH\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 MONTH\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'2 MONTH\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 MONTH\')');
                break;
            case 'during_month':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 MONTH\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'1 MONTH\')');
                break;
            case 'previous_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 WEEK\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 WEEK\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'2 WEEK\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 WEEK\')');
                break;
            case 'last_7_days':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 WEEK\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'1 WEEK\')');
                break;
            case 'day_before':
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'2 DAY\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 DAY\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'2 DAY\')')->andWhere('trns.created <= (NOW() - INTERVAL \'1 DAY\')');
                break;
            default:
                $queryBuilder->where('trns.created >= (NOW() - INTERVAL \'1 DAY\')');
                $numbOfUsersQB1->where('trns.created >= (NOW() - INTERVAL \'1 DAY\')');
                break;
        }

        $numbOfUsersQB2 = $entityManager->getConnection()->createQueryBuilder();
        $numbOfUsersQB2
            ->select('COUNT(*) AS users_total')
            ->setParameter('interval_begin', $dto->interval_begin)
            ->setParameter('interval_end', $dto->interval_end)
        ;

        if (!empty($dto->currency)) {
            $queryBuilder->andWhere('trns.currency = :currency')->setParameter('currency', $dto->currency);
            $numbOfUsersQB1->andWhere('trns.currency = :currency');
            $numbOfUsersQB2->setParameter('currency', $dto->currency);
        }

        if (!empty($dto->tr_type)) {
            $queryBuilder->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
            $numbOfUsersQB1->andWhere('UPPER(trns.tr_type) IN ('."'".implode("','", explode(',', $usefulToolsHelper->sanitizeString($dto->tr_type)))."'".') ');
        }

        if (!empty($dto->user_id)) {
            $queryBuilder->andWhere('trns.user_id = :user_id')->setParameter('user_id', $dto->user_id);
            $numbOfUsersQB1->andWhere('trns.user_id = :user_id');
            $numbOfUsersQB2->setParameter('user_id', $dto->user_id);
        }

        $numbOfUsersQB2->from('('.$numbOfUsersQB1->groupBy('user_id')->getSQL().')', 'tb1');

        // echo $numbOfUsersQB2->getSQL(); exit; // SHOW SQL

        $results = $queryBuilder->execute()->fetchAll();
        if ($results && \count($results) > 0) {
            $data['value'] = (float) $results[0]['value'];
            $data['transactions_total'] = $results[0]['transactions_total'];
        }

        $results = $numbOfUsersQB2->execute()->fetchAll();
        if ($results && \count($results) > 0) {
            $data['users_total'] = $results[0]['users_total'];
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }
}
