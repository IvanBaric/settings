<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use InvalidArgumentException;
use IvanBaric\Settings\Contracts\RegistersSettings;

final class SettingsRegistry
{
    /**
     * @var array<string, SettingsPage>
     */
    private array $pages = [];

    public function registerPage(SettingsPage $page): SettingsPage
    {
        if (array_key_exists($page->name, $this->pages)) {
            throw new InvalidArgumentException("Settings page [{$page->name}] is already registered.");
        }

        $this->pages[$page->name] = $page;

        return $page;
    }

    public function registerRegistrar(RegistersSettings $registrar): void
    {
        $registrar->register($this);
    }

    /**
     * @return array<int, SettingsPage>
     */
    public function pages(): array
    {
        $pages = array_values($this->pages);

        usort($pages, static function (SettingsPage $left, SettingsPage $right): int {
            return [$left->sortOrder, $left->label, $left->name] <=> [$right->sortOrder, $right->label, $right->name];
        });

        return $pages;
    }

    public function page(string $name): ?SettingsPage
    {
        return $this->pages[$name] ?? null;
    }

    /**
     * @return array<int, SettingsPage>
     */
    public function visiblePagesForUser(mixed $user): array
    {
        return array_values(array_filter(
            $this->pages(),
            static fn (SettingsPage $page): bool => $page->visibleTo($user),
        ));
    }
}
