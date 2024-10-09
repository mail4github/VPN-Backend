<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Message\AddClient;
use App\Message\CheckCredentials;
use App\Message\DeployServer;
use App\Message\RevokeClient;
use App\Message\TestMsg;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as MessengerSerializerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ExternalJsonMessageSerializer implements MessengerSerializerInterface
{
    private const STAMP_HEADER_PREFIX = 'X-Message-Stamp-';

    public function __construct(private SerializerInterface $serializer, private array $context = [])
    {
        $this->context = $context + [Serializer::MESSENGER_SERIALIZATION_CONTEXT => true];
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        throw new \Exception('Transport & Serializer not meant for receiving messages');
    }

    public function encode(Envelope $envelope): array
    {
        $body = $this->serializer->serialize($envelope->getMessage(), 'json');

        $envelope = $envelope->withoutStampsOfType(NonSendableStampInterface::class);

        $encodedStamps = $this->encodeStamps($envelope);

        $headers = ['type' => $this->getMessageType($envelope)] + $encodedStamps + ['Content-Type' => 'application/json'];

        return ['headers' => $headers, 'body' => $body];
    }

    private function getMessageType(Envelope $envelope): string
    {
        $message = $envelope->getMessage();

        return match ($message::class) {
            AddClient::class => 'add_client',
            CheckCredentials::class => 'check',
            DeployServer::class => 'deploy',
            RevokeClient::class => 'revoke_client',
            TestMsg::class => 'test',
            default => 'unknown'
        };
    }

    private function encodeStamps(Envelope $envelope): array
    {
        if (!$allStamps = $envelope->all()) {
            return [];
        }

        $headers = [];
        foreach ($allStamps as $class => $stamps) {
            $headers[self::STAMP_HEADER_PREFIX.$class] = $this->serializer->serialize($stamps, 'json', $this->context);
        }

        return $headers;
    }
}
