<?php

namespace Kaa\Component\Database\EntityManager;

use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\Exception\DatabaseException;

/**
 * This code is heavily inspired by the Doctrine Project
 */
class DnfSort
{
    private const NOT_VISITED = 1;
    private const VISITED = 2;
    private const IN_PROGRESS = 3;

    /**
     * Array of all nodes, indexed by object ids.
     *
     * @var array<string, EntityInterface>
     */
    private array $nodes = [];

    /**
     * DFS state for the different nodes, indexed by node object id and using one of
     * this class' constants as value.
     *
     * @var array<string, int>
     */
    private array $states = [];

    /**
     * Edges between the nodes. The first-level key is the object id of the outgoing
     * node; the second array maps the destination node by object id as key. The final
     * boolean value indicates whether the edge is optional or not.
     *
     * @var array<string, string[]>
     */
    private array $edges = [];

    /**
     * Builds up the result during the DFS.
     *
     * @var EntityInterface[]
     */
    private array $sortResult = [];

    public function addNode(EntityInterface $node): void
    {
        $this->nodes[$node->_getOid()] = $node;
        $this->states[$node->_getOid()] = self::NOT_VISITED;
        $this->edges[$node->_getOid()] = [];
    }

    public function addEdge(string $from, string $to): void
    {
        if (in_array($to, $this->edges[$from], true)) {
            return;
        }

        $this->edges[$from][] = $to;
    }

    /**
     * @return EntityInterface[]
     */
    public function sort(): array
    {
        foreach (array_keys($this->nodes) as $oid) {
            if ($this->states[$oid] === self::NOT_VISITED) {
                $this->visit($oid);
            }
        }

        return $this->sortResult;
    }

    /**
     * @throws DatabaseException
     */
    private function visit(string $oid): void
    {
        if ($this->states[$oid] === self::IN_PROGRESS) {
            throw new DatabaseException('Could not persist entities because of a cycle');
        }

        if ($this->states[$oid] === self::VISITED) {
            return;
        }

        $this->states[$oid] = self::IN_PROGRESS;

        // Continue the DFS downwards the edge list
        foreach ($this->edges[$oid] as $adjacentId) {
            $this->visit($adjacentId);
        }

        // We have traversed all edges and visited all other nodes reachable from here.
        // So we're done with this vertex as well.

        $this->states[$oid] = self::VISITED;
        $this->sortResult[] = $this->nodes[$oid];
    }
}
