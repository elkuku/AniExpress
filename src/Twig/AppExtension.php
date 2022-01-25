<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\UploaderHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [
                $this,
                'getUploadedAssetPath',
                0,
            ]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('role_name', [$this, 'getRoleName']),
        ];
    }

    public function getRoleName(
        $value
    ): string {
        return array_search($value, User::ROLES, true);
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getPublicPath($path);
    }

    public static function getSubscribedServices(): array
    {
        return [
            UploaderHelper::class,
        ];
    }
}
