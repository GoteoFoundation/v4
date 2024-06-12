<?php

namespace App\Entity;

use App\Repository\SystemVariableRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stores values referenced across different system-level services.\
 * Works like environment variables but are stored at database level.
 *
 * @see src\DependencyInjection\SystemVariablesLoader.php
 */
#[UniqueEntity(fields: ['name'], message: 'A system var with that name already exists.')]
#[ORM\Entity(repositoryClass: SystemVariableRepository::class)]
class SystemVariable
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank()]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = strtoupper($name);

        return $this;
    }

    public static function seralizeValue(mixed $value): string
    {
        if (\is_numeric($value)) {
            if (\is_float($value) || \preg_match('/\./', $value)) {
                $float = (float) $value;

                return "float:$float";
            }

            return "int:$value";
        }

        if (\is_bool($value) && true === $value) {
            return 'bool:true';
        }

        if (\is_bool($value) && false === $value) {
            return 'bool:false';
        }

        if (\is_null($value)) {
            return 'null:null';
        }

        $lowercase = \strtolower($value);

        if (\in_array($lowercase, ['true', 'yes'])) {
            return 'bool:true';
        }

        if (\in_array($lowercase, ['false', 'no'])) {
            return 'bool:false';
        }

        if (\in_array($lowercase, ['null', 'none'])) {
            return 'null:null';
        }

        return "str:$value";
    }

    public static function unserializeValue(string $value): mixed
    {
        \preg_match('/[a-z]+\:/', $value, $colons);
        if (empty($colons) || !\in_array($colons[0], ['int:', 'float:', 'bool:', 'null:', 'str:'])) {
            throw new \Exception('The given value was not serialized as a SystemVar value');
        }

        $marker = $colons[0];
        $serialized = \join(':', \array_slice(explode(':', $value), 1));

        switch ($marker) {
            case 'str:':
                return $serialized;
            case 'null:':
                return null;
            case 'bool:':
                return filter_var($serialized, FILTER_VALIDATE_BOOLEAN);
            case 'float:':
                return floatval($serialized);
            case 'int:':
                return intval($serialized);
        }
    }

    public function getValue(): ?string
    {
        return self::unserializeValue($this->value);
    }

    public function setValue(?string $value): static
    {
        $this->value = self::seralizeValue($value);

        return $this;
    }
}
