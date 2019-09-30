<?php
/**
 * Created by PhpStorm.
 * User: Cleiton
 * Date: 23/09/2019
 * Time: 14:45
 */

// datas �teis
function hoje() { // padr�o comum
    return date("d-m-Y", time());
}

function ontem() {
    return date("d-m-Y", strtotime("yesterday"));
}

function amanha() {
    return date("d-m-Y", strtotime("tomorrow"));
}

function agora() { // padr�o sql
    return date("Y-m-d H:i:s");
}

// convers�es de data
function converteData($data, $separador = "-") { // quando usa um separador diferente e por que precisa ser um par�metro?
    // imagino que isso n�o v� quebrar nada
    if($data === null) return null;

    $data = str_replace("/", "-", $data);

    $data_1 = array();
    $data_1 = explode("-", $data);

    $dia = $data_1[0];
    $mes = $data_1[1];
    $ano = $data_1[2];

    $data_i = $ano.$separador.$mes.$separador.$dia;

    return $data_i;
}

function converteDataSql($data) {
    if(empty($data) || $data == "0000-00-00" || $data == "0000-00-00 00:00:00")
        return "";
    else
        return date("d-m-Y", strtotime($data));
}

// isso � quase desnecess�rio; a data vinda do SQL j� tem esse formato, tudo que faz � retirar a string das horas e deixar AAAA-MM-DD
function converteDataSqlOrdenada($data) {
    if (empty($data) || $data == "0000-00-00" || $data == "0000-00-00 00:00:00")
        return "";
    else
        return date("Y-m-d", strtotime($data));
}

function converteDataHoraSql($data) {
    if ($data == "")
        return "";
    else
        return date("Y-m-d H:i:s", strtotime($data));
}

/* ==========================================================================================
* dateDiff
* Retira a diferen�a entre as datas de acordo com o tipo de verifica��o
* @param string $dt1 - Data inicial
* @param string $dt2 - Data Final
* @return int diferen�a de dias entre datas
*/
function dateDiff($dt1, $dt2){
    $inicio	= \DateTime::createFromFormat('Y-m-d', $dt1);
    $fim 	= \DateTime::createFromFormat('Y-m-d', $dt2);
    $diff = $fim->diff($inicio);
    return  $diff->format('%a');
}

// vig�ncia dos t�tulos provis�rios - pode ser o primeiro do m�s corrente ou �ltimo do m�s anterior
function dataEmissao($data_base = null) {
    if(empty($data_base)) $data_base = hoje();

    $partes = explode("-", $data_base);
    $mes = $partes[1];
    $ano = $partes[2];

    if(__EMITE_MES_ANTERIOR__) {
        $mes -= 1;

        if($mes <= 0) {
            $mes = 12;
            $ano -= 1;
        }

        $dia = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    }
    else {
        $dia = "01";
    }

    return $dia."-".$mes."-".$ano;
}

// n�mero do m�s atual
function esteMes() {
    return date("m");
}

// n�mero do m�s passado
function mesAnterior($mes) {
    if(!$mes) $mes  = date("m");
    $mes_anterior = date("m", mktime(0, 0, 0, $mes - 1));
    return $mes_anterior;
}

// nome dos meses por extenso
function nomeDoMes($mes) {
    switch($mes) {
        case 1: return "Janeiro";
        case 2: return "Fevereiro";
        case 3: return "Mar�o";
        case 4: return "Abril";
        case 5: return "Maio";
        case 6: return "Junho";
        case 7: return "Julho";
        case 8: return "Agosto";
        case 9: return "Setembro";
        case 10: return "Outubro";
        case 11: return "Novembro";
        case 12: return "Dezembro";
        default: return "Indefinido";
    }
}

// nome dos dias da semana (usar com par�metro "w" do date)
function nomeDiaSemana($dia) {
    switch($dia) {
        case 0:
        case 7: // just in case?
            return "Dom";
        case 1: return "Seg";
        case 2: return "Ter";
        case 3: return "Qua";
        case 4: return "Qui";
        case 5: return "Sex";
        case 6: return "Sab";
    }
}

