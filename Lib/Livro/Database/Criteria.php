<?php
namespace Livro\Database;

class Criteria
{
    private array $filters;
    private array $properties;
    public function __construct()
    {
        $this->filters = [];
        $this->properties = [];
    }

    public function add(string $variavel, string $compare, mixed $value, string $logic_op = " and")
    {
        if (empty($this->filters)) {
            $logic_op = NULL;
        }
        $this->filters[] = [$variavel, $compare, $this->transform($value), $logic_op];
    }

    public function transform(mixed $value)
    {
        if (is_array($value)) {
             $foo = [];
            foreach ($value as $x) {
                if (is_integer($x)) {
                    $foo[] = $x;
                } elseif (is_string($x)) {
                    $foo[] = "'$x'";
                }
            }
            $result = '(' . implode(',', $foo) . ')';
        }
        elseif(is_string($value)){
            $result = "'$value'";
        }elseif(is_null($value)){
            $result = 'NULL';
        }elseif(is_bool($value)){
            $result = $value ? 'TRUE' : 'FALSE';
        }else{
            $result = $value;
        }
        return $result;
    }

    public function dump() {
        if(is_array($this->filters) and count($this->filters) > 0){
            $result = '';
            foreach($this->filters as $filters){
                $result .= $filters[3] . ' ' . $filters[0]  . ' ' . $filters[1]  . ' ' . $filters[2];
            }
            $result = trim($result);
            return "({$result})";
        }
    }

    public function setProperty(string $property, mixed $value){
        $this->properties[$property] = $value; 
    }

    public function getProperty(string $property){
        if(isset($this->properties[$property])){
            return $this->properties[$property];
        }
    }
}
