<?php

namespace Livro\Widgets\Form;

interface FormElementInterface{
    public function setName(string $name) : void;
    public function getName() : string ;
    public function setValue(mixed $value) : void ;
    public function getValue() : mixed ;
    public function setSize(mixed $width, ?string $height = null): void; 
    public function setLabel(string $label): void;                       
    public function getLabel(): string;
    public function show(): void;
}
?>