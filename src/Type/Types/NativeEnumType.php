<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\EnumType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use Stringable;
use UnitEnum;

use function array_keys;
use function array_map;
use function assert;

/** @internal */
final class NativeEnumType implements EnumType
{
    /** @var array<string, UnitEnum> */
    private array $cases;

    private bool $nullable = false;

    public function __construct(
        /** @var class-string<UnitEnum> */
        private string $enumName
    ) {
    }

    /**
     * @return class-string<UnitEnum>
     */
    public function className(): string
    {
        return $this->enumName;
    }

    public function generics(): array
    {
        return [];
    }

    public function accepts(mixed $value): bool
    {
        return $value instanceof $this->enumName;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $other->enumName === $this->enumName;
        }

        return $other instanceof UndefinedObjectType
            || $other instanceof MixedType;
    }

    public function canCast(mixed $value): bool
    {
        if ($value instanceof Stringable) {
            $value = (string)$value;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return isset($this->cases()[(string)$value]);
    }

    public function cast(mixed $value): UnitEnum
    {
        assert($this->canCast($value));

        return $this->cases()[(string)$value]; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        $cases = array_map(
            fn ($case) => ValueDumper::dump($case),
            array_keys($this->cases())
        );

        return MessageBuilder::newError('Value {source_value} does not match any of {allowed_values}.')
            ->withParameter('allowed_values', implode(', ', $cases))
            ->build();
    }

    public function readableSignature(): string
    {
        return implode('|', array_keys($this->cases()));
    }

    public function toString(): string
    {
        return $this->enumName;
    }

    /**
     * @return array<string, UnitEnum>
     */
    private function cases(): array
    {
        // @infection-ignore-all
        return $this->cases ??= (function () {
            $cases = [];

            foreach (($this->enumName)::cases() as $case) {
                /** @var UnitEnum $case */
                $cases[$case instanceof BackedEnum ? (string)$case->value : $case->name] = $case;
            }

            return $cases;
        })();
    }

    public function setNullable(bool $nullable)
    {
        $this->nullable = $nullable;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
