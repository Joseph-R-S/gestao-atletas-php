<?php

namespace Livro\Widgets\Container;

use Livro\Widgets\Base\Element;

/**
 * TNotebook - Contenedor lógico de pestañas dinámicas (Tabs)
 */
class TNotebook extends Element
{
    private array $pages = [];
    private string $id;
    private array $size = ['100%', 'auto'];
    private int $currentPage = 0;
    private array $tabActions = [];
    private bool $tabsVisible = true;
    private bool $tabsSensible = true;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        // Inicializa el contenedor principal como un DIV
        parent::__construct('div');
        $this->id = 'tnotebook_' . uniqid();
    }

    /**
     * Retorna el ID único del componente
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Configura el tamaño del contenedor (Ancho, Alto)
     */
    public function setSize(mixed $width, $height = 'auto'): void
    {
        $this->size = [$width, $height];
    }

    /**
     * Retorna un array con el ancho y alto configurados
     */
    public function getSize(): array
    {
        return $this->size;
    }

    /**
     * Agrega una nueva página/pestaña al Notebook
     * @param string $title Título visible de la pestaña
     * @param Element $content Componente contenedor (ej: TVBox)
     */
    public function appendPage(string $title, mixed $content): void
    {
        $page_id = 'page_' . uniqid();

        $this->pages[] = [
            'title'   => $title,
            'content' => $content,
            'id'      => $page_id
        ];
    }

    /**
     * Retorna la cantidad total de páginas agregadas
     */
    public function getPageCount(): int
    {
        return count($this->pages);
    }

    /**
     * Define cuál pestaña estará activa por defecto (índice basado en 0)
     */
    public function setCurrentPage(int $index): void
    {
        $this->currentPage = $index;
    }

    /**
     * Retorna el índice de la pestaña activa actual
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Permite asociar una acción de JavaScript o servidor al hacer clic en una pestaña
     */
    public function setTabAction(int $index, $action): void
    {
        $this->tabActions[$index] = $action;
    }

    /**
     * Controla la visibilidad de la botonera de pestañas
     */
    public function setTabsVisibility(bool $visible): void
    {
        $this->tabsVisible = $visible;
    }

    /**
     * Controla si las pestañas responden a los clics (Habilitado/Deshabilitado)
     */
    public function setTabsSensibility(bool $sensible): void
    {
        $this->tabsSensible = $sensible;
    }

    /**
     * Construye la estructura interna de etiquetas HTML (Estructura base)
     */
    public function render()
    {
        if (empty($this->pages)) {
            return $this;
        }

        // Aplicamos el tamaño configurado al contenedor principal
        $this->{'style'} = "width: {$this->size[0]}; height: {$this->size[1]};";

        // 1. Crear la botonera de pestañas (Nav Tabs)
        $nav = new Element('ul');
        $nav->class = 'nav nav-tabs';
        $nav->id = $this->id;
        $nav->role = 'tablist';

        // Ocultar las pestañas físicamente si setTabsVisibility(false) fue invocado
        if (!$this->tabsVisible) {
            $nav->{'style'} = 'display: none !important;';
        }

        // 2. Crear el contenedor de contenidos (Tab Content)
        $tabContent = new Element('div');
        $tabContent->class = 'tab-content';

        // 3. Recorrer y estructurar las páginas
        foreach ($this->pages as $index => $page) {
            // Evaluamos cuál pestaña debe marcarse como activa
            $isActive = ($index === $this->currentPage);

            // --- Estructura del botón (LI > BUTTON) ---
            $li = new Element('li');
            $li->class = 'nav-item';
            $li->role = 'presentation';

            $button = new Element('button');
            $button->class = 'nav-link' . ($isActive ? ' active' : '');
            $button->id = $page['id'] . '-tab';
            $button->{'data-bs-toggle'} = 'tab';
            $button->{'data-bs-target'} = '#' . $page['id'];
            $button->type = 'button';
            $button->role = 'tab';
            $button->aria_controls = $page['id'];
            $button->aria_selected = $isActive ? 'true' : 'false';
            
            // Si setTabsSensibility(false), deshabilitamos los botones
            if (!$this->tabsSensible) {
                $button->{'disabled'} = 'disabled';
            }

            // Si hay una acción personalizada para esta pestaña, la inyectamos
            if (isset($this->tabActions[$index])) {
                $button->{'onclick'} = $this->tabActions[$index];
            }

            $button->add($page['title']);
            $li->add($button);
            $nav->add($li);

            // --- Estructura del panel contenedor (DIV) ---
            $panel = new Element('div');
            $panel->class = 'tab-pane fade' . ($isActive ? ' show active' : '');
            $panel->id = $page['id'];
            $panel->role = 'tabpanel';
            $panel->aria_labelledby = $page['id'] . '-tab';

            // Metemos el layout interno (ej: tu TVBox) dentro del panel correspondiente
            $panel->add($page['content']);
            $tabContent->add($panel);
        }

        // 4. Acoplamos los bloques construidos al DIV principal de este objeto
        parent::add($nav);
        parent::add($tabContent);

        // Retornamos el objeto Element listo para que lo reciba el Wrapper
        return $this;
    }

    /**
     * Dibuja el componente en pantalla si se usa de forma directa sin Wrapper
     */
    public function show() : void
    {
        //$this->render();
        parent::show();
    }

    /**
     * Expone el método getChildren de TElement para que el Wrapper pueda leerlo
     */
    public function getChildren(): array
    {
        return $this->children; // O return parent::getChildren(); si la clase madre lo tiene público
    }

    /**
     * Expone el método clearChildren de TElement para que el Wrapper pueda limpiar los hijos
     */
    public function clearChildren(): void
    {
        $this->children = [];
    }
}