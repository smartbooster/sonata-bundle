<?php

namespace Smart\SonataBundle\Route;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function Symfony\Component\String\u;

final class RouteLoader extends Loader
{
    public const SMART_DOCUMENTATION_ROUTE_PREFIX = 'smart_sonata_documentation_md_';

    private ?string $projectDir;

    public function __construct(?string $projectDir = null)
    {
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    /**
     * To enable the routes, add the following code to your /config/routes.yaml :
     * _smart_sonata:
     *     resource: .
     *     type:     smart_sonata
     *     host:     "admin.%domain%"
     */
    public function supports(mixed $resource, string $type = null): bool
    {
        return 'smart_sonata' === $type;
    }

    public function load(mixed $resource, string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        // MDT Dynamic routes generation foreach markdown files
        $docNamePrefix = self::SMART_DOCUMENTATION_ROUTE_PREFIX;
        $docPathPrefix = '/documentation/';
        $docControllerPath = 'Smart\SonataBundle\Controller\Admin\DocumentationController::renderMarkdown';
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->projectDir . '/documentation')) {
            $directoryFinder = new Finder();
            foreach ($directoryFinder->directories()->in($this->projectDir . '/documentation')->sortByName(true) as $directory) {
                $directoryName = $directory->getFilename();
                $separator = strpos($directoryName, '-');
                if ($separator !== false) {
                    $directoryPath = substr($directoryName, $separator + 1);
                } else {
                    $directoryPath = $directoryName;
                }

                $mdFinder = new Finder();
                $mdFinder->files()->in($this->projectDir . '/documentation/' . $directoryName)->name('*.md')->sortByName(true);
                foreach ($mdFinder as $file) {
                    $filename = $file->getFilename();
                    $separator = strpos($filename, '-');
                    if ($separator !== false) {
                        $filename = substr($filename, $separator + 1);
                    }
                    $filename = u($filename)->replace('.md', '');

                    $collection->add(
                        $docNamePrefix . u($directoryPath)->snake()->toString() . '_' . $filename->snake()->toString(),
                        new Route($docPathPrefix . $directoryPath . DIRECTORY_SEPARATOR . $filename->toString(), [
                            '_controller' => $docControllerPath,
                        ])
                    );
                }
            }
        }

        $collection->add(
            'smart_sonata_documentation_email',
            new Route('/documentation/email', [
                '_controller' => 'Smart\SonataBundle\Controller\Admin\DocumentationController::email',
            ], [], [], '', [], ['GET', 'POST'])
        );

        return $collection;
    }
}
