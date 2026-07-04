<?php

namespace Livro\Widgets\Base;

/**
 * Base class for all HTML Elements (Customized & Secure)
 *
 * @version    8.5 - Custom
 * @package    widget
 * @subpackage base
 * @author     Pablo Dall'Oglio (Updated with Output Buffering Interception)
 */
class TElement
{
    private string $tagname;      // tag name
    private array $properties;    // tag properties
    private bool $wrapped;
    private bool $useLineBreaks;
    private bool $useSingleQuotes;
    private ?object $beforeElement; // El '?' significa que puede ser un objeto o null
    private ?object $afterElement;  // Puede ser objeto o null
    protected array $children;
    private static ?array $voidelements = null;
    private bool $hidden;
    private bool $isVoidElement;

    /**
     * Class Constructor
     * @param $tagname  tag name
     */
    public function __construct(string $tagname)
    {
        // define the element name
        $this->tagname = $tagname;
        $this->useLineBreaks = TRUE;
        $this->useSingleQuotes = FALSE;
        $this->wrapped = FALSE;
        $this->hidden = FALSE;
        $this->properties = [];
        $this->isVoidElement = FALSE;
        $this->children = [];

        if (empty(self::$voidelements)) {
            self::$voidelements = array(
                'area',
                'base',
                'br',
                'col',
                'command',
                'embed',
                'hr',
                'img',
                'input',
                'keygen',
                'link',
                'meta',
                'param',
                'source',
                'track',
                'wbr'
            );
        }
    }

    /**
     * Turn element into a void element
     */
    public function enableVoidElement()
    {
        $this->isVoidElement = TRUE;
    }

    /**
     * Create an element
     */
    public static function tag(string $tagname, mixed $value, $attributes = NULL)
    {
        $object = new TElement($tagname);

        if (is_array($value)) {
            foreach ($value as $element) {
                $object->add($element);
            }
        } else {
            $object->add($value);
        }

        if ($attributes) {
            foreach ($attributes as $att_name => $att_value) {
                $object->$att_name = $att_value;
            }
        }

        return $object;
    }

    /**
     * hide object
     */
    public function hide()
    {
        $this->hidden = true;
    }

    /**
     * Insert element before
     */
    public function before(object $element)
    {
        $this->beforeElement = $element;
    }

    /**
     * Insert element after
     */
    public function after(object $element)
    {
        $this->afterElement = $element;
    }

    public function getBeforeElement()
    {
        return $this->beforeElement;
    }

    public function getAfterElement()
    {
        return $this->afterElement;
    }

    public function setName(string $tagname)
    {
        $this->tagname = $tagname;
    }

    public function getName()
    {
        return $this->tagname;
    }

    public function setIsWrapped(bool $bool)
    {
        $this->wrapped = $bool;
    }

    public function getIsWrapped()
    {
        return $this->wrapped;
    }

    public function setProperty(string $name, mixed $value)
    {
        if (is_scalar($value)) {
            $this->properties[$name] = $value;
        }
    }

    public function setProperties(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (is_null($value)) {
                unset($this->properties[$property]);
            } else {
                $this->properties[$property] = $value;
            }
        }
    }

    public function getProperty(string $name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function __set(string $name, mixed $value)
    {
        if (is_scalar($value)) {
            $this->properties[$name] = $value;
        }
    }

    public function __unset(string $name)
    {
        unset($this->properties[$name]);
    }

    public function __get(string $name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
    }

    public function __isset(string $name)
    {
        return isset($this->properties[$name]);
    }

    public function __clone()
    {
        if ($this->children) {
            foreach ($this->children as $key => $child) {
                if (is_object($child)) {
                    $this->children[$key] = clone $child;
                } else {
                    $this->children[$key] = $child;
                }
            }
        }
    }

    public function add(mixed $child)
    {
        $this->children[] = $child;
        if ($child instanceof TElement) {
            $child->setIsWrapped(TRUE);
        }
    }

    public function addMany(object $children)
    {
        if ($children) {
            foreach ($children as $child) {
                $this->add($child);
            }
        }
    }

    public function insert(int $position, mixed $child)
    {
        array_splice($this->children, $position, 0, array($child));
        if ($child instanceof TElement) {
            $child->setIsWrapped(TRUE);
        }
    }

    public function setUseLineBreaks(bool $linebreaks)
    {
        $this->useLineBreaks = $linebreaks;
    }

    public function setUseSingleQuotes(bool $singlequotes)
    {
        $this->useSingleQuotes = $singlequotes;
    }

    public function del(object $object)
    {
        foreach ($this->children as $key => $child) {
            if ($child === $object) {
                unset($this->children[$key]);
            }
        }
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function find(string $element, ?array $properties = null)
    {
        $return_list = [];

        if ($this->children) {
            foreach ($this->children as $child) {
                if ($child instanceof TElement) {
                    if ($child->getName() == $element) {
                        $match = true;
                        if ($properties) {
                            foreach ($properties as $key => $value) {
                                if ($child->getProperty($key) !== $value) {
                                    $match = false;
                                }
                            }
                        }

                        if (empty($child->getProperties())) {
                            $match = false;
                        }

                        if ($match) {
                            return [$child];
                        }
                    }

                    $return_list = array_merge($return_list, $child->find($element, $properties));
                }
            }
        }
        return $return_list;
    }

    public function get(int $position)
    {
        return $this->children[$position];
    }

    public function openTag()
    {
        echo "<{$this->tagname}";
        if ($this->properties) {
            foreach ($this->properties as $name => $value) {
                if ($this->useSingleQuotes) {
                    $value = str_replace("'", '&#039;', $value);
                    echo " {$name}='{$value}'";
                } else {
                    $value = str_replace('"', '&quot;', $value);
                    echo " {$name}=\"{$value}\"";
                }
            }
        }

        if (in_array($this->tagname, self::$voidelements) || $this->isVoidElement) {
            echo '/>';
        } else {
            echo '>';
        }
    }

    public function open()
    {
        $this->openTag();
    }

    /**
     * MEJORA CENTRALIZADA: Modificamos show() para interceptar wrappers de forma segura
     */
    public function show()
    {
        if ($this->hidden) {
            return;
        }

        if (!empty($this->beforeElement)) {
            $this->beforeElement->show();
        }

        $this->openTag();

        if ($this->children) {
            if (count($this->children) > 1) {
                if ($this->useLineBreaks) {
                    echo "\n";
                }
            }

            foreach ($this->children as $child) {
                if ($child instanceof self) {
                    $child->setUseLineBreaks($this->useLineBreaks);
                }

                if (is_object($child)) {
                    // Si es un decorator antiguo que usa show() (ej: tu FormWrapper)
                    if (method_exists($child, 'show') && !($child instanceof self)) {
                        $child->show();
                    }
                    // Si implementa __toString nativo
                    elseif (method_exists($child, '__toString')) {
                        echo $child->__toString();
                    } else {
                        $child->show();
                    }
                } else if ((is_string($child)) or (is_numeric($child))) {
                    echo $child;
                }
            }
        }

        if (!in_array($this->tagname, self::$voidelements)) {
            $this->closeTag();
        }

        if (!empty($this->afterElement)) {
            $this->afterElement->show();
        }
    }

    public function closeTag()
    {
        echo "</{$this->tagname}>";
        if ($this->useLineBreaks) {
            echo "\n";
        }
    }

    public function close()
    {
        $this->closeTag();
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getContents()
    {
        ob_start();
        $this->show();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function clearChildren()
    {
        $this->children = array();
    }
}
