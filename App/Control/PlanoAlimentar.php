<?php

use Livro\Control\Page;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;
use Livro\Widgets\Dialog\Message;

use Dompdf\Dompdf;
use Dompdf\Options;
use Livro\Widgets\Container\TVBox;

class PlanoAlimentar extends Page
{
    private $loaded;

    public function __construct()
    {
        parent::__construct();
    }

    public function showDieta()
    {
        $param = $_GET;
        $id = $param['id'] ?? null;

        if (!$id) {
            new Message('error', 'ID do atleta não informado.');
            return;
        }

        try {
            Transaction::open('livro');

            // 1. Carregar atleta
            $atleta = new Atletas($id);

            // 2. Obter a última dieta ativa
            $repository = new Repository('Dieta');
            $criteria   = new Criteria;
            $criteria->add('atleta_id', '=', $id);
            $criteria->setProperty('order', 'id DESC');
            $criteria->setProperty('limit', 1);
            $dietas = $repository->load($criteria);

            if (empty($dietas)) {
                throw new Exception("Nenhuma dieta encontrada para este atleta.");
            }

            $dieta_actual = $dietas[0];
            $objetivo     = new Objetivos($dieta_actual->objetivo_id);

            // 3. Carregar itens
            $repository_itens = new Repository('DietaItem');
            $criteria_itens   = new Criteria;
            $criteria_itens->add('dieta_id', '=', $dieta_actual->id);
            $itens = $repository_itens->load($criteria_itens);

            // 4. Estruturar refeições e totais
            $refeicoes = [];
            $totaisGlobais = [
                'calorias'      => 0,
                'proteinas'     => 0,
                'carbohidratos' => 0,
                'gorduras'      => 0
            ];

            foreach ($itens as $item) {
                $nombreComida = $item->nome_refeicao ?? 'Geral';
                $alimento     = Alimentos::find($item->alimento_id);

                if (!isset($refeicoes[$nombreComida])) {
                    $refeicoes[$nombreComida] = [
                        'nome_refeicao' => $nombreComida,
                        'alimentos'     => [],
                        'total_kcal'    => 0,
                        'total_prot'    => 0,
                        'total_carb'    => 0,
                        'total_gord'    => 0
                    ];
                }

                if ($alimento) {
                    $cal  = (float) ($item->calorias ?? 0);
                    $prot = (float) ($item->proteinas ?? 0);
                    $carb = (float) ($item->carbohidratos ?? 0);
                    $gord = (float) ($item->gorduras ?? 0);

                    $refeicoes[$nombreComida]['alimentos'][] = [
                        'id'            => $alimento->id,
                        'nome'          => ucfirst($alimento->nome),
                        'quantidade'    => $item->quantidade,
                        'unidade'       => $alimento->medida ?? 'g',
                        'calorias'      => $cal,
                        'proteinas'     => $prot,
                        'carbohidratos' => $carb,
                        'gorduras'      => $gord,
                        'observacao'    => $item->observacao ?? null
                    ];

                    $refeicoes[$nombreComida]['total_kcal'] += $cal;
                    $refeicoes[$nombreComida]['total_prot'] += $prot;
                    $refeicoes[$nombreComida]['total_carb'] += $carb;
                    $refeicoes[$nombreComida]['total_gord'] += $gord;

                    $totaisGlobais['calorias']      += $cal;
                    $totaisGlobais['proteinas']     += $prot;
                    $totaisGlobais['carbohidratos'] += $carb;
                    $totaisGlobais['gorduras']      += $gord;
                }
            }

            // 5. Renderizar com Twig
            $loader   = new \Twig\Loader\FilesystemLoader('App/Resources');
            $twig     = new \Twig\Environment($loader, ['cache' => false]);
            $template = $twig->load('dieta_report.html');

            $replaces = [
                'atleta'         => $atleta,
                'dieta'          => $dieta_actual,
                'peso'           => $dieta_actual->peso,
                'altura'         => $dieta_actual->altura,
                'data_criacao'   => date('d/m/Y', strtotime($dieta_actual->data_criacao ?? 'now')),
                'objetivo_nome'  => $objetivo->descricao ?? 'Não especificado',
                'dietas'         => array_values($refeicoes),
                'totais'         => $totaisGlobais
            ];

            $content = $template->render($replaces);

            // Botão para descarregar o PDF quando o utilizador clicar
            $btnPdf = "<div style='margin-bottom: 15px;'>
            <a href='index.php?class=PlanoAlimentar&method=downloadPDF&id={$id}' class='btn btn-primary' target='_blank'>
                <i class='fa fa-file-pdf-o' aria-hidden='true'></i> Baixar PDF
            </a>
           </div>";


            // Renderizar no Painel
            $vbox = new TVBox;
            $vbox->add($btnPdf);
            $vbox->class = 'bg-light p-2';
            $panel = new Panel('Plano Alimentar - ' . strtoupper($atleta->nome));
            $vbox->add($panel);
            $panel->add($content);
            parent::add($vbox);

            Transaction::close();
        } catch (\Throwable $e) {
            Transaction::rollback();
            new Message('error', 'Erro ao carregar dieta: ' . $e->getMessage());
        }
    }

