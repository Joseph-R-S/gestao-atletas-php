<?php
namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

/**
 * Representa um rótulo de texto
 * @author Pablo Dall'Oglio
 */
class Label extends Field implements FormElementInterface
{
    private Element $tag;
    
    /**
     * Construtor
     * @param $value text label
     */
    public function __construct(mixed $value)
    {
        // set the label's content
        $this->setValue($value);
        
        // create a new element
        $this->tag = new Element('label');
        
    }
    
    /**
     * Adiciona conteúdo no label
     */
    public function add(mixed $child)
    {
        $this->tag->add($child);
    }
    
    /**
     * Exibe o widget
     */
    public function show() :void
    {
        $this->tag->add($this->value);
        $this->tag->show();
    }
}
