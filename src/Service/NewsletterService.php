<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\NewsletterHtmlDto;
use App\Dto\NewsletterTextDto;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnv;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NewsletterService
{
    public function __construct(
        protected MailerInterface $mailer,
        protected TwigEnv $twig,
        protected TemplateService $uploads
    ) {}

    /**
     * @param NewsletterHtmlDto $dto
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     */
    public function sendTemplate(NewsletterHtmlDto $dto): void
    {
        $email = (new Email())
            ->to($dto->recipient)
            ->subject($dto->subject)
            ->html($this->uploads->render($dto->templateName, $dto->getParams()));
        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendText(NewsletterTextDto $dto): void
    {
        $email = (new Email())
            ->to($dto->recipient)
            ->subject($dto->subject)
            ->text($dto->text);
        $this->mailer->send($email);
    }
}
