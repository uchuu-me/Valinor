<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

use function sys_get_temp_dir;

final class MapperBuilderTest extends TestCase
{
    private MapperBuilder $mapperBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperBuilder = new MapperBuilder();
    }

    public function test_builder_methods_return_clone_of_builder_instance(): void
    {
        $builderA = $this->mapperBuilder;
        $builderB = $builderA->infer(DateTimeInterface::class, static fn () => DateTime::class);
        $builderC = $builderA->bind(static fn (): DateTime => new DateTime());
        $builderD = $builderA->registerConstructor(static fn (): stdClass => new stdClass());
        $builderE = $builderA->alter(static fn (string $value): string => 'foo');
        $builderF = $builderA->flexible();
        $builderG = $builderA->enableFlexibleCasting();
        $builderH = $builderA->allowSuperfluousKeys();
        $builderI = $builderA->allowPermissiveTypes();
        $builderJ = $builderA->filterExceptions(fn () => new FakeErrorMessage());
        $builderK = $builderA->withCache(new FakeCache());

        self::assertNotSame($builderA, $builderB);
        self::assertNotSame($builderA, $builderC);
        self::assertNotSame($builderA, $builderD);
        self::assertNotSame($builderA, $builderE);
        self::assertNotSame($builderA, $builderF);
        self::assertNotSame($builderA, $builderG);
        self::assertNotSame($builderA, $builderH);
        self::assertNotSame($builderA, $builderI);
        self::assertNotSame($builderA, $builderJ);
        self::assertNotSame($builderA, $builderK);
    }

    public function test_mapper_instance_is_the_same(): void
    {
        self::assertSame($this->mapperBuilder->mapper(), $this->mapperBuilder->mapper());
    }
}
