<?php

namespace Transmission\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Transmission\Formatter;

/**
 * AbstractModel.
 */
class AbstractModel extends Collection
{
    /**
     * Cast attributes to appropriate types.
     *
     * @var bool
     */
    protected $castingEnabled = false;

    /**
     * The attributes that should be cast to native or other supported types.
     *
     * Casts only when enabled and attributes provided.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * {@inheritdoc}
     *
     * @param null|bool $castingEnabled
     */
    public function get($key, $default = null, $castingEnabled = null)
    {
        $value = parent::get($key, $default);

        return $this->castAttribute($key, $value, $castingEnabled ?? $this->castingEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $this->castAllAttributes();

        return parent::toArray();
    }

    /**
     * Enable casting attributes globally.
     *
     * @param bool $castingEnabled Default enable casting.
     *
     * @return $this
     */
    public function enableCasting($castingEnabled = true): self
    {
        $this->castingEnabled = $castingEnabled;

        return $this;
    }

    /**
     * Cast All Attributes.
     */
    protected function castAllAttributes(): void
    {
        $this->transform(function ($value, $key) {
            return $this->castAttribute($key, $value, $this->castingEnabled);
        });
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $castingEnabled
     *
     * @return mixed
     */
    protected function castAttribute(string $key, $value, bool $castingEnabled = false)
    {
        if ($castingEnabled && array_key_exists($key, $this->casts)) {
            return Formatter::castAttribute($this->casts[$key], $value);
        }

        return $value;
    }

    /**
     * Magic method to get attributes dynamically.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $attribute = Str::camel(Str::after($method, 'get'));
        if (!Str::startsWith($method, 'get') || !$this->has($attribute)) {
            throw new \BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $castingEnabled = $arguments[0] ?? $this->castingEnabled;
        $value = parent::get($attribute);

        return $this->castAttribute($attribute, $value, $castingEnabled);
    }
}
