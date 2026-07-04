<?php

namespace Livro\Widgets\Form;

abstract class Field implements FormElementInterface
{
    protected string $name;
    //protected mixed $size = '100%'; // Cambiado a mixed o string para soportar "100%", "200px", etc.
    protected mixed $value = null;   // Inicializado en null para evitar error de inicialización
    protected bool $editable = true;
    protected string $formLabel = ''; // Inicializado como string vacío
    protected array $properties = [];

    public function __construct(string $name)
    {
        $this->setEditable(true);
        $this->setName($name);
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setLabel(string $label): void {
        $this->formLabel = $label;
    }

    public function getLabel(): string {
        return $this->formLabel;
    }

    public function setValue(mixed $value): void {
        $this->value = $value;
    }

    public function getValue(): mixed {
        return $this->value;
    }

    public function setEditable(bool $editable): void {
        $this->editable = $editable;
    }

    public function getEditable(): bool {
        return $this->editable;
    }

    public function setProperty(string $name, mixed $value): void {
        $this->properties[$name] = $value;
    }

    public function getProperty(string $name): mixed {
        // Evitamos un "Undefined array key" si la propiedad no existe
        return $this->properties[$name] ?? null;
    }

    // Métodos mágicos para interceptar propiedades HTML dinámicas (class, id, style, etc.)
    public function __set(string $name, mixed $value): void
    {
        if (is_scalar($value)) {
            $this->setProperty($name, $value);
        }
    }

    public function __get(string $name): mixed
    {
        return $this->getProperty($name);
    }


    public function setSize(mixed $width, ?string $height = null): void {
        $this->size = $width;
    }

    public function getSize(): mixed {
        return $this->size;
    }
}
