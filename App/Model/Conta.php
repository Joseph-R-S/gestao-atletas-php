<?php
use Livro\Database\Record;
use Livro\Database\Criteria;
use Livro\Database\Repository;

class Conta extends Record
{
    const TABLENAME = 'conta';
	private $cliente;
	
    public function get_cliente()
    {
        if (empty($this->cliente))
        {
            $this->cliente = new Pessoa($this->id_cliente);
        }
        
        // Retorna o objeto instanciado
        return $this->cliente;
    }
    
	public static function getByPessoa(int $id_pessoa)
	{
	    $criteria = new Criteria;
	    $criteria->add('paga', '<>', 'S');
	    $criteria->add('id_cliente', '=', $id_pessoa);
	    
	    $repo = new Repository('Conta');
	    return $repo->load($criteria);
	}
	
	public static function debitosPorPessoa(int $id_pessoa) : float
	{
	    $total = 0;
	    $contas = self::getByPessoa($id_pessoa);
	    if ($contas)
	    {
	        foreach ($contas as $conta)
	        {
	            $total += $conta->valor;
	        }
	    }
	    return $total;
	}
	/*
	* delay -> dias para gerar depois ejemplo hoy mas 5 dias
	*/
	public static function geraParcelas(int $id_cliente, int $delay, float $valor, int $parcelas)
	{
	    $date = new DateTime(date('Y-m-d'));
		// P é PLUS
		// D de dias
	    $date->add(new DateInterval('P'.$delay.'D'));
	    
	    for ($n=1; $n<=$parcelas; $n++)
	    {
	        $conta = new self;
	        $conta->id_cliente = $id_cliente;
	        $conta->dt_emissao = date('Y-m-d');
	        $conta->dt_vencimento = $date->format('Y-m-d');
	        $conta->valor = $valor / $parcelas;
	        $conta->paga = 'N';
	        $conta->store();
	        
			// P plus 1 mes 
	        $date->add(new DateInterval('P1M'));
	    }
	}
}