    /**
     * Método para gerar e forçar o download/stream do PDF no navegador
     */
    public static function downloadPDF($param)
    {
        $id = $param['id'] ?? null;

        if (!$id) {
            new Message('error', 'ID do atleta não informado.');
            return;
        }

        try {
            Transaction::open('livro');

            // 1. Carregar atleta
            $atleta = new Atletas($id);

            // 2. Obter a última dieta ativa
            $repository = new Repository('Dieta');
            $criteria   = new Criteria;
            $criteria->add('atleta_id', '=', $id);
            $criteria->setProperty('order', 'id DESC');
            $criteria->setProperty('limit', 1);
            $dietas = $repository->load($criteria);

            if (empty($dietas)) {
                throw new Exception("Nenhuma dieta encontrada para este atleta.");
            }

            $dieta_actual = $dietas[0];
            $objetivo     = new Objetivos($dieta_actual->objetivo_id);

            // 3. Carregar itens da dieta
            $repository_itens = new Repository('DietaItem');
            $criteria_itens   = new Criteria;
            $criteria_itens->add('dieta_id', '=', $dieta_actual->id);
            $itens = $repository_itens->load($criteria_itens);

            // 4. Estruturar refeições e calcular totais
            $refeicoes = [];
            $totaisGlobais = [
                'calorias'      => 0,
                'proteinas'     => 0,
                'carbohidratos' => 0,
                'gorduras'      => 0
            ];

            foreach ($itens as $item) {
                $nombreComida = $item->nome_refeicao ?? 'Geral';
                $alimento     = Alimentos::find($item->alimento_id);

                if (!isset($refeicoes[$nombreComida])) {
                    $refeicoes[$nombreComida] = [
                        'nome_refeicao' => $nombreComida,
                        'alimentos'     => [],
                        'total_kcal'    => 0,
                        'total_prot'    => 0,
                        'total_carb'    => 0,
                        'total_gord'    => 0
                    ];
                }

                if ($alimento) {
                    $cal  = (float) ($item->calorias ?? 0);
                    $prot = (float) ($item->proteinas ?? 0);
                    $carb = (float) ($item->carbohidratos ?? 0);
                    $gord = (float) ($item->gorduras ?? 0);

                    $refeicoes[$nombreComida]['alimentos'][] = [
                        'id'            => $alimento->id,
                        'nome'          => ucfirst($alimento->nome),
                        'quantidade'    => $item->quantidade,
                        'unidade'       => $alimento->medida ?? 'g',
                        'calorias'      => $cal,
                        'proteinas'     => $prot,
                        'carbohidratos' => $carb,
                        'gorduras'      => $gord,
                        'observacao'    => $item->observacao ?? null
                    ];

                    $refeicoes[$nombreComida]['total_kcal'] += $cal;
                    $refeicoes[$nombreComida]['total_prot'] += $prot;
                    $refeicoes[$nombreComida]['total_carb'] += $carb;
                    $refeicoes[$nombreComida]['total_gord'] += $gord;

                    $totaisGlobais['calorias']      += $cal;
                    $totaisGlobais['proteinas']     += $prot;
                    $totaisGlobais['carbohidratos'] += $carb;
                    $totaisGlobais['gorduras']      += $gord;
                }
            }

            // 5. Renderizar o HTML via Twig
            $loader   = new \Twig\Loader\FilesystemLoader('App/Resources');
            $twig     = new \Twig\Environment($loader, ['cache' => false]);
            $template = $twig->load('dieta_report.html');

            $replaces = [
                'atleta'         => $atleta,
                'dieta'          => $dieta_actual,
                'peso'           => $dieta_actual->peso,
                'altura'         => $dieta_actual->altura,
                'data_criacao'   => date('d/m/Y', strtotime($dieta_actual->data_criacao ?? 'now')),
                'objetivo_nome'  => $objetivo->descricao ?? 'Não especificado',
                'dietas'         => array_values($refeicoes),
                'totais'         => $totaisGlobais
            ];

            $html = $template->render($replaces);
            $panel = new Panel('Plano Alimentar - ' . strtoupper($atleta->nome));
            $panel->add($html);
            Transaction::close();

            // 6. Configurar Dompdf
            $options = new Options();
            $options->set('dpi', 128);
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', realpath(__DIR__ . '/../../'));

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($panel);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // 7. Enviar o PDF diretamente ao navegador (Attachment => false abre no navegador, true força download)
            $filename = 'plano_alimentar_' . $atleta->id . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit;
        } catch (\Throwable $e) {
            Transaction::rollback();
            new Message('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
}
