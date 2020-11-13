<?php

namespace App\Http\Controllers;

use App\Models\Parametro;
use App\Models\Transportadora;
use Illuminate\Http\Request;

use App\Http\Requests;

class ConfiguracoesController extends Controller
{
    protected $param;
    protected $transp;
    protected $request;
    protected $route = '/configuracoes';
    protected $redirectEditParam = '/configuracoes/editparam';
    protected $redirectCadParam = '/configuracoes/cadparam';
    protected $redirectCadTransp = '/configuracoes/cadtransp';

    public function __construct(Parametro $parametro, Transportadora $transportadora, Request $request)
    {
        $this->param = $parametro;
        $this->transp = $transportadora;
        $this->request = $request;
    }

    public function index()
    {
        $parametros = $this->param->get();
        $trasportadoras = $this->transp->get();

        return view('configuracoes.index', compact('parametros','trasportadoras'));
    }

    public function cadParam()
    {
        return view("parametros.form");
    }

    public function cadParamGo()
    {
        //Recupera os dados do formulario
        $dadosForm = $this->request->all();

        //Faz o insert
        $insert = $this->param->create($dadosForm);

        if ($insert)
            return redirect($this->route);
        else
            return redirect("$this->redirectCadParam")
                ->withErrors(['errors' => 'Falha ao Cadastrar!!!'])
                ->withInput();

    }

    public function editParam($id)
    {
        $parametros = $this->param->find($id);

        return view("parametros.form", compact('parametros'));
    }

    public function editParamGo($id)
    {
        $dadosForm = $this->request->all();

        //dd($dadosForm);

        $item = $this->param->find($id);

        $update = $item->update($dadosForm);

        if ($update)
            return redirect($this->route);
        else
            return redirect("$this->redirectEditParam/$id")
                ->withErrors(['errors' => 'Falha ao editar!!!'])
                ->withInput();

    }


    public function cadTransp()
    {
        return view("transportadoras.form");
    }

    public function cadTranspGo()
    {
        //Recupera os dados do formulario
        $dadosForm = $this->request->all();

        //Faz o insert
        $insert = $this->transp->create($dadosForm);

        if ($insert)
            return redirect($this->route);
        else
            return redirect("$this->redirectCadTransp")
                ->withErrors(['errors' => 'Falha ao Cadastrar!!!'])
                ->withInput();

    }



}
