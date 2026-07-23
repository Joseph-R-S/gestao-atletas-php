<?php

namespace Livro\Widgets\Datagrid;

use Exception;
use Livro\Widgets\Base\Element;

class PageNavigation
{
    private $limit;
    private $count;
    private $order;
    private $page;
    private $first_page;
    private $action;
    private $width;
    private $direction;
    private $hidden;
    private $resume;

    public function __construct()
    {
        $this->hidden = false;
        $this->resume = false;
    }

    public function hide()
    {
        $this->hidden = true;
    }

    public function enableCounters()
    {
        $this->resume = true;
    }

    // 🌟 MÉTODOS DE COMPATIBILIDAD AÑADIDOS
    public function setProperty($name, $value)
    {
        if ($name == 'limit') {
            $this->setLimit($value);
        }
    }

    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setCount($count)
    {
        $this->count = (int) $count;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setPage($page)
    {
        $this->page = (int) $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setFirstPage($first_page)
    {
        $this->first_page = (int) $first_page;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    public function setProperties($properties)
    {
        $order      = isset($properties['order'])   ? addslashes($properties['order'])  : '';
        $page       = isset($properties['page'])    ? $properties['page']   : 1;
        $direction  = (isset($properties['direction']) and in_array($properties['direction'], array('asc', 'desc')))  ? $properties['direction']   : NULL;
        $first_page = isset($properties['first_page']) ? $properties['first_page'] : 1;

        $this->setOrder($order);
        $this->setPage($page);
        $this->setDirection($direction);
        $this->setFirstPage($first_page);
    }

    public function setAction($action)
    {
        $this->action = $action;
    }
    public function __toString()
    {
        ob_start();
        $this->show();
        return ob_get_clean();
    }

    /**
     * Retorna el texto de resumen de registros (Ej: "1 a 10 de 50 registros")
     */
    private function getResume(): string
    {
        if (!$this->getCount()) {
            return 'Nenhum registro encontrado';
        }

        $limit = $this->getLimit() > 0 ? $this->getLimit() : 10;
        $page  = $this->getPage() > 0 ? $this->getPage() : 1;

        $min = (($limit * ($page - 1)) + 1);
        $max = min(($limit * $page), $this->getCount());
        $total = $this->getCount();

        return "{$min} a {$max} de {$total} registros";
    }

    public function show()
    {
        if ($this->hidden) {
            return;
        }

        if ($this->resume) {
            $total = new Element('div');
            $total->{'class'} = 'tpagenavigation_resume text-center my-1';
            $total->add($this->getResume());
            $total->show();
        }

        $first_page = isset($this->first_page) ? $this->first_page : 1;
        $direction  = 'asc';
        $page_size  = !empty($this->limit) ? $this->limit : 10;
        $max = 10;
        $registros = $this->count ?? 0;

        if ($page_size > 0) {
            // 1. Calculamos el total real de páginas usando ceil()
            $pages = (int) ceil($registros / $page_size);
        } else {
            $pages = 1;
        }

        // Si solo hay 1 página o ninguna, no mostramos navegación
        if ($pages <= 1) {
            return;
        }

        $last_page = min($pages, $max);

        $nav = new Element('nav');
        $nav->{'class'} = 'tpagenavigation';
        $nav->{'align'} = 'center';

        $ul = new Element('ul');
        $ul->{'class'} = 'pagination';
        $ul->{'style'} = 'display:inline-flex;';
        $nav->add($ul);

        // Botón "Primera" y "Anterior"
        if ($first_page > 1) {
            $item = new Element('li');
            $link = new Element('a');
            $span = new Element('span');
            $link->{'aria-label'} = 'Previous';
            $ul->add($item);
            $item->add($link);
            $link->add($span);
            $this->action->setParameter('offset', 0);
            $this->action->setParameter('limit',  $page_size);
            $this->action->setParameter('direction', $this->direction);
            $this->action->setParameter('page',   1);
            $this->action->setParameter('first_page', 1);
            $this->action->setParameter('order', $this->order);

            $link->{'class'}     = "page-link";
            $link->{'href'}      = $this->action->serialize();
            $span->add(Element::tag('span', '', ['class' => 'fa fa-angle-double-left']));

            $item = new Element('li');
            $link = new Element('a');
            $span = new Element('span');
            $link->{'aria-label'} = 'Previous';
            $ul->add($item);
            $item->add($link);
            $link->add($span);
            $this->action->setParameter('offset', ($first_page - $max - 1) * $page_size);
            $this->action->setParameter('limit',  $page_size);
            $this->action->setParameter('direction', $this->direction);
            $this->action->setParameter('page',   $first_page - $max);
            $this->action->setParameter('first_page', $first_page - $max);
            $this->action->setParameter('order', $this->order);

            $link->{'class'}     = "page-link";
            $link->{'href'}      = $this->action->serialize();
            $link->{'generator'} = 'adianti';
            $span->add(Element::tag('span', '', ['class' => 'fa fa-angle-left']));
        }

        // 2. Dibuja SOLO las páginas existentes (sin rellenar con estáticas)
        for ($n = $first_page; $n <= $last_page; $n++) {
            $offset = ($n - 1) * $page_size;
            $item = new Element('li');
            $link = new Element('a');
            $span = new Element('span');
            $ul->add($item);
            $item->add($link);
            $link->add($span);
            $span->add($n);

            $this->action->setParameter('offset', $offset);
            $this->action->setParameter('limit',  $page_size);
            $this->action->setParameter('direction', $this->direction);
            $this->action->setParameter('page',   $n);
            $this->action->setParameter('first_page', $first_page);
            $this->action->setParameter('order', $this->order);

            $link->{'href'}      = $this->action->serialize();
            $link->{'class'}     = 'page-link';

            if ($this->page == $n) {
                $item->{'class'} = 'active page-item';
            } else {
                $item->{'class'} = 'page-item';
            }
        }

        // Botón "Siguiente" y "Última" (Solo si hay más de 10 páginas en total)
        if ($pages > $max && ($first_page + $max - 1) < $pages) {
            $first_page = $n;
            $item = new Element('li');
            $link = new Element('a');
            $span = new Element('span');
            $link->{'aria-label'} = "Next";
            $ul->add($item);
            $item->add($link);
            $link->add($span);
            $this->action->setParameter('offset', ($n - 1) * $page_size);
            $this->action->setParameter('limit',   $page_size);
            $this->action->setParameter('direction', $this->direction);
            $this->action->setParameter('page',    $n);
            $this->action->setParameter('first_page', $n);
            $this->action->setParameter('order', $this->order);
            $link->{'class'}     = "page-link";
            $link->{'href'}      = $this->action->serialize();
            $link->{'generator'} = 'adianti';
            $span->add(Element::tag('span', '', ['class' => 'fa fa-angle-right']));

            $item = new Element('li');
            $link = new Element('a');
            $span = new Element('span');
            $link->{'aria-label'} = "Next";
            $ul->add($item);
            $item->add($link);
            $link->add($span);
            $this->action->setParameter('offset', ($pages - 1) * $page_size);
            $this->action->setParameter('limit',   $page_size);
            $this->action->setParameter('direction', $this->direction);
            $this->action->setParameter('page',    $pages);
            $this->action->setParameter('first_page', (int) floor($pages / $max) * $max + 1);
            $this->action->setParameter('order', $this->order);
            $link->{'class'}     = "page-link";
            $link->{'href'}      = $this->action->serialize();
            $span->add(Element::tag('span', '', ['class' => 'fa fa-angle-double-right']));
        }

        $nav->show();
    }
}
