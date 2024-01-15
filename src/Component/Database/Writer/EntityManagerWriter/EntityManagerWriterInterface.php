<?php

namespace Kaa\Component\Database\Writer\EntityManagerWriter;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
interface EntityManagerWriterInterface
{
    public function write(): void;
}
