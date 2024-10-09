<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StatisticsSequenceConnectionsGetDto
{
    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    #[Assert\Regex(pattern: '/^interval$|^today$|^day_before$|^last_7_days$|^previous_7_days$|^during_month$|^during_previous_month$|^during_year$|^during_previous_year$|^all_time$/')]
    public ?string $period_type = null;

    #[Assert\NotBlank]
    #[Assert\DateTime(message: 'This value is not a valid datetime. Must be: Y-m-d H:i:s')]
    public ?string $interval_begin = null;

    #[Assert\NotBlank]
    #[Assert\DateTime(message: 'This value is not a valid datetime. Must be: Y-m-d H:i:s')]
    public ?string $interval_end = null;

    #[Assert\Regex(pattern: '/^HOUR$|^DAY$|^WEEK$|^MONTH$/')]
    public ?string $step = null;

    public ?int $user_id = null;

    public ?int $server_id = null;

    public ?int $limit = null; // "limit" Return the `limit` rows maximum

    public function __construct(
        string $sort_order = 'asc',
        string $period_type = 'today',
        ?string $interval_begin = null,
        ?string $interval_end = null,
        string $step = '',
        ?int $user_id = null,
        ?int $server_id = null,
        int $limit = 0
    ) {
        $this->sort_order = $sort_order;
        $this->period_type = $period_type;
        if ('interval' != $period_type) {
            $interval_begin = '2024-04-26 00:00:00';
            $interval_end = '2024-04-26 00:00:00';
        }
        $this->interval_begin = $interval_begin;
        $this->interval_end = $interval_end;
        if (empty($step)) {
            switch ($this->period_type) {
                case 'interval':
                    if (strtotime($this->interval_end) - strtotime($this->interval_begin) > 60 * 60 * 24 * 30 * 4) { // 4 Months
                        $this->step = 'MONTH';
                    } elseif (strtotime($this->interval_end) - strtotime($this->interval_begin) > 60 * 60 * 24 * 4) { // 4 Days
                        $this->step = 'DAY';
                    } elseif (strtotime($this->interval_end) - strtotime($this->interval_begin) > 60 * 60 * 4) { // 4 Hours
                        $this->step = 'HOUR';
                    } else {
                        $this->step = 'MINUTE';
                    }
                    break;
                case 'during_previous_year':
                    $this->step = 'MONTH';
                    break;
                case 'during_year':
                    $this->step = 'MONTH';
                    break;
                case 'during_previous_month':
                    $this->step = 'DAY';
                    break;
                case 'during_month':
                    $this->step = 'DAY';
                    break;
                case 'previous_7_days':
                    $this->step = 'DAY';
                    break;
                case 'last_7_days':
                    $this->step = 'DAY';
                    break;
                case 'day_before':
                    $this->step = 'HOUR';
                    break;
                default:
                    $this->step = 'HOUR';
                    break;
            }
        } else {
            $this->step = $step;
        }
        $this->user_id = $user_id;
        $this->server_id = $server_id;
        $this->limit = $limit;
    }
}
