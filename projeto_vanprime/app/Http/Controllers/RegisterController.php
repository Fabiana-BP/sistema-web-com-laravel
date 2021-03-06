<?php

namespace App\Http\Controllers;

use App\User;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller{


  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('guest');
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {

    //recuperacao de dados
    $categories=Category::orderBy('title')->get();

    //Passar dados para a view
    return view('auth.register',compact('categories'));

  }


  public function store(Request $request){
    //valida Dados
    $categories=Category::orderBy('title')->get();

    $validatedData = $request->validate(
      [
        'name'=>'required|min:3|max:255',
        'email'=>'required|min:6|max:255|unique:users',
        'password' =>'required|min:8|max:255',
        'username' =>'required|min:3|max:10|unique:users',
        'cpf' =>'required|min:14|max:14|unique:users',
        'phone'=>'required|min:11|max:14',
      ]);

      if(!preg_match("/\s*/",$request->username)){ //há espaço em branco
        return redirect()->back()->withInput();
      }
      $u=new User;
      $u->username=$request->username;
      $u->name=$request->name;
      $u->cpf=$request->cpf;
      $u->phone=$request->phone;
      $u->user_role=$request->user_role;
      $u->password= Hash::make($request->password);
      $username=$request->username;
      $u->email=$request->email;
    //salvar foto em storage/app/public/profiles/
      if($request->hasFile('image_user') && $request->file('image_user')->isValid()){
      // Define um aleatório para o arquivo baseado no timestamps atual
        $name = uniqid(date('HisYmd'));

        // Recupera a extensão do arquivo
        try{
          $extension = $request->image_user->extension();
        }catch(Exception $e){
          return redirect()
                      ->back()
                      ->with('error', 'Arquivo não suportado')
                      ->withInput();
        }

        // Define finalmente o nome
        $nameFile = "{$name}.{$extension}";
        $u->image_user=$nameFile;
        $upload = $request->image_user->storeAs('profiles',$nameFile);
        // Verifica se NÃO deu certo o upload (Redireciona de volta)
        if ( !$upload )
            return redirect()
                        ->back()
                        ->with('error', 'Falha ao fazer upload')
                        ->withInput();
    }

    $save=$u->save();
    if($save && ($request->user_role=="company")){

      return view('register.register_company',compact('categories','username'));
    }else if($save && ($request->user_role=="client")){
      return redirect('login');
    }else{
      return redirect()->route('cadastro.create')->withInput();
    }

    //envia todos os dados
    //User::create($request->all());

  }
}
