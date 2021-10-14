<?php

namespace Smarty2\Resource;

use Smarty2\Resource\AggregateInterface;
use Smarty2\Resource\ResourceInterface;
use Generator;

use function array_filter;

class Aggregate implements AggregateInterface
{
        /**
        * @var ResourceInterface[]
        */
        protected array $resources = [];

        function __construct(array $resources = [])
        {
                $this->resources = array_filter(
                        $resources,
                        static function ($resource)
                        {
                                return ($resource instanceof ResourceInterface);
                        });
        }

        function register(string $type, ResourceInterface $resource) : self
        {
                $this->unregister($type);
		$this->resources[ $type ] = $resource;

                return $this;
        }

        function unregister(string $type) : self
        {
                unset( $this->resources[$type] );
                return $this;
        }

        function hasType(string $type) : bool
        {
                return !empty($this->resources[ $type ]);
        }

        function getType(string $type) : ?ResourceInterface
        {
                return $this->resources[ $type ] ?? null;
        }

        function getIterator(): Generator
        {
                yield from $this->resources;
        }
}
