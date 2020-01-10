<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 02/04/2019
 * Time: 10:03
 */

namespace src\entity;

use src\services\Transact\ExtPDO as PDO;

class AgendaGUI extends ObjectGUI implements InterfaceGUI
{
    public function __construct($handle = null)
    {
        $this->handle = $handle;

        if(__EDUCACIONAL__) {
            $this->header = array(
                'Data Inicial', 'Data Final', 'Titulo Evento', 'Tipo', 'Regiao', 'Escola', 'Turma', 'Aluno', 'Responsavel'
            );
        }else{
            $this->header = array(
                'Data Inicial', 'Data Final', 'Titulo Evento', 'Hora Inicial', 'Hora Final', 'Pessoa', 'Responsavel'
            );
        }

    }

    public function getCampo($linha, $coluna)
    {
        // indexa o item
        $item = $this->itens[$linha];
        $tipo = AgendaETT::getNomeEvento($item->tipo_evento);

        if(__EDUCACIONAL__){
            // para a coluna, retorna um array com o valor e a classe a aplicar
            switch ($coluna) {
                case 0:         return campo($item->data_inicial);
                case 1:         return campo($item->data_final);
                case 2:         return campo($item->titulo);
                case 3:         return campo($tipo);
                case 4:         return campo($item->regiao);
                case 5:         return campo($item->escola);
                case 6:         return campo($item->turma);
                case 7:         return campo($item->pessoa);
                case 8:         return campo($item->nome_responsavel);

                default:        return "não implementado";
            }
        }else{
            // para a coluna, retorna um array com o valor e a classe a aplicar
            switch ($coluna) {
                case 0:         return campo($item->data_inicial);
                case 1:         return campo($item->data_final);
                case 2:         return campo($item->titulo);
                case 3:         return campo($item->hora_inicial);
                case 4:         return campo($item->hora_final);
                case 5:         return campo($item->pessoa);
                case 6:         return campo($item->nome_responsavel);
                default:        return "não implementado";
            }
        }
    }

    public function fetch()
    {
        global $conexao;

        if(__EDUCACIONAL__){
            //$where = "WHERE ".filtraFilial("A.FILIAL", "Educacional");
            $where = "WHERE 1 = 1";
        }else{
            $where = "WHERE 1 = 1";
        }

        if (!empty($this->pesquisa['pesq_num'])) {
            $where .= " AND A.HANDLE = :handle \n";
        }
        if (!empty($this->pesquisa['pesq_data_inicial'])) {
            $where .= " AND A.DATA >= :pesq_data_inicial \n";
        }
        if (!empty($this->pesquisa['pesq_data_final'])) {
            $where .= " AND A.DATAFINAL <= :pesq_data_final \n";
        }
        if (!empty($this->pesquisa['pesq_cod_pessoa'])) {
            $where .= " AND A.PESSOA = :pesq_cod_pessoa \n";
        }
        if (!empty($this->pesquisa['pesq_responsavel'])) {
            $where .= " AND A.USUARIO = :pesq_responsavel \n";
        }
        if (!empty($this->pesquisa['pesq_regiao'])) {
            $where .= " AND A.REGIAO = :pesq_regiao \n";
        }
        if (!empty($this->pesquisa['pesq_escola'])) {
            $where .= " AND A.FILIAL = :pesq_escola \n";
        }
        if (!empty($this->pesquisa['pesq_turma'])) {
            $where .= " AND A.TURMA = :pesq_turma \n";
        }


        // tras os indices da lista
        $sql = "SELECT {$this->top} P.NOME AS NOMEPESSOA, F.NOME AS NOMEESCOLA, 
                T.NOME AS NOMETURMA, R.NOME AS NOMEREGIAO, D.NOME AS RESPONSAVEL,
                A.*
                FROM K_AGENDA A 
                LEFT JOIN K_FN_PESSOA P ON P.HANDLE = A.PESSOA
                LEFT JOIN K_FN_FILIAL F ON F.HANDLE = A.FILIAL
                LEFT JOIN K_TURMA T ON T.HANDLE = A.TURMA
                LEFT JOIN K_REGIAO R ON R.HANDLE = A.REGIAO
                LEFT JOIN K_PD_USUARIOS D ON D.HANDLE = A.USUARIO
                {$where}
                AND A.ATIVO = 'S'
                ";


        $stmt = $conexao->prepare($sql);
        if (!empty($this->pesquisa['pesq_num'])) {$stmt->bindValue(':handle', $this->pesquisa['pesq_num']);}
        if (!empty($this->pesquisa['pesq_data_inicial'])) {$stmt->bindValue(':pesq_data_inicial', converteData($this->pesquisa['pesq_data_inicial']));} 
        if (!empty($this->pesquisa['pesq_data_final'])) {$stmt->bindValue(':pesq_data_final', converteData($this->pesquisa['pesq_data_final']));}
        if (!empty($this->pesquisa['pesq_cod_pessoa'])) {$stmt->bindValue(':pesq_cod_pessoa', $this->pesquisa['pesq_cod_pessoa']);}
        if (!empty($this->pesquisa['pesq_responsavel'])) {$stmt->bindValue(':pesq_responsavel', $this->pesquisa['pesq_responsavel']);}
        if (!empty($this->pesquisa['pesq_regiao'])) {$stmt->bindValue(':pesq_regiao', $this->pesquisa['pesq_regiao']);}
        if (!empty($this->pesquisa['pesq_escola'])) {$stmt->bindValue(':pesq_escola', $this->pesquisa['pesq_escola']);}
        if (!empty($this->pesquisa['pesq_turma'])) {$stmt->bindValue(':pesq_turma', $this->pesquisa['pesq_turma']);}

        $stmt->execute();

        $listas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $i = 0; //inicia contador

        if (!empty($listas)) {
            foreach ($listas as $r) {
                $item = new AgendaETT();
                $item->handle = $r->HANDLE;
                $item->cont = $i;
                
                $item->hora_final = $r->HORAFINAL;
                $item->hora_inicial = $r->HORA;

                $item->data_inicial = new \DateTime($r->DATA);
                $item->data_final = new \DateTime($r->DATAFINAL);
                $item->data_inicial = $item->data_inicial->format('Y-m-d');
                $item->data_final = $item->data_final->format('Y-m-d');

                $item->titulo = $r->TITULO;
                $item->tipo_evento = $r->TIPO;
                $item->pessoa = $r->NOMEPESSOA;
                $item->cod_pessoa = $r->PESSOA;
                $item->turma = $r->NOMETURMA;
                $item->cod_turma = $r->TURMA;
                $item->regiao = $r->NOMEREGIAO;
                $item->cod_regiao = $r->REGIAO;
                $item->escola = $r->NOMEESCOLA;
                $item->cod_escola = $r->FILIAL;
                $item->responsavel = $r->USUARIO;
                $item->conteudo = $r->CONTEUDO;
                $item->evento_ativo = $r->ATIVO;
                $item->nome_responsavel = $r->RESPONSAVEL;
                $this->itens[] = $item;

                // alguns eventos precisam marcar a data final como se fosse outro evento
                if(in_array($item->tipo_evento, array($item::DIV_BIMESTRE, $item::DIV_TRIMESTRE, $item::PERIODO_ANO, $item::PERIODO_SEMESTRE))){
                    $item = clone $item;
                    $item->titulo = "Final ".$item->titulo;
                    $item->data_inicial = $item->data_final;
                    $this->itens[] = $item;
                }
                ++$i;
            }
        }
    }
}