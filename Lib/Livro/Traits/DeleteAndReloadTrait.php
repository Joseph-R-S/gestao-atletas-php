<?php

namespace Livro\Traits;

use Livro\Control\Action;
use Livro\Database\Transaction;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Dialog\MessageSweetAlert;
use Livro\Widgets\Dialog\Question;
use Exception;

trait DeleteAndReloadTrait
{
    /**
     * Carrega a DataGrid com os objetos do banco de dados
     */
    public function onReload(): void
    {
        try {
            Transaction::open($this->connection);
            
            // Instancia el repositorio usando la clase dinámica configurada en el controlador
            $repository = new Repository($this->activeRecord);

            $criteria = new Criteria();
            $criteria->setProperty('order', 'id');
            
            $objects = $repository->load($criteria);

            $this->datagrid->clear();
            
            if ($objects) {
                foreach ($objects as $object) {
                    $this->datagrid->addItem($object);
                }
            }
            Transaction::close();
        } catch (Exception $e) {
            new MessageSweetAlert('error', "{$e->getMessage()}");
            Transaction::rollback();
        }
    }

    /**
     * Exibe la pregunta de confirmación antes de deletrear
     */
    public function onDelete(array $param): void
    {
        $action = new Action([$this, 'delete']);
        $action->setParameter('id', $param['id']);
        
        new Question('Deseja realmente apagar o registro?', $action);
    }

    /**
     * Executa a exclusão física no banco de dados
     */
    public function delete(array $param): void
    {
        try {
            Transaction::open($this->connection);

            // Usa el ActiveRecord dinámico (ej: Funcionario::find() o Cidade::find())
            $activeRecordClass = $this->activeRecord;
            $object = $activeRecordClass::find($param['id']);

            if ($object) {
                $object->delete();
            }
            Transaction::close();
            
            // 💡 Ciclo seguro: Avisamos que requiere recarga en el método show()
            $this->loaded = false;
            
            new MessageSweetAlert('info', 'Registro excluído com sucesso!');
        } catch (Exception $e) {
            new MessageSweetAlert('error', "{$e->getMessage()}");
            Transaction::rollback();
        }
    }

    /**
     * Controla la exhibición de la página
     */
    public function show(): void
    {
        if (empty($this->loaded)) {
            $this->onReload();
            $this->loaded = true;
        }
        parent::show();
    }
}