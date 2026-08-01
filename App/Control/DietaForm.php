<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;
use Livro\Session\Session;
use Livro\util\RespuestaAjax;
use Livro\Widgets\Container\Panel;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Wrapper\FormWrapper;

class DietaForm extends Page
{
    private FormWrapper $form;
    private DatagridWrapper $datagrid;
    private $loaded;

    public function __construct()
    {
        parent::__construct();
        new Session;
        $this->form = new FormWrapper(new Form('form_dieta'));

        $id_atleta = new Entry('id_atleta');
        $nome = new Entry('nome');
        $tasa_metabolica = new Entry('tasa_metabolica');
        $objetivo = new Entry('objetivo');
        $ajus_obj_consumo = new Entry('ajus_obj_consumo');
        $ajus_obj_proteinas = new Entry('ajus_obj_proteinas');
        $ajus_obj_gorduras = new Entry('ajus_obj_gorduras');
        $ajus_obj_carbohidratos = new Entry('ajus_obj_carbohidratos');
        $peso = new Entry('peso');
        $refeicao   = new Combo('refeicao');
        $tipo       = new Combo('tipo');
        $alimentos  = new Combo('alimentos');
        $quantidade = new Entry('quantidade');

        //
        $tot_calorias  = new Entry('tot_calorias');
        $tot_proteinas = new Entry('tot_proteinas');
        $tot_gorduras   = new Entry('tot_gorduras');
        $tot_carb      = new Entry('tot_carb');

        // IDs requeridos para que JS pueda identificar los elementos en el DOM
        $tipo->id      = 'tipo';
        $alimentos->id = 'alimentos';
        $quantidade->id = 'quantidade';

        $id_atleta->setEditable(FALSE);
        $nome->setEditable(FALSE);
        $tasa_metabolica->setEditable(FALSE);
        $objetivo->setEditable(FALSE);
        $peso->setEditable(FALSE);

        $tot_calorias->setEditable(FALSE);
        $tot_proteinas->setEditable(FALSE);
        $tot_gorduras->setEditable(FALSE);
        $tot_carb->setEditable(FALSE);

        $ajus_obj_consumo->setProperty('placeholder', 'Ajuste de calorias');
        $ajus_obj_proteinas->setProperty('placeholder', '0.8 a 2.4 por kg de peso');
        $ajus_obj_gorduras->setProperty('placeholder', '0.7 a 1.2 por kg de peso');
        $ajus_obj_carbohidratos->setProperty('placeholder', 'ocupan as calorías sobrantes');

        // Dispara la función Ajax al cambiar la opcion del combo 'Tipo'
        $tipo->setProperty('onchange', "App.ejecutar('index.php?class=DietaForm&method=onCarregarAlimentos', {tipo: this.value})");
        $alimentos->setProperty('onchange', "App.ejecutar('index.php?class=DietaForm&method=onCarregarQuantidade', {alimentos: this.value})");

        $refeicoes_todas = Alimentos::REFEICOES;

        $tipos = Alimentos::TIPO;

        $refeicao->addItems($refeicoes_todas);
        $tipo->addItems($tipos);

        // Registro de los campos en el formulario
        $this->form->addField('Id', $id_atleta, '2');
        $this->form->addField('Nome', $nome, '4');

        $this->form->addField('Tasa Metabolica Total', $tasa_metabolica, '2');
        $this->form->addField('Objetivo', $objetivo, '2');
        $this->form->addField('Peso', $peso, '2');

        $this->form->addField('Total Calorías (Consumido/Meta)', $tot_calorias, '3');
        $this->form->addField('Total Proteínas(g)', $tot_proteinas, '3');
        $this->form->addField('Total Grasas(g)', $tot_gorduras, '3');
        $this->form->addField('Total Carbos(g)', $tot_carb, '3');

        $this->form->addField('Ajuste de calorias', $ajus_obj_consumo, '3');
        $this->form->addField('Ajuste segun objetivo Proteinas', $ajus_obj_proteinas, '3');
        $this->form->addField('Ajuste segun objetivo Gurdura', $ajus_obj_gorduras, '3');

        $this->form->addField('Refeição', $refeicao, '3');
        $this->form->addField('Tipo', $tipo, '3');
        $this->form->addField('Alimentos', $alimentos, '3');
        $this->form->addField('Quantidade', $quantidade, '3');

        // Botoes de accion
        $adicionar_item = new Action([$this, 'onAdiciona']);
        $adicionar_item->setProperty('id', 'btn_adicionar_item');
        $adicionar_item->setAjax(true);
        $adicionar_item->setProperty('id', 'btn_adicionar_item');
        $this->form->addAction('Adicionar Item', $adicionar_item);
        $this->form->addAction('Salvar Dieta', new Action([$this, 'onSave']));

        // Configuración de la Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);
        $this->datagrid->setProperty('id', 'datagrid_dieta');
        $col_codigo     = new DatagridColumn('id', 'Código', 'center', '10%');
        $col_refeicao       = new DatagridColumn('nome_refeicao', 'Refeicao', 'left', '15%');
        $col_nome       = new DatagridColumn('nome', 'Descrição', 'left', '25%');
        $col_quantidade = new DatagridColumn('quantidade', 'Qtde', 'left', '10%');
        $col_calorias   = new DatagridColumn('calorias', 'Calorias', 'left', '10%');
        $col_proteinas   = new DatagridColumn('proteinas', 'Proteinas', 'left', '10%');
        $col_gorduras   = new DatagridColumn('gorduras', 'Gorduras', 'left', '10%');
        $col_carb   = new DatagridColumn('carbohidratos', 'Carb', 'left', '10%');

        $this->datagrid->addColumn($col_codigo);
        $this->datagrid->addColumn($col_refeicao);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_quantidade);
        $this->datagrid->addColumn($col_calorias);
        $this->datagrid->addColumn($col_proteinas);
        $this->datagrid->addColumn($col_gorduras);
        $this->datagrid->addColumn($col_carb);

        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'session_key', 'fa fa-trash fa-lg red');
        $panel = new Panel();
        $panel->add($this->form);
        $panel->add($this->datagrid);
        parent::add($panel);
    }

    /**
     * Calcula los totales consumidos en la lista de alimentos
     * y prepara los datos de comparación con las metas del atleta.
     */
    private function calcularResumenNutricional($dados)
    {
        $list = Session::getValue('list') ?? [];

        $acc_calorias  = 0.0;
        $acc_proteinas = 0.0;
        $acc_gorduras   = 0.0;
        $acc_carb      = 0.0;

        // 1. Sumar todo lo que está actualmente en la DataGrid (Sesión)
        foreach ($list as $item) {
            $acc_calorias  += (float)($item->calorias ?? 0);
            $acc_proteinas += (float)($item->proteinas ?? 0);
            $acc_gorduras   += (float)($item->gorduras ?? 0);
            $acc_carb      += (float)($item->carbohidratos ?? 0);
        }

        // 2. Extraer datos del formulario / sesión para calcular metas
        $peso = (float)($dados->peso ?? 0);
        $factor_prot = (float)($dados->ajus_obj_proteinas ?? 2.0); // Ejemplo: 2.0g por kg por defecto
        $factor_gord = (float)($dados->ajus_obj_gorduras ?? 1.0);  // Ejemplo: 1.0g por kg por defecto

        // Metas en gramos
        $meta_prot = $peso * $factor_prot;
        $meta_gord = $peso * $factor_gord;
        $meta_cal  = (float)($dados->tasa_metabolica ?? 0) + (float)($dados->ajus_obj_consumo ?? 0);

        // 3. Formatear cadenas explicativas para los inputs del formulario
        $dados->tot_calorias  = sprintf("%.1f / %.1f kcal", $acc_calorias, $meta_cal);
        $dados->tot_proteinas = sprintf("%.1f / %.1f g (%s)", $acc_proteinas, $meta_prot, ($acc_proteinas >= $meta_prot) ? 'OK' : 'Falta ' . number_format($meta_prot - $acc_proteinas, 1) . 'g');
        $dados->tot_gorduras   = sprintf("%.1f / %.1f g (%s)", $acc_gorduras, $meta_gord, ($acc_gorduras >= $meta_gord) ? 'OK' : 'Falta ' . number_format($meta_gord - $acc_gorduras, 1) . 'g');
        $dados->tot_carb      = sprintf("%.1f g acumulados", $acc_carb);
    }

    /**
     * Agrega un ítem a la lista en sesión y actualiza la DataGrid
     */
    public function onAdiciona()
    {
        try {
            $dados = $this->form->getData();

            // 1. Validaciones
            if (empty($dados->alimentos)) {
                throw new Exception("Selecione um alimento da lista.");
            }
            if ($dados->refeicao) {
                throw new Exception("Selecione uma refeiçao.");
            }
            if (empty($dados->quantidade) || (float)$dados->quantidade <= 0) {
                throw new Exception("Informe uma quantidade válida.");
            }

            Transaction::open('livro');
            $alimento_result = Alimentos::find($dados->alimentos);

            if ($alimento_result) {
                $refeicao_id = $dados->refeicao ?? '0';
                $refeicoes   = Alimentos::REFEICOES;
                $nome_refeicao = $refeicoes[$refeicao_id] ?? '';
                $session_key = $refeicao_id . '_' . $alimento_result->id;

                // 2. Cálculos según unidad/gramos
                if ($alimento_result->medida == 'unid') {
                    $calorias      = (float)$dados->quantidade * (float)$alimento_result->calorias;
                    $proteinas     = (float)$dados->quantidade * (float)$alimento_result->proteinas;
                    $gorduras      = (float)$dados->quantidade * (float)$alimento_result->gorduras;
                    $carbohidratos = (float)$dados->quantidade * (float)$alimento_result->carbohidratos;
                } else {
                    $calorias      = (float)$dados->quantidade * ((float)$alimento_result->calorias / 100);
                    $proteinas     = (float)$dados->quantidade * ((float)$alimento_result->proteinas / 100);
                    $gorduras      = (float)$dados->quantidade * ((float)$alimento_result->gorduras / 100);
                    $carbohidratos = (float)$dados->quantidade * ((float)$alimento_result->carbohidratos / 100);
                }

                // 3. Crear el objeto para la sesión
                $item = new \stdClass;
                $item->session_key   = $session_key;
                $item->id            = $alimento_result->id;
                $item->refeicao      = $refeicao_id;
                $item->nome          = $alimento_result->nome;
                $item->quantidade    = $dados->quantidade;
                $item->nome_refeicao = $nome_refeicao;
                $item->calorias      = number_format($calorias, 2, '.', '');
                $item->proteinas     = number_format($proteinas, 2, '.', '');
                $item->gorduras      = number_format($gorduras, 2, '.', '');
                $item->carbohidratos = number_format($carbohidratos, 2, '.', '');

                //pegar valores da sessão
                $atleta_session = Session::getValue('atleta_dieta');

                //adicionar novos valores
                $atleta_session->ajus_obj_consumo = $dados->ajus_obj_consumo;
                $atleta_session->ajus_obj_proteinas = $dados->ajus_obj_proteinas;
                $atleta_session->ajus_obj_gorduras = $dados->ajus_obj_gorduras;

                //salvar os valores
                Session::setValue('atleta_dieta', $atleta_session);
                // Guardar en la lista en sesión
                $list = Session::getValue('list') ?? [];
                $list[$session_key] = $item;
                Session::setValue('list', $list);

                Transaction::close();

                // 4. Recalcular totales del resumen
                $this->calcularResumenNutricional($dados);

                // 5. Construir el HTML de la nueva fila <tr>
                $trHtml = "<tr id='row_{$session_key}'><td><a href=?class=DietaForm&method=onDelete&key={$session_key}&session_key={$session_key}><i class='fa fa-trash fa-lg text-primary' title='Excluir'></i></a></td>"
                    . "<td align='center'>{$item->id}</td>"
                    . "<td>{$nome_refeicao}</td>"
                    . "<td>{$item->nome}</td>"
                    . "<td align='left'>{$item->quantidade}</td>"
                    . "<td align='left'>{$item->calorias}</td>"
                    . "<td align='left'>{$item->proteinas}</td>"
                    . "<td align='left'>{$item->gorduras}</td>"
                    . "<td align='left'>{$item->carbohidratos}</td>"
                    . "</tr>";

                // 6. Preparar paquete de respuestas JSON
                $respuestas = [
                    // Inserción en el Datagrid
                    ['tipo' => 'appendHTML', 'target' => '#datagrid_dieta tbody', 'html' => $trHtml],

                    // Actualizar los inputs del resumen nutricional
                    ['tipo' => 'setValue', 'target' => 'tot_calorias', 'data' => $dados->tot_calorias],
                    ['tipo' => 'setValue', 'target' => 'tot_proteinas', 'data' => $dados->tot_proteinas],
                    ['tipo' => 'setValue', 'target' => 'tot_gorduras', 'data' => $dados->tot_gorduras],
                    ['tipo' => 'setValue', 'target' => 'tot_carb', 'data' => $dados->tot_carb],

                    // Limpiar el campo de cantidad para la siguiente entrada
                    ['tipo' => 'setValue', 'target' => 'quantidade', 'data' => '']
                ];

                header('Content-Type: application/json');
                echo json_encode($respuestas);
                exit;
            }
        } catch (\Throwable $e) {
            Transaction::rollback();
            header('Content-Type: application/json');
            echo json_encode([
                ['tipo' => 'executeJS', 'script' => "alert('" . addslashes($e->getMessage()) . "');"]
            ]);
            exit;
        }
    }

    public function onSetDieta(array $param)
    {
        $id = $param['id'] ?? null;
        $atleta_session = Session::getValue('atleta_dieta');

        if ($id) {
            try {
                Transaction::open('livro');
                $objetivo = new Objetivos($id);
                if ($atleta_session) {
                    $atleta_session->objetivo = $objetivo->descricao;
                }
                Transaction::close(); // <- Faltaba cerrar la transacción aquí
            } catch (\Throwable $e) {
                Transaction::rollback();
                new Message('erro', 'Erro ao buscar objetivo do atleta: ' . $e->getMessage());
            }
            $this->onReload();
        }
        if ($atleta_session) {
            // Carga los datos de la cabecera en el formulario
            $this->form->setData($atleta_session);
        } else {
            new Message('error', 'Sessão do atleta expirada ou não encontrada.');
        }
        $this->calcularResumenNutricional($atleta_session);
        // Recarga los ítems agregados en la DataGrid leyendo Session::getValue('list')
        $this->onReload();
    }

    /**
     * Metodo para buscar a dados del perfil atleta
     */
    public function buscarDados(string $id_atleta)
    {
        try {

            if (isset($id_atleta)) {
                $id = $id_atleta;
                $repository = new Repository('PerfilAtleta');
                $criteria = new Criteria;
                $criteria->add('atleta_id', '=', (int) $id);

                $medidas = $repository->load($criteria);
                if ($medidas) {
                    $ultimoPerfil = end($medidas);
                    return $ultimoPerfil;
                }
            }
        } catch (Exception $e) {
            new Message('erro', 'Erro ao buscar atleta 2' . $e->getMessage());
        }
    }

    /**
     * Formata valor 
     */
    public function formata_money(float $valor)
    {
        return number_format($valor, 2, '.', '.');
    }
    /**
     * Endpoint encargado exclusivamente de responder peticiones Ajax
     */
    public function onCarregarAlimentos()
    {
        $tipo = $_POST['tipo'] ?? null;
        $alimentos_array = [];
        if ($tipo) {
            try {
                Transaction::open('livro');
                $repository = new Repository('Alimentos');
                $criteria   = new Criteria;
                $criteria->setProperty('order', 'nome');
                $criteria->setProperty('direction', 'asc');
                $criteria->add('tipo', '=', $tipo);

                $results = $repository->load($criteria);

                if ($results) {
                    foreach ($results as $result) {
                        $alimentos_array[$result->id] = $result->nome;
                    }
                }
                Transaction::close();
            } catch (Exception $e) {
                // En peticiones Ajax silenciamos la excepción o retornamos array vacío
                Transaction::rollback();
            }
        }

        // Responde directamente JSON y detiene PHP
        RespuestaAjax::setValue('alimentos', $alimentos_array);
    }

    public function onCarregarQuantidade()
    {
        $alimentoId = $_POST['alimentos'] ?? null;
        $quantidade = '';
        if ($alimentoId) {
            try {
                Transaction::open('livro');

                $alimento = Alimentos::find($alimentoId);
                if ($alimento) {
                    // Reemplaza 'porcao' o 'quantidade' por el nombre exacto de la columna en tu BD
                    $quantidade = $alimento->quantidade;
                }
                Transaction::close();
            } catch (Exception $e) {
                Transaction::rollback();
            }
        }
        // Responde directamente JSON y detiene la ejecución (exit)
        RespuestaAjax::setValue('quantidade', $quantidade);
    }

    // Método a implementar:
    public function onSave()
    {
        try {
            Transaction::open('livro');
            $dados = $this->form->getData();
            $atleta_session =  Session::getValue('atleta_dieta');
            $list  = Session::getValue('list');

            if (empty($list)) {
                throw new Exception("Adicione ao menos um alimento à dieta.");
            }

            $objetivos = Objetivos::all();
            if ($objetivos) {
                foreach ($objetivos as $objetivo) {
                    if ($objetivo->descricao == $atleta_session->objetivo) {
                        $objetivo_id = $objetivo->id;
                    }
                }
            }

            $dieta = new Dieta;
            $dieta->atleta_id       = $atleta->id_atleta ?? $dados->id_atleta;
            $dieta->peso = $dados->peso;
            $dieta->altura = $atleta_session->altura;
            $dieta->tasa_metabolica_total = $dados->tasa_metabolica;
            $dieta->objetivo_id        = $objetivo_id;
            $dieta->ajus_obj_consumo   = $dados->ajus_obj_consumo;
            $dieta->ajus_obj_proteinas  = $dados->ajus_obj_proteinas;
            $dieta->ajus_obj_gorduras   = $dados->ajus_obj_gorduras;
            $dieta->status          = 'ativa';
            $dieta->data_criacao    = date('Y-m-d H:i:s');
            $dieta->store();

            foreach ($list as $item) {
                $itemDieta = new DietaItem;
                $itemDieta->dieta_id      = $dieta->id; // ID generado en el paso anterior
                $itemDieta->alimento_id   = $item->id;
                $itemDieta->refeicao_id   = $item->refeicao;
                $itemDieta->nome_refeicao = $item->nome_refeicao;
                $itemDieta->quantidade    = $item->quantidade;
                $itemDieta->calorias      = $item->calorias;
                $itemDieta->proteinas     = $item->proteinas;
                $itemDieta->gorduras      = $item->gorduras;
                $itemDieta->carbohidratos = $item->carbohidratos;
                $itemDieta->store();
            }

            //Session::setValue('list', []); // Limpiar sesión tras guardar
            Transaction::close();

            new Message('info', 'Dieta salva com sucesso!');
            $this->onReload();
        } catch (Exception $e) {
            Transaction::rollback();
            new Message('error', 'Erro ao salvar dieta: ' . $e->getMessage());
        }
    }

    //Ordena a list antes de ir no data grid
    public function ordenaList(array $list): array
    {
        $refeicoes = Alimentos::REFEICOES; // Array das refeições na ordem correta

        // Usa uasort para preservar as chaves do array ("1_3", "2_8", etc.)
        uasort($list, function ($a, $b) use ($refeicoes) {
            // Busca o índice numérico no array $refeicoes usando o nome_refeicao
            $posA = array_search($a->nome_refeicao ?? '', $refeicoes);
            $posB = array_search($b->nome_refeicao ?? '', $refeicoes);

            // Se a refeição não for encontrada no array, envia para o final (índice 999)
            $posA = ($posA === false) ? 999 : $posA;
            $posB = ($posB === false) ? 999 : $posB;

            return $posA <=> $posB;
        });

        return $list;
    }

    /**
     * Carga/Recarga la DataGrid leyendo la sesión
     */
    public function onReload()
    {
        $list = Session::getValue('list') ?? [];
        $this->datagrid->clear(); // Limpia filas viejas
        $list = $this->ordenaList($list);
        if ($list) {
            foreach ($list as $item) {
                $this->datagrid->addItem($item); // Reinserta los registros almacenados
            }
        }
        $this->loaded = true;
    }

    /**
     * Elimina un ítem da sesão
     */
    public function onDelete(array $param)
    {
        // 1. Obtenemos la clave (session_key) enviada por el botón de la datagrid
        $key = $param['session_key'] ?? $param['id'] ?? null;

        if ($key) {
            // 2. Leemos la lista de la sesión
            $list = Session::getValue('list') ?? [];

            // 3. Eliminamos el elemento de la lista
            if (isset($list[$key])) {
                unset($list[$key]);
                Session::setValue('list', $list);
            }
        }

        // 4. Mantenemos los datos del formulario visibles (Rehidratación)
        $dados = $this->form->getData();

        // Si algunos campos deshabilitados (readonly) no viajan en el POST, los traemos de la sesión
        $atleta_session = Session::getValue('atleta_dieta');
        if ($atleta_session) {
            foreach ($atleta_session as $campo => $valor) {
                if (empty($dados->$campo)) {
                    $dados->$campo = $valor;
                }
            }
        }

        $this->calcularResumenNutricional($dados);
        // 5. Devolvemos los datos al formulario para que no se limpie la pantalla
        $this->form->setData($dados);

        // 6. Recargamos el DataGrid actualizado
        $this->onReload();
    }

    /**
     * Renderiza a pagina
     */
    public function show()
    {
        // Rehidrata los datos de la cabecera atleta
        $atleta_session = Session::getValue('atleta_dieta');
        if ($atleta_session) {
            $this->calcularResumenNutricional($atleta_session);
            $this->form->setData($atleta_session);
        }

        // Carga los ítems agregados ao Datagrid
        if (!$this->loaded) {
            $this->onReload();
        }

        parent::show();
    }
}
