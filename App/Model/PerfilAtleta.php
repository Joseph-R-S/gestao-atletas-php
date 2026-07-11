<?php
use Livro\Database\Record;

class PerfilAtleta extends Record
{
    const TABLENAME = 'perfil_atleta';

    const FACTORES_ACTIVIDADE = [
        '1.2'   => 'Sedentário',
        '1.375' => 'Levemente Ativo',
        '1.55'  => 'Moderadamente Ativo',
        '1.725' => 'Altamente Ativo',
        '1.9'   => 'Extremamente Ativo'
    ];

    public static function indiceMasaCorporal(float $peso, float $altura): array
    {
        $cal = $peso / ($altura * $altura);

        if($cal < 18.5){
            $string = ' Abaixo do peso';
        } elseif($cal < 24.9){
            $string = ' Peso normal';
        }elseif($cal < 29.9){
            $string = ' Pré-obesidade';
        }else{
            $string = ' Obesidade';
        }
        $imc = [];
        $imc['imc'] = $cal;
        $imc['result'] = $string;
        return $imc;
    }

    public static function tasaMetabolicaBasal(float $peso, int|float $altura, object $edad, string $sexo) : float
    {
        if ($sexo == 'm') {
            $ajuste_sexo = 5;
        } else {
            $ajuste_sexo = -161;
        }
        return (10 * $peso) + (6.25 * $altura) - (5 * $edad->y) + $ajuste_sexo;
    }

    //$fa factor de actividad
    public static  function  tasaMetabolicaTotal(float $tmb, float $fa) : float {
      
        return $tmb * $fa;
    }
    
    // método en el modelo para recuperar el texto legible
    public static function getTextoFactor(string $factor)
    {
        return self::FACTORES_ACTIVIDADE[$factor] ?? '';
    }
}