/*
 * gera uma lista de sele��o de meses e anos.
 * para sele��es de data onde o dia � irrelevante!
 * retorna um array onde [0] s�o labels e [1] s�o values para usar com widgetLista ou widgetCelulaLista
 *
 * @data_base: vai definir o ano onde a lista se inicia. se n�o for passado, recebe a data de hoje
 * @range: extens�o da lista em meses
 * @dia: qual � o dia do m�s das datas a serem geradas. aqui deve ser poss�vel passar 31 como �ltimo dia de cada m�s
 *
 * IMPORTANTE: tratar os defaults antes de chamar widgets! se estiver fora do range da lista, incluir no final
 */
function listaMesAno($data_base = null, $dia = 1, $range = 48) {
    $labels = array();
    $values = array();
    $opt = array();

    if(empty($data_base)) $data_base = hoje();
    $partes = explode("-", $data_base);
    $mes = 1;					// gera a lista come�ando sempre por janeiro
    $ano = intval($partes[2]);	// come�a a lista pelo ano atual ou anterior?

    for($i = 0; $i < $range; $i++){
        // qual � o dia?
        $d = $dia;
        if($dia >= 31) $d = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

        // nome do m�s
        $nome = substr(nomeDoMes($mes), 0, 3);

        // monta listas
        $labels[] = $nome."/".$ano;
        $values[] = insereZeros($d, 2)."-".insereZeros($mes, 2)."-".$ano; // no ano 10.000 precisaremos de insereZeros($ano, 4)

        // incrementa o m�s
        $mes++;
        if($mes > 12) {
            $mes = 1;
            $ano++;
        }
    }

    $opt[0] = $labels;
    $opt[1] = $values;
    return $opt;
}

// diferen�a de dias de uma data para hoje
function diasAtraso($data) {
    $t1 = strtotime($data);
    $t2 = time();

    $dif = floor(($t2 - $t1) / 3600 / 24);

    // if($dif < 0) $dif = 0;
    return $dif;
}

// diferen�a de dias entre duas datas
function diasEntre($data1, $data2) {
    $t1 = strtotime($data1);
    $t2 = strtotime($data2);

    $dif = (float) (($t2 - $t1) / 3600 / 24);

    // if($dif < 0) $dif = 0;
    return $dif;
}

// n�mero do dia da semana em uma data (yyyy-mm-dd)
function diaDaSemana($data) {
    return date("w", strtotime($data));
}

// retorna a quantidade de fins de semana em um per�odo (yyyy-mm-dd)
function fds($ini, $fim) {
    $inicio = explode("-", $ini);
    $final = explode("-", $fim);

    $d_ano = (int) $inicio[0];
    $d_mes = (int) $inicio[1];
    $d_dia = (int) $inicio[2];

    $dia_base = $d_dia;

    $diferenca = dateDiff($ini, $fim);
    $ultimo_dia= cal_days_in_month(CAL_GREGORIAN, $d_mes, $d_ano);
    $final_semana = 0;

    for($i = 0; $i <= $diferenca; $i++){

        $data_montada = mktime(false, false, false, $d_mes, $dia_base, $d_ano);
        $data_montada = getdate($data_montada);

        if($data_montada['weekday'] == "Saturday" or $data_montada['weekday'] == "Sunday")
            $final_semana++;

        if($dia_base == $ultimo_dia){
            $dia_base = 0;
            $d_mes + 1;
            $d_ano + 1;

            if($d_mes > 12) $d_mes = 1;

            $ultimo_dia = cal_days_in_month(CAL_GREGORIAN, $d_mes, $d_ano);
        }
        $dia_base++;

    }

    return $final_semana;
}

