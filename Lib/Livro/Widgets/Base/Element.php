<?php
namespace Livro\Widgets\Base; 

class Element {
    protected string $tagname;
    protected array $properties = [];
    protected array $children = [];
    
    public function __construct(string $tagname) {
        $this->tagname = $tagname;
    }

    public static function tag($tagname, $value, $attributes = NULL)
    {
        $object = new Element($tagname);
        
        if (is_array($value))
        {
            foreach ($value as $element)
            {
                $object->add($element);
            }
        }
        else
        {
            $object->add($value);
        }
        
        if ($attributes)
        {
            foreach ($attributes as $att_name => $att_value)
            {
                $object->$att_name = $att_value;
            }
        }
        
        return $object;
    }

    public function __set(string $name, mixed $value) {
        $this->properties[$name] = $value;
    }

    public function __get(string $name) {
        return $this->properties[$name] ?? null;
    }

    // Adiciona um elemento filho (pode ser outro Element, string ou número)
    public function add(mixed $child) {
        $this->children[] = $child;
    }

    // Em vez de dar echo direto, geramos a string. É muito mais seguro!
    private function open(): string {
        $html = "<{$this->tagname}";
        if ($this->properties) {
            foreach ($this->properties as $name => $value) {
                if (is_scalar($value)) {
                    $html .= " {$name}=\"{$value}\"";
                }
            }
        }
        $html .= ">";
        return $html;
    }

    private function close(): string {
        return "</{$this->tagname}>\n"; // Aspas duplas corrigidas!
    }

    // Método mágico que transforma o objeto em string automaticamente se necessário
// Método mágico que transforma o objeto em string automaticamente se necessário
    public function __toString(): string {
        $html = $this->open() . "\n";
        
        if ($this->children) {
            foreach ($this->children as $child) {
                if (is_object($child)) {
                    // 1. Si el objeto tiene __toString() (como otros Elements anidados)
                    if (method_exists($child, '__toString')) {
                        $html .= $child->__toString();
                    // 2. Si el objeto es un decorador y tiene show() (como tu FormWrapper)
                    } elseif (method_exists($child, 'show')) {
                        // Usamos almacenamiento en búfer de salida (Output Buffering)
                        // para capturar los echos de show() y guardarlos en el string sin romper el flujo
                        ob_start();
                        $child->show();
                        $html .= ob_get_clean();
                    }
                } elseif (is_string($child) || is_numeric($child)) {
                    $html .= $child;
                }
            }
        }
        
        $html .= $this->close();
        return $html;
    }

    // Mantém o método show() que você já usa, mas usando a nova lógica centralizada
    public function show() {
        echo $this->__toString();
    }
}