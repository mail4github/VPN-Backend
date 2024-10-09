<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as TwigEnv;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TemplateService
{
    public const EXT = '.html.twig';
    public const UPLOAD_DIR = '/var/www/vpn.sixhands.co/templates';

    protected string $uploadsDir;

    public function __construct(
        protected Filesystem $fs,
        protected ParameterBagInterface $params,
        protected TwigEnv $twig,
    ) {
        $this->uploadsDir = self::UPLOAD_DIR;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function read(string $name): string
    {
        $name = $this->getFileName($name);
        if (!$this->fs->exists($name)) {
            throw new NotFoundHttpException(sprintf('template (%s) not found', $name));
        }
        if (($data = file_get_contents($name)) !== false) {
            return $data;
        }
        throw new LockedHttpException('template file is busy');
    }

    /**
     * @param string $name
     * @param string $html
     *
     * @return void
     */
    public function replace(string $name, string $html): void
    {
        $this->fs->dumpFile($this->getFileName($name), $html);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function delete(string $name): void
    {
        $name = $this->getFileName($name);
        if ($this->fs->exists($name)) {
            $this->fs->remove($this->getFileName($name));
        }
    }

    /**
     * @param string                $name
     * @param array<string, string> $params
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function render(string $name, array $params): string
    {
        return $this->twig->render($this->getFileName($name), $params);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getFileName(string $name): string
    {
        return Path::join($this->uploadsDir, $name.static::EXT);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function realName(string $name): string
    {
        return str_replace('-', '/', $name);
    }
}