// retorna a quantidade de feriados em um per�odo de datas (subtraindo fins de semana) (YYYY-MM-DD)
function feriados($ini, $fim, $fds = true){
    global $conexao;

    // compatibiliza formato de string
    $ini = str_replace("/", "-", $ini);
    $fim = str_replace("/", "-", $fim);

    // separa dia, mes e ano
    $di = explode('-', $ini);
    $df = explode('-', $fim);

    // todos os cadastros da base possuem ano de 2010
    $data_inicial 	= "$di[0]-$di[1]-$di[2]";
    $data_final 	= "$df[0]-$df[1]-$df[2]";

    $sql = "SELECT 	NOME, 
					DATEPART(WEEKDAY, DATA) AS SEMANA
			FROM 	K_FN_FERIADOS
			WHERE 	CONVERT(DATE, RTRIM(REPLACE(CONVERT(CHAR, DATA, 101), CONVERT(CHAR(4), YEAR(DATA)), '$di[0]'))) 
			BETWEEN :data_inicial AND :data_final";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(":data_inicial", $data_inicial);
    $stmt->bindValue(":data_final", $data_final);
    $stmt->execute();
    $f = $stmt->fetchAll(PDO::FETCH_OBJ);

    $feriados = count($f);

    if(!$fds){
        if($feriados > 0)
            return $feriados - fds($ini, $fim);
        else
            return $feriados;
    }
    else{
        return $feriados;
    }
}

// retorna a quantidade de dias in�teis entre um periodo. (YYYY-MM-DD)
function diasInuteis($ini, $fim){
    $feriados = feriados($ini, $fim, false);
    $fds = fds($ini, $fim);
    return $feriados + $fds;
}

// retorna o proximo dia util de uma data
function proximoDiaUtil($data){
    $util = false;
    while(diasInuteis($data, $data)){
        $data = addDias($data, 1);
    }
    return $data;
}

// data do pr�ximo dia de vencimento
function proximoDia($vencimento, $data_base = null) {
    if(empty($data_base)) $data_base = hoje();

    $partes = explode("-", $data_base);
    $dia = $partes[0];
    $mes = (int) $partes[1];
    $ano = $partes[2];

    // �ltimo dia do m�s
    $ultimo_dia = ultimoDia($mes, $ano);
    $teste = $vencimento; // atribuindo a uma vari�vel tempor�ria porque n�o se pode perder o vencimento original

    if($vencimento > $ultimo_dia){
        // CUIDADO ONDE SE CHAMA MENSAGEM
        // mensagem("O dia {$vencimento} � maior que o �ltimo dia de <b>".nomeDoMes($mes)."</b>. Alterando vencimento para o dia {$ultimo_dia}.", MSG_AVISO);
        $teste = $ultimo_dia;
    }

    // se o dia da data base for maior que o dia do vencimento, geramos vencimento pro mes atual tamb�m.
    if($dia >= $teste) $mes += 1;

    if($mes > 12) {
        $mes = 1;
        $ano += 1;
    }

    // �ltimo dia do m�s (o retorno)
    $ultimo_dia = ultimoDia($mes, $ano);
    if($vencimento > $ultimo_dia) $vencimento = $ultimo_dia;

    $dia = $vencimento;
    $dia = str_pad($dia, 2, "0", STR_PAD_LEFT);
    $mes = str_pad($mes, 2, "0", STR_PAD_LEFT);

    return $dia."-".$mes."-".$ano;
}

// ultimo dia do mes
/* isso � menos confi�vel que cal_days_in_month? */
function ultimoDia($mes = false, $ano = false){
    if(!$mes) $mes = date("m");
    if(!$ano) $ano = date("Y");
    return date("t", mktime(0, 0, 0, $mes, '01', $ano));
}

// adiciona dias em uma data (use valores negativos se quiser).
function addDias($data, $dias) {
    // compatibiliza formato de string
    $data = str_replace("/", "-", $data);

    // separa dia, mes e ano
    $data = explode('-', $data);

    $proxima = mktime ( 0, 0, 0, $data[1], $data[2] + $dias, $data[0] );
    $data = strftime("%Y-%m-%d", $proxima);

    return $data;
}

/* addDias, mas usa a nota��o padr�o ao inv�s de SQL.
 * essa � a que voc� deve usar.
 */
function somaDias($data, $dias) {
    return converteDataSql(addDias(converteData($data), $dias));
}

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// essas fun��es mudaram de nome. mapeando chamada por compatibilidade
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
function converteData0($data, $separator = "-") {
    return converteData($data, $separator);
}

function converteDataSql0($data) {
    return converteDataSql($data);
}


