<?php

namespace Livro\Widgets\Wrapper;

use Livro\Widgets\Base\TElement;
use Livro\Widgets\Container\TNotebook;

/**
 * BootstrapNotebookWrapper - Decorador para aplicar estilos Bootstrap al TNotebook
 */
class BootstrapNotebookWrapper
{
    private TNotebook $decorated;
    private array $properties = [];
    private string $direction = '';
    private array $divisions = [2, 10];
    private string $wrapper_class;
    private string $tab_class = 'tabs';
    
    /**
     * El constructor recibe el TNotebook puro
     */
    public function __construct(TNotebook $notebook, string $wrapper_class = '')
    {
        $this->decorated = $notebook;
        $this->wrapper_class = $wrapper_class;
    }
    
    /**
     * Cambia el diseño de pestañas a formato de botones (Pills)
     */
    public function enablePills(): void
    {
        $this->tab_class = 'pills';
    }
    
    /**
     * Captura las asignaciones de propiedades mágicas y las guarda internamente
     */
    public function __set(string $property, mixed $value)
    {
        $this->properties[$property] = $value;
    }
    
    /**
     * Redirecciona las llamadas a métodos directamente al TNotebook (como appendPage)
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->decorated, $method], $parameters);
    }
    
    /**
     * Configura pestañas verticales (a la izquierda o a la derecha)
     */
    public function setTabsDirection(string $direction, ?array $divisions = null): void
    {
        if ($direction) {
            $this->direction = 'tabs-' . $direction;
            if ($divisions) {
                $this->divisions = $divisions;
            }
        } 
    }
    
    /**
     * Modifica el HTML generado por el TNotebook y lo dibuja estilizado
     */
    public function show(): void
    {
        /** @var TElement $rendered */
        $rendered = $this->decorated->render();
        $rendered->{'role'} = 'tabpanel';
        
        // Definimos las clases principales del contenedor envolvente
        $rendered->{'class'} = 'tabwrapper card shadow-sm p-3 bg-white ' . $this->wrapper_class . ' ' . $this->tab_class;
        
        // Aplicamos propiedades mágicas que se hayan asignado externamente
        foreach ($this->properties as $property => $value) {
            $rendered->$property = $value;
        }
        
        $tabs = null;
        $panel = null;
        
        // Inspeccionamos los hijos
        $sessions = $rendered->getChildren();
        
        if ($sessions) {
            foreach ($sessions as $section) {
                if ($section instanceof TElement) {
                    // Modificamos la botonera superior (UL)
                    if ($section->{'class'} == 'nav nav-tabs') {
                        $section->{'class'} = "nav nav-" . $this->tab_class . ' ' . $this->direction;
                        
                        if ($this->direction) {
                            $section->{'class'} .= " flex-column nav-pills me-3"; 
                        }
                        $section->{'role'} = "tablist";
                        $tabs = $section;
                    }
                    
                    // Ocultamos espaciadores innecesarios en Bootstrap
                    if ($section->{'class'} == 'spacer') {
                        $section->{'style'} = "display:none";
                    }
                    
                    // Modificamos el contenedor de contenidos (DIV con clase tab-content)
                    if ($section->{'class'} == 'tab-content') {
                        $panel = $section;
                    }
                }
            }
        }
        
        // Si las pestañas van a la IZQUIERDA
        if ($this->direction == 'tabs-left' && $tabs && $panel) {
            $rendered->clearChildren();
            
            $left_pack  = TElement::tag('div', '', ['class' => 'left-pack col-md-' . $this->divisions[0], 'style' => 'padding:0']);
            $right_pack = TElement::tag('div', '', ['class' => 'right-pack col-md-' . $this->divisions[1], 'style' => 'padding-right:0; margin-right:0']);
            
            $rendered->add($left_pack);
            $rendered->add($right_pack);
            
            $left_pack->add($tabs);
            $right_pack->add($panel);
        }
        // Si las pestañas van a la DERECHA
        else if ($this->direction == 'tabs-right' && $tabs && $panel) {
            $rendered->clearChildren();
            
            $left_pack  = TElement::tag('div', '', ['class' => 'left-pack col-md-' . $this->divisions[1]]);
            $right_pack = TElement::tag('div', '', ['class' => 'right-pack col-md-' . $this->divisions[0]]);
            
            $rendered->add($left_pack);
            $rendered->add($right_pack);
            
            $left_pack->add($panel);
            $right_pack->add($tabs);
        }
        
        // Si hay dirección vertical, forzamos un display flex en la raíz del componente
        if (!empty($this->direction)) {
            $rendered->{'style'} .= '; display: flex; align-items: flex-start;';
        }

        // Invocamos la salida del elemento de forma definitiva
        $rendered->show();
    }
}