<?php

namespace Livro\Widgets\Wrapper;

use Livro\Widgets\Container\Panel;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Base\Element;

class DatagridWrapper
{
    private Datagrid $decorated;
    public function __construct(Datagrid $datagrid)
    {
        $this->decorated = $datagrid;
    }

    //chama sempre que o metodo não existe
    //redireciona para decorated
    public function __call(string $method, array $parameters): mixed
    {
        return call_user_func_array([$this->decorated, $method], $parameters);
    }

    public function __set(string $atibute, mixed $value): void
    {
        $this->decorated->$atibute = $value;
    }

public function show(): void
    {
        // 1. Instanciamos la tabla tal como lo tenías
        $element = new Element('table');
        $element->class = 'table table-striped table-hover';
        //class=""
        $thead = new Element('thead');
        $element->add($thead);

        $this->createHeaders($thead);

        $tbody = new Element('tbody');
        $element->add($tbody);

        $items = $this->decorated->getItems();

        foreach ($items as $item) {
            $this->createItem($tbody, $item);
        }

        $responsiveDiv = new Element('div');
        $responsiveDiv->class = 'table-responsive';
        
        // Metemos la tabla adentro del div responsivo
        $responsiveDiv->add($element);

        // código final modificado: Metemos el DIV (y no la tabla directo) adentro del panel
        $panel = new Panel;
        $panel->add($responsiveDiv); // <-- Aquí cambiamos $element por $responsiveDiv
        $panel->show();
    }

    public function createHeaders(Element $thead): void
    {
        $row = new Element('tr');
        $thead->add($row);

        $actions = $this->decorated->getActions();
        $columns = $this->decorated->getColumns();

        if ($actions) {
            foreach ($actions as $action) {
                $cell = new Element('th');
                $cell->width = '40px';
                $row->add($cell); // Agrega las celdas de acciones a la fila
            }
        }

        if ($columns) {
            foreach ($columns as $column) {
                $label = $column->getLabel();
                $align = $column->getAlign();
                $width = $column->getWidth();

                $cell = new Element('th');
                $cell->add($label);
                $cell->style = "text-align:$align";
                $cell->width = $width;

                // CORREGIDO: Agrega cada celda de columna a la fila principal del thead
                $row->add($cell);

                if ($column->getAction()) {
                    $url = $column->getAction()->serialize();
                    $cell->onclick = "document.location='$url'";
                }
            }
        }
    }

    public function createItem(object $tbody, object $item): void
    {
        $row = new Element('tr'); // acrecente uma fila do body;
        $tbody->add($row); // adiciona a fila ao bodu da tabla

        //oara saber se tem action
        $actions = $this->decorated->getActions();
        $columns = $this->decorated->getColumns();

        //cria uma ação na frente da linha
        if ($actions) {
            //percorre cada uma das actions
            foreach ($actions as $action) {
                //para cada action tem que criar uma celula com o botão de ação
                $url = $action['action']->serialize();
                $label = $action['label'];
                $image = $action['imagem'];
                $field = $action['field'];

                $key = $item->$field;

                //criu um elemento de ancora
                $link = new Element('a');
                //monto uma url
                $link->href = "$url&key=$key&$field=$key";

                //casso passe uma imagem
                if ($image) {
                    $i = new Element('i');
                    $i->class = $image;
                    $i->title = $label;
                    $i->add('');
                    $link->add($i);
                } else {
                    $link->add($label);
                }

                //crio uma nova columna 
                $element = new Element('td');
                //adiciona o link dentro da columna
                $element->add($link);
                $element->align = 'center';

                //adiciona la columna en la fila
                $row->add($element);
            }
        }

        //cada columna é um objeto de classe DatagridColumn
        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                $align = $column->getAlign();
                $width = $column->getWidth();
                $function = $column->getTransformer();

                $data = $item->$name;

                if ($function) {
                    $data = call_user_func($function, $data);
                }
                $element = new Element('td');
                $element->add($data);
                $element->align = $align;
                $element->width = $width;

                $row->add($element);
            }
        }
    }
}
