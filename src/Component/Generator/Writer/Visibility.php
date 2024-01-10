<?php

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\PhpOnly;
use Nette\PhpGenerator\ClassLike;

#[PhpOnly]
enum Visibility: string
{
    case Public = ClassLike::VisibilityPublic;

    case Protected = ClassLike::VisibilityProtected;

    case Private = ClassLike::VisibilityPrivate;
}
