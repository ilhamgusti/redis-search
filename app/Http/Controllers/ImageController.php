<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageRequest;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(ImageRequest $request)
    {
        dd($request);
        // Validasi berhasil, lanjutkan untuk menambahkan gambar baru
        // Lakukan pemrosesan gambar dan simpan ke penyimpanan

        // $image = new Image();
        // // Set atribut-atribut gambar
        // $image->position = $request->position;
        // $image->type = $request->type;
        // $image->is_desktop = $request->is_desktop;
        // $image->is_mobile = $request->is_mobile;
        // // Simpan gambar
        // $image->save();

        // Berikan respons atau lakukan tindakan lain yang diperlukan
        return response()->json(['message' => 'Gambar berhasil ditambahkan']);
    }
}
