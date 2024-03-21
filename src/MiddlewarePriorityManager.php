<?php

declare(strict_types=1);

namespace Laniakea\MiddlewarePriority;

use Illuminate\Foundation\Configuration\Middleware;

readonly class MiddlewarePriorityManager
{
    public function __construct(private Middleware $middleware)
    {
        //
    }

    /**
     * Get current priority list.
     *
     * @return array
     */
    public function getPriority(): array
    {
        return $this->middleware->getMiddlewarePriority();
    }

    /**
     * Create a new instance with default Laravel middleware priority.
     *
     * @param Middleware $middleware
     * @param array|null $defaults   = null
     *
     * @return static
     */
    public static function withDefaults(Middleware $middleware, ?array $defaults = null): static
    {
        $middleware->priority($defaults ?? DefaultMiddlewarePriority::get());

        return new static($middleware);
    }

    /**
     * Prepend given middleware to the priority list.
     *
     * @param string|array $middleware
     *
     * @return $this
     */
    public function prepend(string|array $middleware): static
    {
        $this->middleware->priority([
            ...$this->getMiddlewareList($middleware),
            ...$this->getFreshPriority(),
        ]);

        return $this;
    }

    /**
     * Append given middleware to the priority list.
     *
     * @param string|array $middleware
     *
     * @return $this
     */
    public function append(string|array $middleware): static
    {
        $this->middleware->priority([
            ...$this->getFreshPriority(),
            ...$this->getMiddlewareList($middleware),
        ]);

        return $this;
    }

    /**
     * Insert given middleware to the priority list before specific middleware.
     *
     * @param string       $before
     * @param string|array $middleware
     *
     * @throws \InvalidArgumentException If middleware to search for was not found in the priority list.
     *
     * @return $this
     */
    public function before(string $before, string|array $middleware): static
    {
        $priority = $this->addToPriority(
            $this->getFreshPriority(),
            $before,
            $middleware,
            0,
        );

        $this->middleware->priority($priority);

        return $this;
    }

    /**
     * Insert given middleware to the priority list after specific middleware.
     *
     * @param string       $after
     * @param string|array $middleware
     *
     * @throws \InvalidArgumentException If middleware to search for was not found in the priority list.
     *
     * @return $this
     */
    public function after(string $after, string|array $middleware): static
    {
        $priority = $this->addToPriority(
            $this->getFreshPriority(),
            $after,
            $middleware,
            1,
        );

        $this->middleware->priority($priority);

        return $this;
    }

    /**
     * Swap positions of two middleware in the priority list.
     *
     * @param string $what
     * @param string $with
     *
     * @throws \InvalidArgumentException If one of the given middleware was not found in the priority list.
     *
     * @return $this
     */
    public function swap(string $what, string $with): static
    {
        $priority = $this->getFreshPriority();
        $whatIndex = $this->getMiddlewareIndex($priority, $what);
        $withIndex = $this->getMiddlewareIndex($priority, $with);

        $priority[$whatIndex] = $with;
        $priority[$withIndex] = $what;

        $this->middleware->priority($priority);

        return $this;
    }

    /**
     * Remove given middleware from the priority list.
     *
     * @param string|array $what
     *
     * @throws \InvalidArgumentException If middleware to search for was not found in the priority list.
     *
     * @return $this
     */
    public function remove(string|array $what): static
    {
        $priority = $this->getFreshPriority();
        $list = $this->getMiddlewareList($what);

        foreach ($list as $middleware) {
            $index = $this->getMiddlewareIndex($priority, $middleware);
            unset($priority[$index]);
        }

        $this->middleware->priority(array_values($priority));

        return $this;
    }

    /**
     * Add middleware to the priority list at specific position.
     *
     * @param array        $priority
     * @param string       $search
     * @param string|array $middleware
     * @param int          $spliceOffset
     *
     * @throws \InvalidArgumentException If middleware to search for was not found in the priority list.
     *
     * @return array
     */
    protected function addToPriority(array $priority, string $search, string|array $middleware, int $spliceOffset): array
    {
        $index = $this->getMiddlewareIndex($priority, $search);

        array_splice($priority, $index + $spliceOffset, 0, $this->getMiddlewareList($middleware));

        return $priority;
    }

    /**
     * Find middleware index in the priority list.
     *
     * @param array  $priority
     * @param string $search
     *
     * @throws \InvalidArgumentException If middleware to search for was not found in the priority list.
     *
     * @return int
     */
    protected function getMiddlewareIndex(array $priority, string $search): int
    {
        $index = array_search($search, $priority);

        if ($index === false) {
            throw new \InvalidArgumentException(
                'Middleware ['.$search.'] was not found in the priority list. You need to append or prepend it first.'
            );
        }

        return $index;
    }

    /**
     * Get fresh copy of current priority list.
     *
     * @return array
     */
    protected function getFreshPriority(): array
    {
        return [...$this->middleware->getMiddlewarePriority()];
    }

    /**
     * Transform middleware to array if it is not already.
     *
     * @param string|array $middleware
     *
     * @return string[]
     */
    protected function getMiddlewareList(string|array $middleware): array
    {
        return is_array($middleware) ? $middleware : [$middleware];
    }
}
