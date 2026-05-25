<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Immutable ordered collection of {@see Shortcut} instances.
 *
 * Sorting by `Shortcut::$order` happens once at construction; iteration is
 * deterministic. Used by the discoverer pipeline and by the manifest builder.
 *
 * @implements IteratorAggregate<int, Shortcut>
 */
final class ShortcutCollection implements Countable, IteratorAggregate
{
    /** @var list<Shortcut> */
    private readonly array $items;

    /** @param iterable<Shortcut> $shortcuts */
    public function __construct(iterable $shortcuts = [])
    {
        $items = is_array($shortcuts)
            ? array_values($shortcuts)
            : iterator_to_array($shortcuts, preserve_keys: false);

        usort($items, fn (Shortcut $a, Shortcut $b) => $a->order <=> $b->order);

        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /** @return list<array{name: string, url: string, icons?: list<array<string, string>>}> */
    public function toManifestArray(): array
    {
        return array_map(fn (Shortcut $s) => $s->toManifestArray(), $this->items);
    }
}
