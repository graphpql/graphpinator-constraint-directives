<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MimeTypeConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Mime type constraint was not satisfied.';
}
