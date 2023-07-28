<?php

namespace Smart\SonataBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Smart\SonataBundle\Entity\Parameter;
use Smart\SonataBundle\Repository\ParameterRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
abstract class AbstractWebTestCase extends WebTestCase
{
    private ?EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        // @phpstan-ignore-next-line
        $this->client = self::createClient();
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        // https://github.com/liip/LiipTestFixturesBundle/blob/2.x/UPGRADE-2.0.md
        // @phpstan-ignore-next-line
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        // Empty load to guarantee that the base will always be available
        $this->loadFixtureFiles([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // avoid memory leaks
        if ($this->entityManager != null) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    protected function getFixtureDir(): string
    {
        return __DIR__ . '/fixtures';
    }

    protected function getParameterRepository(): ParameterRepository
    {
        /** @var ParameterRepository $parameterRepository */
        $parameterRepository = $this->entityManager->getRepository(Parameter::class);
        return $parameterRepository;
    }

    protected function loadFixtureFiles(array $files): void
    {
        // @phpstan-ignore-next-line
        $this->databaseTool->loadAliceFixture($files);
    }
}
