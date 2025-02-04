<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\EnumType;
use CuyZ\Valinor\Type\ScalarType;

use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\NativeEnumType;
use function assert;

/** @internal */
final class ScalarNodeBuilder implements NodeBuilder
{
    public function __construct(private bool $enableFlexibleCasting)
    {
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ScalarType);

        // The flexible mode is always active for enum types, as it makes no
        // sense not to activate it in the strict mode: a scalar value is always
        // wanted as input.
        $canCast = $type->canCast($value);

        if ($this->enableFlexibleCasting && $type instanceof NativeEnumType && ! $canCast && $type->isNullable()) {
            return TreeNode::leaf($shell->withValue(null), null, true);
        }

        if ((! $this->enableFlexibleCasting && ! $type instanceof EnumType) || ! $canCast) {
            throw $type->errorMessage();
        }

        return TreeNode::leaf($shell, $type->cast($value));
    }
}
