<?php

declare(strict_types=1);

namespace App\Dto\Response;

use JMS\Serializer\Annotation as Serialization;

class VpnServerResponseDto
{
    /**
     * @Serialization\Type("int")
     */
    public int $id;

    /**
     * @Serialization\Type("string")
     */
    public string $country;

    /**
     * @SerializationType("string")
     */
    public string $IP;

    /**
     * @SerializationType("string")
     */
    public string $user_name;

    /**
     * @SerializationType("string")
     */
    public string $password;

    /**
     * @SerializationType("string")
     */
    public string $wallet_address;

    /**
     * @SerializationType("string")
     */
    public string $created;

    /**
     * @SerializationType("string")
     */
    public string $modified;

    /**
     * @SerializationType("int")
     */
    public int $connection_quality;

    /**
     * @SerializationType("int")
     */
    public int $created_by;

    /**
     * @SerializationType("boolean")
     */
    public bool $for_free;

    /**
     * @SerializationType("float")
     */
    public float $price;

    /**
     * @SerializationType("string")
     */
    public string $protocol;

    /**
     * @SerializationType("boolean")
     */
    public bool $residential_ip;

    /**
     * @SerializationType("float")
     */
    public float $service_commission;

    /**
     * @SerializationType("integer")
     */
    public int $maximum_active_connections;

    /**
     * @SerializationType("array")
     */
    public array $user;

    /**
     * @SerializationType("boolean")
     */
    public bool $traffic_vs_period;

    /**
     * @SerializationType("boolean")
     */
    public bool $nodus_vpn;

    /**
     * @SerializationType("boolean")
     */
    public bool $new_server;

    /**
     * @SerializationType("float")
     */
    public float $server_load;

    /**
     * @SerializationType("string")
     */
    public string $test_packages;

    /**
     * @SerializationType("string")
     */
    public string $paid_packages;
}
