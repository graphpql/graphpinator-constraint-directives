<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class UniqueConstraintOnlyScalar extends ConstraintSettingsError
{
    public const MESSAGE = 'Unique constraint supports only scalars.';
}
