<?php

namespace Livro\Widgets\Form;

class SimpleForm
{
    private string $name;
    private string $action;
    private array $fields;
    private string $title;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->fields = [];
        $this->title = '';
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function addField(string $label, string $name, string $type, string $value, string $class = '')
    {
        $this->fields[] = [
            'label' => $label,
            'name'  => $name,
            'type'  => $type,
            'value' => $value,
            'class' => $class
        ];
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function show()
    {
        // No Bootstrap 5, 'panel' virou 'card'
        echo "<div class='card my-4 mx-auto' style='max-width: 600px;'>\n";
            if (!empty($this->title)) {
                echo "<div class='card-header bg-primary text-white'> {$this->title} </div>\n";
            }
        echo "<div class='card-body'>\n";
            echo "<form method='POST' action='{$this->action}' name='{$this->name}'>\n";
                if ($this->fields) {
                    foreach ($this->fields as $field) {
                        // Estrutura de grid/linha para formulários no BS5
                        echo "<div class='row mb-3 align-items-center'>\n";
                            echo "<label class='col-sm-3 col-form-label text-sm-end'> {$field['label']} </label>\n";
                            echo "<div class='col-sm-9'>\n";
                                // Correção: fechamento de aspas em 'form-control' removido do meio
                                echo " <input class='form-control {$field['class']}' type='{$field['type']}' name='{$field['name']}' value='{$field['value']}'>\n";
                            echo "</div>\n";
                        echo "</div>\n";
                    }
                }
                
                // Adicionando um botão de envio padrão para o formulário funcionar
                echo "<div class='row'>\n";
                    echo "<div class='col-sm-9 offset-sm-3'>\n";
                        echo "<button type='submit' class='btn btn-success'>Enviar</button>\n";
                    echo "</div>\n";
                echo "</div>\n";
                
            echo "</form>\n";
        echo "</div>\n";
        echo "</div>\n";
    }
}
?>