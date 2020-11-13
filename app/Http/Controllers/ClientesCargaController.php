<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Datasul\DatasulController;
use Log;
use App\Http\Controllers\Magento\MagentoController;
use Illuminate\Http\Request;

use App\Http\Requests;

class ClientesCargaContrphpoller extends Controller
{

    public function converteClienteMagentoClienteDatasul($clientePedidoMagento, $loja){

        $listaCliente = array();

        $cpf_cnpj = preg_replace("/[^0-9\s]/", "", $clientePedidoMagento->taxvat);

        $clienteDatasul = new \stdClass();
        $clienteDatasul->cpf_Cnpj = $cpf_cnpj;

        $nome = '';
        // testar se nomes estao preenchidos
        if ($clientePedidoMagento->firstname != ''){
            $nome = $nome.$clientePedidoMagento->firstname;
        }
        if ($clientePedidoMagento->lastname != ''){
            $nome = $nome." ".$clientePedidoMagento->lastname;
        }
        $clienteDatasul->nome = $nome;
        $clienteDatasul->email = $clientePedidoMagento->email;
        $clienteDatasul->dt_Cadastro = $clientePedidoMagento->created_at;
        $clienteDatasul->loja = $loja;


        $listaCliente[] = $clienteDatasul;

        return $listaCliente;

    }

    public function converteEnderecosClienteMagentoEnderecosClienteDatasul($sessaoMagento, $pedidoMagento, $enderecoClientePedidoMagento, $loja)
    {
        $objDatasul = new DatasulController();

        $listaEnderecosDatasul = array();

        /*if ($pedidoMagento->increment_id == "100000083") {dd($pedidoMagento);}*/

//        dd($pedidoMagento);
//        dd($enderecoClientePedidoMagento);

        //Endereco de entrega
        $enderecoEntrega = new \stdClass();
        // Configura um id especifico para o endereco de entrega do pedido
        $enderecoEntrega->cod_endEntr = $pedidoMagento->increment_id{0}.$pedidoMagento->customer_id;
        $enderecoEntrega->endereco = $this->getRuaComplemento($pedidoMagento->shipping_address->street);

        $cep = preg_replace("/[^0-9\s]/", "", $pedidoMagento->shipping_address->postcode);
        $enderecoEntrega->cep = $cep;

        $enderecoEntrega->bairro = $this->getBairro($pedidoMagento->shipping_address->street);
        if (is_numeric($pedidoMagento->shipping_address->city)){
            $enderecoEntrega->cidade = $objDatasul->getCidadeIBGE($pedidoMagento->shipping_address->city);
        } else {
            $enderecoEntrega->cidade = $pedidoMagento->shipping_address->city;
        }

        if ($loja <> "MAGENTO"){

            if (strlen($pedidoMagento->shipping_address->region) > 2){
                $enderecoEntrega->uf = $this->getUF($pedidoMagento->shipping_address->region);
            }else{
                $enderecoEntrega->uf = $pedidoMagento->shipping_address->region;
            }
        } else{
            if (isset($pedidoMagento->shipping_address->region_id) /*or $pedidoMagento->shipping_address->region_id == 0*/){
                if (isset($pedidoMagento->shipping_address->region)){
                    $enderecoEntrega->uf = $this->getUF($pedidoMagento->shipping_address->region);
                } else{
                    Log::error('log', ['message' => "Estado do endereço de Entrega não foi cadastrado!!!"]);
                    return redirect("/pedidosintegra");
                }
            } else{
                $enderecoEntrega->uf = $this->getUFMagento($sessaoMagento, $pedidoMagento->shipping_address->region_id, $pedidoMagento->shipping_address->country_id);
            }
        }

        if (isset($enderecoClientePedidoMagento[0]->telephone)){
            $enderecoEntrega->telefone1 = $enderecoClientePedidoMagento[0]->telephone;
            $enderecoEntrega->telefone2 = $enderecoClientePedidoMagento[0]->telephone;
        }else{
            $enderecoEntrega->telefone1 = ".";
            $enderecoEntrega->telefone2 = ".";
        }

        $enderecoEntrega->end_cob = false;
        $enderecoEntrega->end_entr = true;

        $listaEnderecosDatasul[] = $enderecoEntrega;

        Log::info('log', ['message' => "Endereco de Entrega: ".json_encode($enderecoEntrega)]);
        
        //Endereco de Cobranca
        $enderecoCobranca = new \stdClass();
        $enderecoCobranca->endereco = $this->getRuaComplemento($pedidoMagento->billing_address->street);

        $cep = preg_replace("/[^0-9\s]/", "", $pedidoMagento->billing_address->postcode);
        $enderecoCobranca->cep = $cep;
        $enderecoCobranca->bairro = $this->getBairro($pedidoMagento->billing_address->street);
        if (is_numeric($pedidoMagento->billing_address->city)){
            $enderecoCobranca->cidade = $objDatasul->getCidadeIBGE($pedidoMagento->billing_address->city);
        } else {
            $enderecoCobranca->cidade = $pedidoMagento->billing_address->city;
        }

        if ($loja <> "MAGENTO"){

            if (strlen($pedidoMagento->billing_address->region) > 2){
                $enderecoCobranca->uf = $this->getUF($pedidoMagento->billing_address->region);
            }else{
                $enderecoCobranca->uf = $pedidoMagento->billing_address->region;
            }
        } else{
            if ($pedidoMagento->billing_address->region_id == 0){
                if (isset($pedidoMagento->billing_address->region)){
                    $enderecoCobranca->uf = $this->getUF($pedidoMagento->billing_address->region);
                } else{
                    Log::error('log', ['message' => "Estado do endereço de Cobrança não foi cadastrado!!!"]);
                    return redirect("/pedidosintegra");
                }
            } else{
                $enderecoCobranca->uf = $this->getUFMagento($sessaoMagento, $pedidoMagento->billing_address->region_id, $pedidoMagento->billing_address->country_id);
            }
        }

        if (isset($enderecoClientePedidoMagento[0]->telephone)){
            $enderecoCobranca->telefone1 = $enderecoClientePedidoMagento[0]->telephone;
            $enderecoCobranca->telefone2 = $enderecoClientePedidoMagento[0]->telephone;
        }else{
            $enderecoCobranca->telefone1 = ".";
            $enderecoCobranca->telefone2 = ".";
        }

        $enderecoCobranca->end_cob = true;
        $enderecoCobranca->end_entr = false;

        //$enderecoCobranca->tipo = $pedidoMagento->billing_address->address_type;

        $listaEnderecosDatasul[] = $enderecoCobranca;

        Log::info('log', ['message' => "Endereco de Cobranca: ".json_encode($enderecoCobranca)]);

//        dd($listaEnderecosDatasul);

        return $listaEnderecosDatasul;
    }

