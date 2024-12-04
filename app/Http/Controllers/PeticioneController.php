<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PeticioneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $content = Peticione::all();
        return view('peticiones.index', compact('content'));
    }

    public function delete(Peticione $id)
    {
        $this->delete($id);
    }

    public function firmar($peticioneId)
    {
        $userId = Auth::id();
        $peticione = Peticione::query()->findOrFail($peticioneId);
        $user = User::query()->find($userId);
        if (!$user->firmas()->where("peticione_id", "=", $peticione->id)->first()) {
            $user->firmas()->attach($peticione->id);
            $user->save();
            $content = peticione::all();
            $peticione->firmantes = $peticione->firmantes + 1;
            $peticione->save();
            return view('peticiones.index', compact('content'));
        }
        $content = $peticione->all();
        return view('peticiones.index', compact('content'));
    }

    public function listMine()
    {
        $id = Auth::id();
        $content = Peticione::query()->where('user_id', '=', $id)->get();
        if ($content) {
            return view('peticiones.index', compact('content'));
        } else {
            $content = Peticione::all();
            return view('peticiones.index', compact('content'));
        }
    }

    public function show($id)
    {
        $content = Peticione::query()->where('id', '=', $id)->get()->first();
        return view('peticiones.show', compact('content'));
    }

    public function peticionesFirmadas(Request $request)
    {
        try {
            $id = Auth::id();
            $usuario = User::findOrFail($id);
            $content = $usuario->firmas;
        } catch (\Exception $exception) {
            return back()->withError($exception->getMessage())->withInput();
        }
        return view('peticiones.index', compact('content'));
    }

    public function create(Request $request)
    {
        $categorias = Categoria::orderBy('nombre', 'asc')->get();
        return view('peticiones.edit-add', compact('categorias'));
    }

    public function update(Request $request)
    {
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'destinatario' => 'required',
            'categoria' => 'required',
            'file' => 'required',
        ]);
        $input = $request->all();
        try {
            $category = Categoria::findOrFail($input['categoria']);
            $user = Auth::user(); //asociarlo al usuario authenticado
            $peticion = new Peticione($input);
            $peticion->categoria()->associate($category);
            $peticion->user()->associate($user);
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';
            $res = $peticion->save();
            if ($res) {
                $res_file = $this->fileUpload($request, $peticion->id);
                if ($res_file) {
                    return redirect('/mispeticiones');
                }
                return back()->withError('Error creando la peticion')->withInput();
            }
        } catch (\Exception $exception) {
            return back()->withError($exception->getMessage())->withInput();
        }
    }



    public function store2(Request $request)
    {
        try {
            $userid = Auth::id();
            $categoria = Categoria::findOrFail($input['category']);
            $peticion = new Peticione($input);
            $peticion->categoria()->associate($categoria);
            $peticion->user()->associate($userid);
            $res = $peticion->save();

            if($res){
                $res_file = $this->fileUpload($request, $peticion->id);
                if ($res_file) {
                    return redirect('/mispeticiones');
                }
                return back()->withError('error creando la peticion')->withInput();
            }
            $estado = "pendiente";
            $firmantes = 0;
            $validator = Validator::make($request->all(), [
                'titulo' => 'string|required',
                'descripcion' => 'string|required',
                'destinatario' => 'string|required',
            ]);
            $validator->validate();
        } catch (\Exception $exception) {
            return back()->withError($exception->getMessage());
        }
        /*$peticion = new Peticione();
        $peticion['titulo'] = $request->get('titulo');
        $peticion['user_id'] = $userid;
        $peticion['categoria_id'] = $categoria;
        $peticion['estado'] = $estado;
        $peticion['descripcion'] = $request->get('descripcion');
        $peticion['destinatario'] = $request->get('destinatario');
        $peticion['firmantes'] = $firmantes;
        $peticion->save();
        $content = $peticion;
        return view('peticiones.show', compact('content'));*/
    }

    public function fileUpload(Request $req, $peticione_id = null)
    {
        $file = $req->file('file');
        $fileModel = new File;
        $fileModel->peticione_id = $peticione_id;
        if ($req->file('file')) {
            //return $req->file('file');

            $filename = $fileName = time() . '_' . $file->getClientOriginalName();
            //      Storage::put($filename, file_get_contents($req->file('file')->getRealPath()));
            $file->move('peticiones', $filename);

            //  Storage::put($filename, file_get_contents($request->file('file')->getRealPath()));
            //   $file->move('storage/', $name);


            //$filePath = $req->file('file')->storeAs('/peticiones', $fileName, 'local');
            //    $filePath = $req->file('file')->storeAs('/peticiones', $fileName, 'local');
            // return $filePath;
            $fileModel->name = $filename;
            $fileModel->file_path = $filename;
            $res = $fileModel->save();
            return $fileModel;
            if ($res) {
                return 0;
            } else {
                return 1;
            }
        }
        return 1;
    }
}
