# GraPHPinator Constraint directives [![PHP](https://github.com/infinityloop-dev/graphpinator-constraint-directives/workflows/PHP/badge.svg?branch=master)](https://github.com/infinityloop-dev/graphpinator-constraint-directives/actions?query=workflow%3APHP) [![codecov](https://codecov.io/gh/infinityloop-dev/graphpinator-constraint-directives/branch/master/graph/badge.svg)](https://codecov.io/gh/infinityloop-dev/graphpinator-constraint-directives)

:zap::globe_with_meridians::zap: Typesystem directives to declare additional validation on top of GraphQL type system.

## Introduction

This package allows server to declare additional constraints on accepted values for arguments, fields and input fields. It is also possible for client to declare constraints for variables in request document.

Additional benefit of using constraint directives is that expected values are displayed to client using GraphQL type language in a self-documenting manner.

## Installation

Install package using composer

```composer require infinityloop-dev/graphpinator-constraint-directives```

## How to use

In order to enable constraint directives on your server, the only thing you need to do is to put selected directives to your `Container`. To avoid cyclic dependencies `ConstraintDirectiveAccessor` must be implemented. This step should be automated when using DI solution.

Here is example configuration for Nette DI:
```neon
- Graphpinator\ConstraintDirectives\StringConstraintDirective
- Graphpinator\ConstraintDirectives\IntConstraintDirective
- Graphpinator\ConstraintDirectives\FloatConstraintDirective
- Graphpinator\ConstraintDirectives\ListConstraintDirective
- Graphpinator\ConstraintDirectives\ObjectConstraintDirective
- Graphpinator\ConstraintDirectives\ListConstraintInput
- Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor(
    string: Graphpinator\ConstraintDirectives\StringConstraintDirective
    int: Graphpinator\ConstraintDirectives\IntConstraintDirective
    float: Graphpinator\ConstraintDirectives\FloatConstraintDirective
    list: Graphpinator\ConstraintDirectives\ListConstraintDirective
    object: Graphpinator\ConstraintDirectives\ObjectConstraintDirective
    listInput: Graphpinator\ConstraintDirectives\ListConstraintInput
)
```

### Add constraint to Argument

The most common usage of constraint directives is to validate input from client without having to do it yourself in resolve function.

```php
$intConstraint; // instance of \Graphpinator\ConstraintDirectives\IntConstraintDirective

\Graphpinator\Argument\Argument::create(
    'year'
    \Graphpinator\Container\Container::Int(),
)->addDirective(
    $intConstraint,
    ['min' => 1900, 'max' => 2021],
);
```

### Add constraint to Field

Additional usage of constraint directives is to validate output from your resolve functions.

```php
$intConstraint; // instance of \Graphpinator\ConstraintDirectives\IntConstraintDirective

\Graphpinator\Field\Field::create(
    'year'
    \Graphpinator\Container\Container::Int(),
)->addDirective(
    $intConstraint,
    ['min' => 1900, 'max' => 2021],
);
```

### Add constraint to Type & Interface & InputType

Special case is `ObjectConstraint` which declares additional information on which fields must be filled. It is a flexible solution to the input-union problem, but can also be applied on Interface/Type to semantically indicate which values are returned.

```php
class DogOrCatInput extends \Graphpinator\Type\InputType
{
    protected const NAME = 'DogOrCatInput';

    public funtion __construct(
        \Graphpinator\ConstraintDirectives\ObjectConstraintDirective $objectConstraint,
    )
    {
        parent::__construct();
        $this->addDirective($this->objectConstraint, ['exactlyOne' => ['dog', 'cat']]);
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('dog', \Graphpinator\Container\Container::String()),
            \Graphpinator\Argument\Argument::create('cat', \Graphpinator\Container\Container::String()),
        ]);
    }
}
```

### Variance

Question of variance comes into play, because field, argument and object constraints can be declared in an interface context and then implemented by concrete type. Traditional rules apply here.

- Covariance for Field constraints - child can restrict parent's constraint, but may not release it.
- Contravariance for Argument constraints - child can soften parent's constraint, but may not restrict it.
- Invariance for Object constraints - child must contain the same constraint as parent.


### Directive options

- `@stringConstraint`
    - minLength
    - maxLength
    - regex
    - oneOf
- `@intConstraint` & `@floatConstraint`
    - min
    - max
    - oneOf
- `@listConstraint`
    - minItems
    - maxItems
    - unique
    - innerList (object with the same arguments to apply constraint to inner list)
- `@uploadConstraint`
    - maxSize
    - mimeType
- `@objectConstraint`
    - atLeastOne
    - exactlyOne
