<?php
namespace Livro\Widgets\Container;

use Livro\Widgets\Base\Element;

/**
 * Vertical Box
 *
 * @version    8.5
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license
 */
class TVBox extends Element
{
    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->{'class'} = 'd-inline-block col-md-12';
    }
    
    /**
     * Add an child element
     * @param $child Any object that implements the show() method
     */
    public function add(mixed $child)
    {
        $wrapper = new Element('div');
        $wrapper->{'class'} = 'clear-both';
        $wrapper->add($child);
        parent::add($wrapper);
        return $wrapper;
    }
    
    /**
     * Add a new col with many cells
     * @param $cells Each argument is a row cell
     */
    public function addColSet()
    {
        $args = func_get_args();
        if ($args)
        {
            foreach ($args as $arg)
            {
                $this->add($arg);
            }
        }
    }
    
    /**
     * Static method for pack content
     * @param $cells Each argument is a cell
     */
    public static function pack()
    {
        $box = new self;
        $args = func_get_args();
        if ($args)
        {
            foreach ($args as $arg)
            {
                $box->add($arg);
            }
        }
        return $box;
    }
}
