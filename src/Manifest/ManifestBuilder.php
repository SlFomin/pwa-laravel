<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest;

use JsonSerializable;
use SlFomin\PwaLaravel\Exceptions\InvalidManifestException;

final class ManifestBuilder implements JsonSerializable
{
    /** @var array<string, mixed> */
    public private(set) array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data = []): self
    {
        $instance = new self;
        $instance->data = $data;

        return $instance;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;

        return $this;
    }

    public function shortName(string $value): self
    {
        $this->data['short_name'] = $value;

        return $this;
    }

    public function description(string $value): self
    {
        $this->data['description'] = $value;

        return $this;
    }

    public function startUrl(string $value): self
    {
        $this->data['start_url'] = $value;

        return $this;
    }

    public function scope(string $value): self
    {
        $this->data['scope'] = $value;

        return $this;
    }

    public function display(string $value): self
    {
        $this->data['display'] = $value;

        return $this;
    }

    public function themeColor(string $value): self
    {
        $this->data['theme_color'] = $value;

        return $this;
    }

    public function backgroundColor(string $value): self
    {
        $this->data['background_color'] = $value;

        return $this;
    }

    public function orientation(string $value): self
    {
        $this->data['orientation'] = $value;

        return $this;
    }

    public function lang(string $value): self
    {
        $this->data['lang'] = $value;

        return $this;
    }

    public function addIcon(
        string $src,
        string $sizes,
        string $type = 'image/png',
        ?string $purpose = null,
    ): self {
        $icon = compact('src', 'sizes', 'type');
        if ($purpose !== null) {
            $icon['purpose'] = $purpose;
        }
        $this->data['icons'][] = $icon;

        return $this;
    }

    /**
     * @param  list<array<string, mixed>>  $icons
     */
    public function icons(array $icons): self
    {
        $this->data['icons'] = $icons;

        return $this;
    }

    /**
     * @param  list<array{name: string, short_name?: string, url: string, icons?: list<array<string, mixed>>}>  $shortcuts
     */
    public function shortcuts(array $shortcuts): self
    {
        $this->data['shortcuts'] = $shortcuts;

        return $this;
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function merge(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->validate()->toArray();
    }

    public function validate(): self
    {
        foreach (['name', 'short_name', 'start_url', 'display'] as $field) {
            if (empty($this->data[$field])) {
                throw new InvalidManifestException("Manifest field '{$field}' is required.");
            }
        }

        $allowedDisplay = ['fullscreen', 'standalone', 'minimal-ui', 'browser'];
        if (! in_array($this->data['display'], $allowedDisplay, true)) {
            throw new InvalidManifestException(
                "Invalid display value: {$this->data['display']}. Allowed: ".implode(', ', $allowedDisplay)
            );
        }

        if (isset($this->data['theme_color']) && ! $this->isValidColor($this->data['theme_color'])) {
            throw new InvalidManifestException("Invalid theme_color: {$this->data['theme_color']}");
        }

        return $this;
    }

    public function toJson(int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): string
    {
        $json = json_encode($this->validate()->toArray(), $flags);
        if ($json === false) {
            throw new InvalidManifestException('Manifest JSON encoding failed: '.json_last_error_msg());
        }

        return $json;
    }

    private function isValidColor(string $value): bool
    {
        return (bool) preg_match('/^(#[0-9a-fA-F]{3,8}|rgb\(.+\)|rgba\(.+\)|hsl\(.+\)|[a-z]+)$/i', $value);
    }
}
