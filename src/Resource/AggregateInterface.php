<?php

namespace Smarty2\Resource;

use Smarty2\Resource\ResourceInterface;
use IteratorAggregate;

interface AggregateInterface extends IteratorAggregate
{
        function register(string $type, ResourceInterface $resource) : self;
        function unregister(string $type) : self;
        function hasType(string $type) : bool;
        function getType(string $type) : ?ResourceInterface;
}