    private function getBairro($bairro)
    {
        $bairro = explode("\n",$bairro);

        if (count($bairro) < 4){
            $bairro = "Não Informado";
        } else{
            $bairro = $bairro[count($bairro) - 1];
        }

        return $bairro;

        /*return $bairro[count($bairro) - 1];*/
    }

    private function getRuaComplemento($endereco)
    {
        $ruaComplemento = explode("\n", $endereco);

        if ($ruaComplemento[1] == "S/N" or $ruaComplemento[1] == "SN"){
            if (isset($ruaComplemento[2]) == false){
                $endereco = $ruaComplemento[0]." ".$ruaComplemento[1];
            } else{
                if ($ruaComplemento[2] == ""){
                    $endereco = $ruaComplemento[0]." ".$ruaComplemento[1];
                } else{
                    $endereco = $ruaComplemento[0]." ".$ruaComplemento[1]." - ".$ruaComplemento[2];
                }
            }
        } else{
            if (isset($ruaComplemento[2]) == false){
                $endereco = $ruaComplemento[0].", ".$ruaComplemento[1];
            } else{
                if ($ruaComplemento[2] == ""){
                    $endereco = $ruaComplemento[0].", ".$ruaComplemento[1];
                } else{
                    $endereco = $ruaComplemento[0].", ".$ruaComplemento[1]." - ".$ruaComplemento[2];
                }
            }
        }

        return $endereco;
    }

    private function getUF($estado)
    {
        if ($estado == "Acre" || $estado == "AC"){
            $uf = "AC";
        } elseif ($estado == "Alagoas" || $estado == "AL"){
            $uf = "AL";
        } elseif ($estado == "Amapá" || $estado == "AP"){
            $uf = "AP";
        } elseif ($estado == "Amazonas" || $estado == "AM"){
            $uf = "AM";
        } elseif ($estado == "Bahia" || $estado == "BA"){
            $uf = "BA";
        } elseif ($estado == "Ceará" || $estado == "CE"){
            $uf = "CE";
        } elseif ($estado == "Distrito Federal" || $estado == "DF"){
            $uf = "DF";
        } elseif ($estado == "Espírito Santo" || $estado == "ES"){
            $uf = "ES";
        } elseif ($estado == "Goiás" || $estado == "GO"){
            $uf = "GO";
        } elseif ($estado == "Maranhão" || $estado == "MA"){
            $uf = "MA";
        } elseif ($estado == "Mato Grosso" || $estado == "MT"){
            $uf = "MT";
        } elseif ($estado == "Mato Grosso do Sul" || $estado == "MS"){
            $uf = "MS";
        } elseif ($estado == "Minas Gerais" || $estado == "MG"){
            $uf = "MG";
        } elseif ($estado == "Pará" || $estado == "PA"){
            $uf = "PA";
        } elseif ($estado == "Paraíba" || $estado == "PB"){
            $uf = "PB";
        } elseif ($estado == "Paraná" || $estado == "PR"){
            $uf = "PR";
        } elseif ($estado == "Pernambuco" || $estado == "PE"){
            $uf = "PE";
        } elseif ($estado == "Piauí" || $estado == "PI"){
            $uf = "PI";
        } elseif ($estado == "Rio de Janeiro" || $estado == "RJ"){
            $uf = "RJ";
        } elseif ($estado == "Rio Grande do Norte" || $estado == "RN"){
            $uf = "RN";
        } elseif ($estado == "Rio Grande do Sul" || $estado == "RS"){
            $uf = "RS";
        } elseif ($estado == "Rondônia" || $estado == "RO"){
            $uf = "RO";
        } elseif ($estado == "Roraima" || $estado == "RR"){
            $uf = "RR";
        } elseif ($estado == "Santa Catarina" || $estado == "SC"){
            $uf = "SC";
        } elseif ($estado == "São Paulo" || $estado == "SP"){
            $uf = "SP";
        } elseif ($estado == "Sergipe" || $estado == "SE"){
            $uf = "SE";
        } elseif ($estado == "Tocantins" || $estado == "TO"){
            $uf = "TO";
        }
        
        return $uf;
    }

    private function getUFMagento($sessaoMagento, $regionId, $countryId)
    {
        if ($countryId == "BR"){

            $objMagento = new MagentoController();

            $regioes = $objMagento->getDirectoryRegionList($sessaoMagento, $countryId);

            if ($regioes){
                for ($i = 0; $i < count($regioes); $i++){
                    if ($regioes[$i]->region_id == $regionId){
                        $uf = $regioes[$i]->code;
                    }
                }

                return $uf;
            } else{
                return "";
            }
        } else{
            return "";
        }
    }
}
